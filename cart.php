<?php
// Section 1: Data Initialization & Handlers Loading
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/handlers/cartDetails.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<!-- Section 2: Page Header & Notifications Banner -->
<div class="page-header-banner">
    <h1 class="page-header-title">Shopping Cart</h1>
    <p class="page-header-subtitle">Review your selected literary treasures before checkout</p>
</div>

<?php if (!empty($cartNotifications)): ?>
    <div style="max-width: 1100px; margin: 0 auto 20px; padding: 0 24px;">
        <?php foreach ($cartNotifications as $notice): ?>
            <div style="background: #FEF3C7; border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 14px 18px; font-weight: 700; color: #92400E; display: flex; align-items: center; gap: 10px; box-shadow: var(--neo-shadow-sm); margin-bottom: 10px;">
                <i class="ph-bold ph-warning-circle" style="font-size: 22px;"></i>
                <span><?= htmlspecialchars($notice) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!$user_id): ?>
    <!-- Section 3: Guest User State (Login Required Card) -->
    <div style="max-width: 600px; margin: 40px auto 80px; padding: 0 20px;">
        <div style="background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 48px 24px; text-align: center; box-shadow: var(--neo-shadow-lg);">
            <div style="font-size: 48px; margin-bottom: 12px; color: var(--neo-pink);">
                <i class="ph-bold ph-lock-key"></i>
            </div>
            <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Log In to View Your Cart</h2>
            <p style="color: var(--neo-gray-muted); font-size: 15px; margin-bottom: 24px;">Please sign in with your account to access and synchronize your cart across devices.</p>
            <a href="/login" class="neo-btn btn-yellow" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-weight: 800; text-decoration: none; border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); box-shadow: var(--neo-shadow); background: var(--neo-yellow); color: var(--neo-black);">
                <i class="ph-bold ph-sign-in"></i> Sign In to Account
            </a>
        </div>
    </div>
