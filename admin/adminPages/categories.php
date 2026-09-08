<?php 
require __DIR__."/../../config/config.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";
require __DIR__."/../../includes/dashboardHeader.php";

$fetchCategoriesWithDeals = $connection->prepare("
    SELECT 
        c.*,
        (
            SELECT COUNT(*) 
            FROM deals d 
            JOIN book b ON d.book_id = b.id 
            WHERE b.category_id = c.id 
              AND d.status = 'active' 
              AND CURRENT_TIMESTAMP < d.end_time
        ) AS active_deals_count
    FROM categories c
");
$fetchCategoriesWithDeals->execute();
$allCategories = $fetchCategoriesWithDeals->fetchAll(PDO::FETCH_OBJ);
?>

<div id="content">
    
    <div class="form-container add-cat-section">
        <h4 class="form-title">Add New Category</h4>
        <form class="inline-form ajax-form" action="/admin/handlers/addCategories.php" method="post">
            <div class="input-wrapper">
                <input type="text" name="category" placeholder="Enter Category Name">
                <div class="error title"></div>
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    </div>

    <div class="list-panel">
        <div class="panel-header">
            <h4>Existing Categories</h4>
        </div>
        
        <div class="category-list">
            <?php foreach ($allCategories as $category): ?>
                <div class="category-item" id="cat-item-<?= $category->id ?>">
                    <div class="cat-content">
                        <span class="cat-name"><?= htmlspecialchars($category->title) ?></span>
                        <input type="text" class="edit-cat-input" value="<?= htmlspecialchars($category->title) ?>" style="display:none;">
                        <span class="badge warning"><?= getCount($connection, $category->id) ?> Books</span>
                    </div>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-primary edit-category-btn" data-id="<?= $category->id ?>">Edit</button>
                        <button type="button" class="btn btn-success save-category-btn" data-id="<?= $category->id ?>" style="display:none; background: #2ecc71; color: white;">Save</button>
                        <button type="button" class="btn btn-danger openDeleteCategoryModal" data-id="<?= $category->id ?>" data-title="<?= htmlspecialchars($category->title, ENT_QUOTES) ?>" data-count="<?= getCount($connection, $category->id) ?>" data-has-deal="<?= $category->active_deals_count > 0 ? '1' : '0' ?>">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<div class="modal-overlay" id="deleteCategoryOverlay"></div>

<div class="confirm-modal" id="deleteCategoryModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 22px; color: var(--neo-pink);"></i>
            <h3>Delete Category</h3>
        </div>
        <span class="close-modal" id="closeDeleteCategory">&times;</span>
    </div>
    <div class="confirm-modal-body">
        <p>Are you sure you want to delete <span class="confirm-book-badge" id="deleteCategoryTitle"></span>?</p>
        <div class="confirm-modal-warning-box">
            <i class="ph-bold ph-warning"></i>
            <p><strong>Warning:</strong> Deleting this category will permanently delete all <strong id="deleteCategoryBookCount">0</strong> books associated with it, including their cover images. This action cannot be undone.</p>
        </div>
    </div>
    <form action="/admin/handlers/deleteCategory.php" method="POST" class="ajax-form" id="deleteCategoryForm">
        <input type="hidden" name="id" id="deleteCategoryIdField">
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelDeleteCategoryBtn">Cancel</button>
            <button type="submit" class="btn btn-danger" id="confirmDeleteCategoryBtn">Delete Category</button>
        </div>
    </form>
</div>

<div class="modal-overlay" id="dealBlockedOverlay"></div>

<div class="confirm-modal" id="dealBlockedModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-lock-key" style="font-size: 22px; color: var(--neo-pink);"></i>
            <h3>Cannot Delete Category</h3>
        </div>
        <span class="close-modal" id="closeDealBlocked">&times;</span>
    </div>
    <div class="confirm-modal-body">
        <p>The category <span class="confirm-book-badge" id="dealBlockedCategoryTitle"></span> cannot be deleted.</p>
        <div class="confirm-modal-warning-box">
            <i class="ph-bold ph-warning"></i>
            <p><strong>Notice:</strong> This category cannot be deleted because a book in this category is currently active in the Daily Deal. Please wait for the deal to expire before deleting this category.</p>
        </div>
    </div>
    <div class="confirm-modal-actions">
        <button type="button" class="btn btn-secondary" id="ackDealBlockedBtn" style="width: 100%;">Understood</button>
    </div>
</div>

<div id="categoryErrorToast" class="neo-toast">
    <div class="toast-content">
        <i class="ph-bold ph-warning-circle toast-icon"></i>
        <span id="categoryErrorMessage"></span>
    </div>
    <button type="button" class="toast-close" id="closeToast">&times;</button>
</div>

</div>

<script>
    const deleteCatModal = document.getElementById('deleteCategoryModal');
    const deleteCatOverlay = document.getElementById('deleteCategoryOverlay');
    const closeDeleteCatBtn = document.getElementById('closeDeleteCategory');
    const cancelDeleteCatBtn = document.getElementById('cancelDeleteCategoryBtn');
    const deleteCatIdField = document.getElementById('deleteCategoryIdField');
    const deleteCatTitle = document.getElementById('deleteCategoryTitle');
    const deleteCatCount = document.getElementById('deleteCategoryBookCount');

    const dealBlockedModal = document.getElementById('dealBlockedModal');
    const dealBlockedOverlay = document.getElementById('dealBlockedOverlay');
    const closeDealBlockedBtn = document.getElementById('closeDealBlocked');
    const ackDealBlockedBtn = document.getElementById('ackDealBlockedBtn');
    const dealBlockedCategoryTitle = document.getElementById('dealBlockedCategoryTitle');

    document.querySelectorAll('.openDeleteCategoryModal').forEach(btn => {
        btn.addEventListener('click', () => {
            const catId = btn.getAttribute('data-id');
            const catTitle = btn.getAttribute('data-title');
            const catCount = btn.getAttribute('data-count');
            const hasDeal = btn.getAttribute('data-has-deal');

            if (hasDeal === '1') {
                if (dealBlockedCategoryTitle) dealBlockedCategoryTitle.textContent = catTitle;
                if (dealBlockedModal) dealBlockedModal.classList.add('active');
                if (dealBlockedOverlay) dealBlockedOverlay.classList.add('active');
            } else {
                if (deleteCatIdField) deleteCatIdField.value = catId;
                if (deleteCatTitle) deleteCatTitle.textContent = catTitle;
                if (deleteCatCount) deleteCatCount.textContent = catCount;
                if (deleteCatModal) deleteCatModal.classList.add('active');
                if (deleteCatOverlay) deleteCatOverlay.classList.add('active');
            }
        });
    });

    function closeDeleteCatModal() {
        if (deleteCatModal) deleteCatModal.classList.remove('active');
        if (deleteCatOverlay) deleteCatOverlay.classList.remove('active');
    }

    function closeDealBlockedModal() {
        if (dealBlockedModal) dealBlockedModal.classList.remove('active');
        if (dealBlockedOverlay) dealBlockedOverlay.classList.remove('active');
    }

    if (closeDeleteCatBtn) closeDeleteCatBtn.addEventListener('click', closeDeleteCatModal);
    if (cancelDeleteCatBtn) cancelDeleteCatBtn.addEventListener('click', closeDeleteCatModal);
    if (deleteCatOverlay) deleteCatOverlay.addEventListener('click', closeDeleteCatModal);

    if (closeDealBlockedBtn) closeDealBlockedBtn.addEventListener('click', closeDealBlockedModal);
    if (ackDealBlockedBtn) ackDealBlockedBtn.addEventListener('click', closeDealBlockedModal);
    if (dealBlockedOverlay) dealBlockedOverlay.addEventListener('click', closeDealBlockedModal);
</script>

<script src="/assests/js/admin.js"></script>
</body>
</html>