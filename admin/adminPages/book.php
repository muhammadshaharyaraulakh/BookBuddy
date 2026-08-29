<?php
require __DIR__ . "/../../config/config.php";
require __DIR__ . "/../../includes/dashboardHeader.php";
$allCategories = getCategories($connection);
$fetchNoDeals = $connection->prepare("
    SELECT 
        b.*
    FROM book b
    LEFT JOIN deals d 
        ON b.id = d.book_id
    WHERE d.id IS NULL
");
$fetchNoDeals->execute();
$books = $fetchNoDeals->fetchAll(PDO::FETCH_OBJ);
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
                    <form action="/admin/adminPages/updateBook.php" method="get" class="bookform">
                        <input type="hidden" name="id" value="<?php echo $book->id ?>">
                        <button class="btn btn-primary">Update</button>
                    </form>


                    <button class="btn btn-orange openDiscountModal" data-book-id="<?= $book->id ?>">
                        Discount
                    </button>



                    <form action="/admin/handlers/deleteBook.php" method="post" class="bookform ajax-form">
                        <input type="hidden" name="id" value="<?= $book->id ?>">
                        <button class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>



    </div>
</div>
<div class="modal-overlay" id="discountOverlay"></div>

<!-- Discount Modal -->
<div class="discount-modal" id="discountModal">

    <div class="modal-header">
        <h3>Apply Discount</h3>
        <span class="close-modal" id="closeDiscount">&times;</span>
    </div>

    <form action="/admin/handlers/applyDiscount.php" method="POST" class="discount-form ajax-form">

        <input type="hidden" name="book_id" id="bookIdField">


        <label>Select Discount:</label>
        <select name="discount" required>
            <option value="">Choose Discount</option>
            <option value="0">0%</option>
            <option value="5">5%</option>
            <option value="10">10%</option>
            <option value="15">15%</option>
            <option value="20">20%</option>
            <option value="25">25%</option>
            <option value="30">30%</option>
            <option value="35">35%</option>
            <option value="40">40%</option>
            <option value="45">45%</option>
            <option value="50">50%</option>
        </select>

        <button type="submit" class="btn btn-orange">Apply</button>
    </form>

</div>
</div>
<script>
    const modal = document.getElementById("discountModal");
    const overlay = document.getElementById("discountOverlay");
    const closeBtn = document.getElementById("closeDiscount");
    const bookIdField = document.getElementById("bookIdField");

    document.querySelectorAll(".openDiscountModal").forEach(btn => {
        btn.addEventListener("click", () => {
            const bookId = btn.getAttribute("data-book-id");
            bookIdField.value = bookId;

            modal.classList.add("active");
            overlay.classList.add("active");
        });
    });

    function closeModal() {
        modal.classList.remove("active");
        overlay.classList.remove("active");
    }

    closeBtn.addEventListener("click", closeModal);
    overlay.addEventListener("click", closeModal);
</script>

<script src="/assests/js/admin.js"></script>

</body>

</html>