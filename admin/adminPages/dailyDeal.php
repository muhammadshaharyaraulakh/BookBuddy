<?php 
require __DIR__."/../../config/config.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";
require __DIR__."/../../includes/dashboardHeader.php";

$fetch = $connection->prepare("
    SELECT * FROM book 
    WHERE Discount_Percentage IS NULL 
       OR Discount_Percentage = 0
");

$fetch->execute();
$result = $fetch->fetchAll(PDO::FETCH_OBJ);

$fetchDeal = $connection->prepare("
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
       AND d.status = 'active'
    ORDER BY d.id DESC
    LIMIT 1
");
$fetchDeal->execute();
$dealBook = $fetchDeal->fetch(PDO::FETCH_OBJ);
$hasActiveDeal = !empty($dealBook);
?>

<div id="content">

    <div class="deal-page">
        <div class="form-wrapper">
            <div class="form-container">
                <h4 class="form-title">Set New Deal</h4>

                <?php if ($hasActiveDeal): ?>
                    <div class="deal-active-notice">
                        <i class="ph-bold ph-lock-key"></i>
                        <div>
                            <strong>Active Deal Running:</strong> Only 1 book can be on daily deal at a time. The deal form is locked and will automatically re-enable once the current active deal expires.
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="/admin/handlers/deals.php" class="ajax-form">
                    <div class="form-group">
                        <label>Select Book</label>
                        <div class="input-wrapper">
                            <select name="id" <?= $hasActiveDeal ? 'disabled' : '' ?> required>
                                <option value="" disabled selected>Select Book for Deal</option>
                                <?php foreach($result as $book): ?>
                                    <option value="<?= $book->id ?>"><?= htmlspecialchars($book->title) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="error id"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Discounted Percentage</label>
                        <div class="input-wrapper">
                            <input type="number" name="percentage" placeholder="70%" min="1" max="70" <?= $hasActiveDeal ? 'disabled' : '' ?> required>
                            <div class="error percentage"></div>
                        </div>
                    </div>

                    <?php if ($hasActiveDeal): ?>
                        <button type="submit" class="btn-submit orange" disabled>
                            Deal Active (Locked)
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn-submit orange">
                            Activate Deal
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if ($hasActiveDeal && $dealBook): ?>
            <div class="active-deal-wrapper">
                <div class="deal-preview-card single-deal-card">
                    <h4 class="form-title">Currently Active Deal</h4>
                    <div class="deal-visual">
                        <img src="/images/<?= htmlspecialchars($dealBook->coverImage) ?>" alt="Book Cover">
                        <div class="deal-badge">-<?= htmlspecialchars($dealBook->discount_percentage) ?>%</div>
                    </div>
                    <div class="deal-info">
                        <h3><?= htmlspecialchars($dealBook->title) ?></h3>
                        <div class="price-box">
                            <span class="old-price">$<?= htmlspecialchars($dealBook->Original_Price) ?></span>
                            <span class="new-price">$<?= htmlspecialchars($dealBook->Discount_Price) ?></span>
                        </div>
                        <div class="countdown">
                            <i class="ph-bold ph-clock"></i> Ends in: <strong><?= htmlspecialchars($dealBook->end_time) ?></strong>
                        </div>
                        <div class="deal-actions">
                            <button type="button" class="btn btn-danger openRemoveDealModal" data-deal-id="<?= $dealBook->deal_id ?>" data-book-id="<?= $dealBook->id ?>" data-book-title="<?= htmlspecialchars($dealBook->title, ENT_QUOTES) ?>">
                                Remove from Daily Deal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<div class="modal-overlay" id="removeDealOverlay"></div>

<div class="confirm-modal" id="removeDealModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 22px; color: var(--neo-pink);"></i>
            <h3>Remove from Daily Deal</h3>
        </div>
        <span class="close-modal" id="closeRemoveDeal">&times;</span>
    </div>
    <div class="confirm-modal-body">
        <p>Are you sure you want to remove <span class="confirm-book-badge" id="removeDealBookTitle"></span> from the Daily Deal?</p>
        <div class="confirm-modal-warning-box">
            <i class="ph-bold ph-info"></i>
            <p>This will immediately end the daily deal and restore the original price for this book. The deal form will be unlocked.</p>
        </div>
    </div>
    <form action="/admin/handlers/deleteDeal.php" method="POST" class="ajax-form" id="removeDealForm">
        <input type="hidden" name="deal_id" id="removeDealIdField">
        <input type="hidden" name="book_id" id="removeDealBookIdField">
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelRemoveDealBtn">Cancel</button>
            <button type="submit" class="btn btn-danger" id="confirmRemoveDealBtn">Remove Deal</button>
        </div>
    </form>
</div>

</div>

<script>
    const removeDealModal = document.getElementById('removeDealModal');
    const removeDealOverlay = document.getElementById('removeDealOverlay');
    const closeRemoveDealBtn = document.getElementById('closeRemoveDeal');
    const cancelRemoveDealBtn = document.getElementById('cancelRemoveDealBtn');
    const removeDealIdField = document.getElementById('removeDealIdField');
    const removeDealBookIdField = document.getElementById('removeDealBookIdField');
    const removeDealBookTitle = document.getElementById('removeDealBookTitle');

    document.querySelectorAll('.openRemoveDealModal').forEach(btn => {
        btn.addEventListener('click', () => {
            const dealId = btn.getAttribute('data-deal-id');
            const bookId = btn.getAttribute('data-book-id');
            const bookTitle = btn.getAttribute('data-book-title');

            if (removeDealIdField) removeDealIdField.value = dealId;
            if (removeDealBookIdField) removeDealBookIdField.value = bookId;
            if (removeDealBookTitle) removeDealBookTitle.textContent = bookTitle;

            if (removeDealModal) removeDealModal.classList.add('active');
            if (removeDealOverlay) removeDealOverlay.classList.add('active');
        });
    });

    function closeRemoveDeal() {
        if (removeDealModal) removeDealModal.classList.remove('active');
        if (removeDealOverlay) removeDealOverlay.classList.remove('active');
    }

    if (closeRemoveDealBtn) closeRemoveDealBtn.addEventListener('click', closeRemoveDeal);
    if (cancelRemoveDealBtn) cancelRemoveDealBtn.addEventListener('click', closeRemoveDeal);
    if (removeDealOverlay) removeDealOverlay.addEventListener('click', closeRemoveDeal);
</script>

<script src="/assests/js/admin.js"></script>
</body>
</html>
