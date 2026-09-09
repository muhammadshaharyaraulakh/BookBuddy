<?php
require __DIR__ . "/../../config/config.php";
require __DIR__ . "/../../includes/dashboardHeader.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";

$allCategories = getCategories($connection);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : 'all';

$whereClauses = ["b.Stock < 5"];
$params = [];

if ($categoryFilter !== 'all' && is_numeric($categoryFilter)) {
    $whereClauses[] = "b.category_id = :cat_id";
    $params[':cat_id'] = (int)$categoryFilter;
}

$whereSql = implode(' AND ', $whereClauses);

$countQuery = $connection->prepare("SELECT COUNT(*) FROM book b WHERE $whereSql");
$countQuery->execute($params);
$totalBooks = (int)$countQuery->fetchColumn();
$totalPages = max(1, (int)ceil($totalBooks / $limit));

if ($page > $totalPages && $totalBooks > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

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
    WHERE $whereSql
    ORDER BY b.id DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) {
    $fetchBooks->bindValue($k, $v, PDO::PARAM_INT);
}
$fetchBooks->bindValue(':limit', $limit, PDO::PARAM_INT);
$fetchBooks->bindValue(':offset', $offset, PDO::PARAM_INT);
$fetchBooks->execute();
$books = $fetchBooks->fetchAll(PDO::FETCH_OBJ);
?>

<div id="content">

    <div class="category-filter">
        <a href="?category=all&page=1" class="category-btn <?= $categoryFilter === 'all' ? 'active' : '' ?>" data-filter="all">All</a>
        <?php foreach ($allCategories as $category): ?>
            <a href="?category=<?= $category->id ?>&page=1" class="category-btn <?= (string)$categoryFilter === (string)$category->id ? 'active' : '' ?>" data-filter="<?= $category->id ?>"><?= htmlspecialchars($category->title) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="books-grid">
        <?php if (empty($books)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 24px; background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); box-shadow: var(--neo-shadow);">
                <div style="font-size: 36px; margin-bottom: 8px;"><i class="ph-bold ph-check-circle"></i></div>
                <h3 style="font-size: 20px; font-weight: 800; color: var(--neo-black);">No Out of Stock Books</h3>
                <p style="color: var(--neo-gray-muted); margin-top: 8px;">All books currently have healthy inventory levels (5 or more units in stock).</p>
            </div>
        <?php else: ?>
            <?php foreach ($books as $book): ?>
                <div class="book-card" data-category="<?= $book->category_id ?>">
                    <img src="/images/<?= $book->coverImage ?>" class="book-img">
                    <div class="book-title"><?= htmlspecialchars($book->title) ?></div>
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

                    <div class="stock-status out-of-stock">Out of Stock (<?= $book->Stock ?>)</div>
                    <div class="book-actions">
                        <?php if ($book->is_on_deal > 0): ?>
                            <button type="button" class="btn btn-primary" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot update book while in daily deal">Update</button>
                            <button type="button" class="btn btn-orange" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot apply discount while in daily deal">Discount</button>
                            <button type="button" class="btn btn-danger" disabled style="opacity: 0.45; cursor: not-allowed;" title="Cannot delete book while in daily deal">Delete</button>
                        <?php else: ?>
                            <form action="/updatebook" method="get" class="bookform">
                                <input type="hidden" name="id" value="<?= $book->id ?>">
                                <input type="hidden" name="page" value="<?= $page ?>">
                                <input type="hidden" name="from" value="outofstock">
                                <?php if ($categoryFilter !== 'all'): ?>
                                    <input type="hidden" name="category" value="<?= htmlspecialchars($categoryFilter) ?>">
                                <?php endif; ?>
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
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <?php
        $categoryParam = $categoryFilter !== 'all' ? '&category=' . urlencode($categoryFilter) : '';
        ?>
        <div class="admin-pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $categoryParam ?>" class="pagination-btn">
                    <i class="ph-bold ph-caret-left"></i> Previous
                </a>
            <?php else: ?>
                <span class="pagination-btn pagination-disabled">
                    <i class="ph-bold ph-caret-left"></i> Previous
                </span>
            <?php endif; ?>

            <div class="pagination-pages">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-page-num active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= $categoryParam ?>" class="pagination-page-num"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $categoryParam ?>" class="pagination-btn">
                    Next <i class="ph-bold ph-caret-right"></i>
                </a>
            <?php else: ?>
                <span class="pagination-btn pagination-disabled">
                    Next <i class="ph-bold ph-caret-right"></i>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
