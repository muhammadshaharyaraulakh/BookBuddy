<?php
require __DIR__ . "/../../config/config.php";
require __DIR__ . "/../../includes/dashboardHeader.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";
$allCategories = getCategories($connection);
$fetchBooks = $connection->prepare("
    SELECT 
        b.*,
        (
            SELECT COUNT(*) 
            FROM deals d 
            WHERE d.book_id = b.id 
              AND d.status = 'active' 
              AND CURRENT_TIMESTAMP < d.end_time
        ) AS is_on_deal
    FROM book b
    ORDER BY b.id DESC
");
$fetchBooks->execute();
$books = $fetchBooks->fetchAll(PDO::FETCH_OBJ);
?>

<div id="content">

    <div class="category-filter">
        <button class="category-btn active" data-filter="all">All</button>
        <?php foreach ($allCategories as $category): ?>
            <button class="category-btn" data-filter="<?= $category->id ?>"><?= $category->title ?></button>
        <?php endforeach; ?>
    </div>

    <div class="books-grid">
        <?php foreach ($books as $book): ?>
            <div class="book-card" data-category="<?= $book->category_id ?>">
                <img src="/images/<?= $book->coverImage ?>" class="book-img">
                <div class="book-title"><?= $book->title ?></div>
                <?php if ($book->is_on_deal > 0): ?>
                    <span class="badge warning" style="background: var(--neo-yellow); color: var(--neo-black); border: 1.5px solid var(--neo-black); font-weight: 800; margin-bottom: 8px;">On Daily Deal</span>
                <?php endif; ?>
                <?php if ($book->Discount_Percentage !== 0 && $book->Discount_Percentage !== null): ?>
                    <span class="old-price">
                        <strike>$<?= $book->Original_Price ?></strike>
                    </span>
                    <span class="book-price">$<?= $book->Discount_Price ?></span>

                <?php else: ?>
                    <span>No Discount</span>
                    <span class="book-price">$<?= $book->Original_Price ?></span>

                <?php endif; ?>

                <div class="stock-status">In Stock (<?= $book->Stock ?>)</div>
                <div class="book-actions">
                    <?php if ($book->is_on_deal > 0): ?>
                        <button type="button" class="btn btn-primary" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot update book while in daily deal">Update</button>
                        <button type="button" class="btn btn-orange" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot apply discount while in daily deal">Discount</button>
                        <button type="button" class="btn btn-danger" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot delete book while in daily deal">Delete</button>
                    <?php else: ?>
                        <form action="/updatebook" method="get" class="bookform">
                            <input type="hidden" name="id" value="<?php echo $book->id ?>">
                            <button class="btn btn-primary">Update</button>
                        </form>

                        <button type="button" class="btn btn-orange openDiscountModal" data-book-id="<?= $book->id ?>">
                            Discount
                        </button>

                        <button type="button" class="btn btn-danger openDeleteModal" data-book-id="<?= $book->id ?>" data-book-title="<?= htmlspecialchars($book->title, ENT_QUOTES) ?>">
                            Delete
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<div class="modal-overlay" id="discountOverlay"></div>

<div class="discount-modal" id="discountModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-tag" style="font-size: 20px;"></i>
            <h3>Apply Discount</h3>
        </div>
        <span class="close-modal" id="closeDiscount">&times;</span>
    </div>

    <form action="/admin/handlers/applyDiscount.php" method="POST" class="discount-form ajax-form">
        <input type="hidden" name="book_id" id="bookIdField">

        <label for="discountSelect">Choose Discount</label>
        <div class="discount-select-wrap">
            <select name="discount" id="discountSelect" class="discount-select" required>
                <option value="" disabled selected>Choose Discount</option>
                <option value="0">0% (Remove Discount)</option>
                <option value="5">5% Off</option>
                <option value="10">10% Off</option>
                <option value="15">15% Off</option>
                <option value="20">20% Off</option>
                <option value="25">25% Off</option>
                <option value="30">30% Off</option>
                <option value="35">35% Off</option>
                <option value="40">40% Off</option>
                <option value="45">45% Off</option>
                <option value="50">50% Off</option>
            </select>
            <i class="ph-bold ph-caret-down select-icon"></i>
        </div>

        <button type="submit" class="btn btn-orange btn-submit-discount">Apply Discount</button>
    </form>
</div>

<div class="modal-overlay" id="deleteOverlay"></div>

<div class="confirm-modal" id="deleteModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 22px; color: var(--neo-pink);"></i>
            <h3>Delete Book</h3>
        </div>
        <span class="close-modal" id="closeDelete">&times;</span>
    </div>
    <div class="confirm-modal-body">
        <p>Are you sure you want to delete <span class="confirm-book-badge" id="deleteBookTitle"></span>?</p>
        <p class="confirm-modal-subtext">This action cannot be undone and will permanently remove this book from your catalog.</p>
    </div>
    <form action="/admin/handlers/deleteBook.php" method="POST" class="ajax-form" id="deleteBookForm">
        <input type="hidden" name="id" id="deleteBookIdField">
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
            <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
    </form>
</div>

</div>

<script>
    const discountModal = document.getElementById("discountModal");
    const discountOverlay = document.getElementById("discountOverlay");
    const closeDiscountBtn = document.getElementById("closeDiscount");
    const bookIdField = document.getElementById("bookIdField");

    document.querySelectorAll(".openDiscountModal").forEach(btn => {
        btn.addEventListener("click", () => {
            const bookId = btn.getAttribute("data-book-id");
            bookIdField.value = bookId;
            discountModal.classList.add("active");
            discountOverlay.classList.add("active");
        });
    });

    function closeDiscountModal() {
        discountModal.classList.remove("active");
        discountOverlay.classList.remove("active");
    }

    if (closeDiscountBtn) closeDiscountBtn.addEventListener("click", closeDiscountModal);
    if (discountOverlay) discountOverlay.addEventListener("click", closeDiscountModal);

    const deleteModal = document.getElementById("deleteModal");
    const deleteOverlay = document.getElementById("deleteOverlay");
    const closeDeleteBtn = document.getElementById("closeDelete");
    const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
    const deleteBookIdField = document.getElementById("deleteBookIdField");
    const deleteBookTitle = document.getElementById("deleteBookTitle");

    document.querySelectorAll(".openDeleteModal").forEach(btn => {
        btn.addEventListener("click", () => {
            const bookId = btn.getAttribute("data-book-id");
            const bookTitle = btn.getAttribute("data-book-title");
            deleteBookIdField.value = bookId;
            deleteBookTitle.textContent = bookTitle;
            deleteModal.classList.add("active");
            deleteOverlay.classList.add("active");
        });
    });

    function closeDeleteModal() {
        deleteModal.classList.remove("active");
        deleteOverlay.classList.remove("active");
    }

    if (closeDeleteBtn) closeDeleteBtn.addEventListener("click", closeDeleteModal);
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener("click", closeDeleteModal);
    if (deleteOverlay) deleteOverlay.addEventListener("click", closeDeleteModal);
</script>

<script src="/assests/js/admin.js"></script>

</body>

</html>