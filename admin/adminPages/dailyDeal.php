<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";

$fetch = $connection->prepare("
    SELECT * FROM book 
    WHERE Discount_Percentage IS NULL 
       OR Discount_Percentage = 0
");

$fetch->execute();
$result=$fetch->fetchAll(PDO::FETCH_OBJ);

$fetchDeals = $connection->prepare("
    SELECT 
        b.*,
        d.id AS deal_id,
        d.discount_percentage,
        d.start_time,
        d.end_time
    FROM book b
    INNER JOIN deals d 
        ON b.id = d.book_id
       AND CURRENT_TIMESTAMP < d.end_time
");
$fetchDeals->execute();
$books = $fetchDeals->fetchAll(PDO::FETCH_OBJ);


?>

<div id="content">
    


    <div class="deal-page">
        <div class="form-wrapper">
            <div class="form-container">
                <h4 class="form-title">Set New Deal</h4>

                <form method="post" action="/admin/handlers/deals.php" class="ajax-form">
                    <div class="form-group">
                        <label>Select Book</label>
                        <div class="input-wrapper">
                            
                            <select name="id">
                                <?php foreach($result as $book): ?>
                                    <option value="<?= $book->id ?>"><?= $book->title ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="error id"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Discounted Percentage</label>
                        <div class="input-wrapper">
                            
                            <input type="number" name="percentage" placeholder="70%">
                            <div class="error percentage"></div>
                        </div>
                    </div>

                    <?php if(count($books)==4) :?>
                    <button type="submit" class="btn-submit orange" disabled>
                        Activate Deal
                    </button>
                    <?php else: ?>
                        <button type="submit" class="btn-submit orange">
                        Activate Deal
                    </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>


        <!-- ⭐ 4 CARDS IN RESPONSIVE GRID ⭐ -->
        <div class="deals-grid">
        <?php foreach($books as $book): ?>
        <div class="deal-preview-card">
                <h4 class="form-title">Currently Active</h4>
                <div class="deal-visual">
                    <img src="/images/<?= $book->coverImage ?>" alt="Book Cover">
                    <div class="deal-badge">-<?= $book->discount_percentage ?></div>
                </div>
                <div class="deal-info">
                    <h3><?= $book->title ?></h3>
                    <div class="price-box">
                        <span class="old-price">$<?= $book->Original_Price ?></span>
                        <span class="new-price">$<?= $book->Discount_Price ?></span>
                    </div>
                    <div class="countdown">
                        <i class="fas fa-clock"></i> Ends in: <strong><?= $book->end_time ?></strong>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
        </div>

    </div><!-- /deal-page -->

</div><!-- /content -->

<script src="/assests/js/admin.js"></script>
</body>
</html>
