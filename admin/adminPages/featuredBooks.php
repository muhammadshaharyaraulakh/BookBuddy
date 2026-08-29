<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";



$fetch = $connection->prepare("SELECT b.*
FROM book b
JOIN categories c 
  ON b.category_id = c.id
WHERE c.title = 'Featured';
");
$fetch->execute();
$books = $fetch->fetchAll(PDO::FETCH_OBJ);


?>

<div id="content">
    


    <div class="deal-page">
        <div class="deals-grid">
        <?php foreach($books as $book): ?>
        <div class="deal-preview-card">
                <h4 class="form-title">Currently Active</h4>
                <div class="deal-visual">
                    <img src="/images/<?= $book->coverImage ?>" alt="Book Cover">
                </div>
                <div class="deal-info">
                    <h3><?= $book->title ?></h3>
                    <div class="price-box">
                        <span class="old-price">$<?= $book->Original_Price ?></span>
                        <span class="new-price">$<?= $book->Discount_Price ?></span>
                    </div>
                    
                    <form action="/admin/handlers/deleteFeatured.php" method="post" class="ajax-form">
                        <input type="hidden" name="bookId" value="<?= $book->id ?>">
                        <input hidden name="id" value="<?= $book->deal_id ?>">
                        <button class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    </div><!-- /deal-page -->

</div><!-- /content -->

<script src="/assests/js/admin.js"></script>
</body>
</html>