<?php elseif (empty($cartItems)): ?>
    <!-- Section 4: Empty Cart State (Browse Books Card) -->
    <div style="max-width: 640px; margin: 40px auto 80px; padding: 0 20px;">
        <div style="background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 48px 24px; text-align: center; box-shadow: var(--neo-shadow-lg);">
            <div style="font-size: 52px; margin-bottom: 14px; color: var(--neo-yellow);">
                <i class="ph-bold ph-shopping-cart"></i>
            </div>
            <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Your Cart is Empty</h2>
            <p style="color: var(--neo-gray-muted); font-size: 15px; margin-bottom: 24px;">You have not added any books to your cart yet. Explore our curated selection of fine books.</p>
            <a href="/shop.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; font-weight: 800; text-decoration: none; border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); box-shadow: var(--neo-shadow); background: var(--neo-teal); color: var(--neo-black);">
                <i class="ph-bold ph-storefront"></i> Explore Books
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Section 5: Active Cart Table & Item Rows (Live Prices, Deals, Stock Badges) -->
    <div class="cart-layout" id="cart-main-container">
        <div class="cart-table-card">
            <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; padding-bottom: 12px; border-bottom: 2px solid var(--neo-black); display: flex; align-items: center; justify-content: space-between;">
                <span>Your Items (<span id="cart-header-count"><?= count($cartItems) ?></span>)</span>
                <span id="cart-header-stock-badge" style="font-size: 13px; font-weight: 800; color: #991B1B; background: #FEE2E2; border: 1.5px solid var(--neo-black); border-radius: var(--neo-radius-pill); padding: 3px 12px; <?= $hasStockIssue ? '' : 'display: none;' ?>">
                    <i class="ph-bold ph-warning"></i> Stock issues detected
                </span>
            </h2>

            <div id="cart-items-wrapper">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item-row <?= ($item->is_out_of_stock || !empty($item->is_daily_deal)) ? 'item-disabled' : '' ?>" id="cart-row-<?= $item->cart_id ?>" data-cart-id="<?= $item->cart_id ?>" data-stock="<?= (int)$item->Stock ?>" data-daily-deal="<?= !empty($item->is_daily_deal) ? '1' : '0' ?>">
                        <img src="/images/<?= htmlspecialchars($item->coverImage ?? 'logo.png') ?>" alt="<?= htmlspecialchars($item->title) ?>" class="cart-item-img" onerror="this.src='/images/logo.png'" />
                        
                        <div>
                            <a href="/book?id=<?= $item->id ?>" style="font-size: 16px; font-weight: 800; color: var(--neo-black); text-decoration: none;">
                                <?= htmlspecialchars($item->title) ?>
                            </a>
                            <div style="font-size: 13px; font-weight: 600; color: #6B7280; margin-top: 2px;">
                                <?= htmlspecialchars($item->author) ?>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 6px;" id="item-badges-<?= $item->cart_id ?>">
                                <?php if (!empty($item->is_daily_deal)): ?>
                                    <span class="cart-badge badge-danger">
                                        <i class="ph-bold ph-lightning-slash"></i> In Daily Deal (Remove to Checkout)
                                    </span>
                                <?php elseif ($item->is_out_of_stock): ?>
                                    <span class="cart-badge badge-danger">
                                        <i class="ph-bold ph-prohibit"></i> Out of Stock
                                    </span>
                                <?php elseif ((int)$item->Stock < 5): ?>
                                    <span class="cart-badge badge-warning">
                                        <i class="ph-bold ph-warning"></i> Only <?= (int)$item->Stock ?> left in stock
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="font-size: 16px; font-weight: 800; margin-top: 8px; display: flex; align-items: baseline; gap: 8px;">
                                <span class="row-live-price">$<?= number_format($item->effective_price, 2) ?></span>
                                <?php if ($item->Discount_Percentage !== null && $item->Discount_Percentage > 0): ?>
                                    <span style="font-size: 13px; font-weight: 700; color: #9CA3AF; text-decoration: line-through;">
                                        $<?= number_format($item->Original_Price, 2) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Section 6: Interactive Quantity Steppers & Removal Triggers -->
                        <div class="cart-qty-control">
                            <button type="button" class="cart-qty-btn btn-cart-dec" data-cart-id="<?= $item->cart_id ?>" <?= (!empty($item->is_daily_deal) || $item->is_out_of_stock) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?> aria-label="Decrease quantity">&minus;</button>
                            <input type="text" value="<?= $item->cart_quantity ?>" readonly class="cart-qty-input row-qty-input" data-cart-id="<?= $item->cart_id ?>" data-stock="<?= (int)$item->Stock ?>" data-max="<?= min(10, max(1, (int)$item->Stock)) ?>" />
                            <button type="button" class="cart-qty-btn btn-cart-inc" data-cart-id="<?= $item->cart_id ?>" <?= (!empty($item->is_daily_deal) || $item->is_out_of_stock) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '' ?> aria-label="Increase quantity">&plus;</button>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="cart-row-subtotal" id="row-subtotal-<?= $item->cart_id ?>" style="font-weight: 800; font-size: 17px; min-width: 70px; text-align: right;">
                                $<?= number_format($item->item_subtotal, 2) ?>
                            </span>
                            <button type="button" class="btn-cart-remove" data-cart-id="<?= $item->cart_id ?>" data-book-title="<?= htmlspecialchars($item->title, ENT_QUOTES) ?>" style="background: none; border: none; cursor: pointer; color: var(--neo-pink); font-size: 20px; padding: 4px; display: inline-flex; align-items: center; justify-content: center;" aria-label="Remove item">
                                <i class="ph-bold ph-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 2px solid #E5E7EB; flex-wrap: wrap; gap: 12px;">
                <a href="/shop.php" style="font-size: 14px; font-weight: 800; text-decoration: underline; display: inline-flex; align-items: center; gap: 6px; color: var(--neo-black);">
                    <i class="ph-bold ph-arrow-left"></i> Continue Shopping
                </a>
                <span style="font-size: 13px; font-weight: 700; color: var(--neo-teal); display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-shield-check"></i> Authentic Books &amp; Express Delivery
                </span>
            </div>
        </div>

        <!-- Section 7: Order Summary & Live Financial Totals -->
        <aside class="cart-summary-card">
            <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; padding-bottom: 12px; border-bottom: 2px solid var(--neo-black);">
                Order Summary
            </h2>

            <div class="summary-row">
                <span>Subtotal</span>
                <span id="cart-summary-subtotal">$<?= number_format($allTotal, 2) ?></span>
            </div>

            <div class="summary-row">
                <span>Delivery</span>
                <span style="color: var(--neo-teal); font-weight: 800;">Free</span>
            </div>

            <div class="summary-row">
                <span>Payment Method</span>
                <span style="font-weight: 800;">Cash On Delivery</span>
            </div>

            <div class="summary-total-row">
                <span>Total Payable</span>
                <span id="cart-summary-total">$<?= number_format($total, 2) ?></span>
            </div>

            <!-- Section 8: Checkout Action & Stock Compliance Validation -->
            <div id="checkout-container" style="display: flex; flex-direction: column; gap: 12px;">
                <div id="checkout-stock-warning" style="background: #FEE2E2; border: 1.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 10px 14px; font-size: 13px; font-weight: 700; color: #991B1B; <?= $hasStockIssue ? '' : 'display: none;' ?>">
                    <i class="ph-bold ph-warning"></i> Remove out-of-stock items, daily deal items, or reduce over-limit quantities to checkout.
                </div>
                <button type="button" id="btn-checkout-disabled" class="btn-checkout-cod" disabled style="opacity: 0.55; cursor: not-allowed; background: var(--neo-gray-muted); width: 100%; <?= $hasStockIssue ? '' : 'display: none;' ?>">
                    Place Order (COD) <i class="ph-bold ph-prohibit"></i>
                </button>
                <a href="/handlers/place_order.php" id="btn-checkout-active" class="btn-checkout-cod" style="text-align: center; text-decoration: none; width: 100%; <?= $hasStockIssue ? 'display: none;' : 'display: flex;' ?>">
                    Place Order (COD) <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>
        </aside>
    </div>
<?php endif; ?>

<!-- Section: Neo-Brutalist Toast Notification Container -->
<div class="neo-toast-container" id="neoToastContainer"></div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
