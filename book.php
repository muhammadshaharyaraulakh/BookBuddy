<?php
require __DIR__ . "/config/config.php";

$book_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$book_id) {
    header("Location: /shop.php");
    exit;
}

$stmt = $connection->prepare("
    SELECT b.*, c.title AS category_title 
    FROM book b 
    LEFT JOIN categories c ON b.category_id = c.id 
    WHERE b.id = :id 
    LIMIT 1
");
$stmt->execute([':id' => $book_id]);
$book = $stmt->fetch(PDO::FETCH_OBJ);

if (!$book) {
    header("Location: /shop.php");
    exit;
}

$hasDiscount = !empty($book->Discount_Percentage) && (int)$book->Discount_Percentage > 0;
$inStock = (int)$book->Stock > 0;

// Check if this book is currently in an active Daily Deal
$dealCheck = $connection->prepare("
    SELECT id, discount_percentage, end_time 
    FROM deals 
    WHERE book_id = :bid 
      AND status = 'active' 
      AND CURRENT_TIMESTAMP < end_time 
    LIMIT 1
");
$dealCheck->execute([':bid' => $book_id]);
$activeDeal = $dealCheck->fetch(PDO::FETCH_OBJ);
$isOnDailyDeal = ($activeDeal !== false && !empty($activeDeal));

require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<div class="book-detail-container">
    <nav class="book-breadcrumb">
        <a href="/"><i class="ph-bold ph-house"></i> Home</a>
        <span>/</span>
        <a href="/shop.php">Shop</a>
        <?php if (!empty($book->category_title)): ?>
            <span>/</span>
            <a href="/shop.php?category=<?= urlencode(strtolower($book->category_title)) ?>"><?= htmlspecialchars($book->category_title) ?></a>
        <?php endif; ?>
        <span>/</span>
        <span class="active-crumb"><?= htmlspecialchars($book->title) ?></span>
    </nav>

    <div class="book-detail-layout">
        <div class="book-detail-visual">
            <div class="book-detail-cover-card">
                <?php if ($hasDiscount): ?>
                    <span class="book-discount-badge">-<?= (int)$book->Discount_Percentage ?>%</span>
                <?php endif; ?>
                <img src="/images/<?= htmlspecialchars($book->coverImage) ?>" alt="<?= htmlspecialchars($book->title) ?>" class="book-detail-img" onerror="this.src='/images/logo.png'" />
            </div>
            <div class="book-trust-pill">
                <i class="ph-bold ph-seal-check"></i>
                <span>100% Original Publisher Certified</span>
            </div>
        </div>

        <div class="book-detail-info">
            <div class="book-detail-header">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <?php if (!empty($book->category_title)): ?>
                        <span class="detail-genre-badge"><?= htmlspecialchars($book->category_title) ?></span>
                    <?php endif; ?>
                    <?php if ($isOnDailyDeal): ?>
                        <span class="cart-badge badge-deal">
                            <i class="ph-bold ph-lightning"></i> Active Daily Deal
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="book-detail-title"><?= htmlspecialchars($book->title) ?></h1>
                <div class="book-detail-author-row">
                    <span class="detail-author-label">Author:</span>
                    <span class="detail-author-name"><?= htmlspecialchars($book->author) ?></span>
                </div>
                <div class="book-detail-rating-row">
                    <div class="testimonial-stars">
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                        <i class="ph-bold ph-star"></i>
                    </div>
                    <span class="detail-rating-text">5.0 &bull; Verified Authentic Edition</span>
                </div>
            </div>

            <div class="book-detail-pricing">
                <?php if ($hasDiscount): ?>
                    <span class="detail-price-current">$<?= number_format($book->Discount_Price, 2) ?></span>
                    <span class="detail-price-original">$<?= number_format($book->Original_Price, 2) ?></span>
                    <span class="detail-price-saved">Save $<?= number_format($book->Original_Price - $book->Discount_Price, 2) ?></span>
                <?php else: ?>
                    <span class="detail-price-current">$<?= number_format($book->Original_Price, 2) ?></span>
                <?php endif; ?>
            </div>

            <div class="book-detail-synopsis">
                <h2 class="synopsis-title">Book Overview</h2>
                <p class="synopsis-text"><?= nl2br(htmlspecialchars($book->description_para_1)) ?></p>
                <?php if (!empty($book->description_para_2)): ?>
                    <p class="synopsis-text" style="margin-top: 1rem;"><?= nl2br(htmlspecialchars($book->description_para_2)) ?></p>
                <?php endif; ?>
            </div>

            <div class="book-detail-perks">
                <div class="perk-item">
                    <i class="ph-bold ph-truck"></i>
                    <div>
                        <strong>Nationwide Delivery</strong>
                        <span>Delivered to your doorstep in 2 to 3 business days.</span>
                    </div>
                </div>
                <div class="perk-item">
                    <i class="ph-bold ph-shield-check"></i>
                    <div>
                        <strong>Doorstep Parcel Inspection</strong>
                        <span>Verify book authenticity and condition before receiving.</span>
                    </div>
                </div>
            </div>

            <!-- Section: Book Purchase & Cart Actions -->
            <div class="book-detail-actions">
                <?php if ($isOnDailyDeal): ?>
                    <button type="button" class="btn-detail-add-cart" disabled style="opacity: 0.85; cursor: not-allowed; background: #FEF3C7; border: 2.5px solid var(--neo-black); color: #92400E; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="ph-bold ph-lightning"></i> Active Daily Deal (Cannot be added to cart)
                    </button>
                <?php elseif ($book->Stock <= 0): ?>
                    <button type="button" class="btn-detail-add-cart" disabled style="opacity: 0.55; cursor: not-allowed; background: var(--neo-gray-muted); border-color: var(--neo-black); color: var(--neo-black);">
                        <i class="ph-bold ph-prohibit"></i> Out of Stock
                    </button>
                <?php else: ?>
                    <div class="cart-qty-control">
                        <button type="button" class="cart-qty-btn" id="qty-decrease" aria-label="Decrease quantity">-</button>
                        <input type="text" class="cart-qty-input" id="book-qty" value="1" readonly data-max="<?= min(10, (int)$book->Stock) ?>" data-stock="<?= (int)$book->Stock ?>" />
                        <button type="button" class="cart-qty-btn" id="qty-increase" aria-label="Increase quantity">+</button>
                    </div>
                    <button type="button" class="btn-detail-add-cart" id="btn-add-to-cart" data-book-id="<?= $book->id ?>" data-book-title="<?= htmlspecialchars($book->title, ENT_QUOTES) ?>" data-stock="<?= (int)$book->Stock ?>">
                        <i class="ph-bold ph-shopping-bag"></i> Add to Cart
                    </button>
                <?php endif; ?>
            </div>

            <div class="book-specs-card">
                <h3 class="specs-title">Book Specifications</h3>
                <div class="specs-grid">
                    <div class="spec-row">
                        <span class="spec-label">Publisher</span>
                        <span class="spec-value"><?= htmlspecialchars($book->Publisher) ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Format</span>
                        <span class="spec-value">Paperback, Pristine Print</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Language</span>
                        <span class="spec-value">English</span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Publish Date</span>
                        <span class="spec-value"><?= !empty($book->publishDate) ? date("F j, Y", strtotime($book->publishDate)) : 'Standard Edition' ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">ISBN-13</span>
                        <span class="spec-value"><?= htmlspecialchars($book->ISBN ?? 'N/A') ?></span>
                    </div>
                    <div class="spec-row">
                        <span class="spec-label">Availability</span>
                        <?php if ($inStock): ?>
                            <span class="spec-value" style="color: #166534; font-weight: 800;">In Stock (<?= (int)$book->Stock ?> Available)</span>
                        <?php else: ?>
                            <span class="spec-value" style="color: #dc2626; font-weight: 800;">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
