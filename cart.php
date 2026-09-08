<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<div class="page-header-banner">
    <h1 class="page-header-title">Shopping Cart</h1>
    <p class="page-header-subtitle">Review your selected literary treasures before checkout</p>
</div>

<div class="cart-layout">
    <div class="cart-table-card">
        <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; padding-bottom: 12px; border-bottom: 2px solid var(--neo-black);">
            Your Items (2)
        </h2>

        <div class="cart-item-row">
            <img src="/images/logo.png" alt="Clean Code" class="cart-item-img" />
            <div>
                <h3 style="font-size: 16px; font-weight: 800;">Clean Code: Agile Software</h3>
                <span style="font-size: 13px; font-weight: 600; color: #6B7280;">Robert C. Martin</span>
                <div style="font-size: 16px; font-weight: 800; margin-top: 6px;">$42.50</div>
            </div>
            <div class="cart-qty-control">
                <button type="button" class="cart-qty-btn">&minus;</button>
                <input type="text" value="1" readonly class="cart-qty-input">
                <button type="button" class="cart-qty-btn">&plus;</button>
            </div>
            <button type="button" style="background: none; border: none; cursor: pointer; color: var(--neo-pink); font-size: 20px;" aria-label="Remove item">
                <i class="ph-bold ph-trash"></i>
            </button>
        </div>

        <div class="cart-item-row">
            <img src="/images/logo.png" alt="Atomic Habits" class="cart-item-img" />
            <div>
                <h3 style="font-size: 16px; font-weight: 800;">Atomic Habits</h3>
                <span style="font-size: 13px; font-weight: 600; color: #6B7280;">James Clear</span>
                <div style="font-size: 16px; font-weight: 800; margin-top: 6px;">$25.00</div>
            </div>
            <div class="cart-qty-control">
                <button type="button" class="cart-qty-btn">&minus;</button>
                <input type="text" value="1" readonly class="cart-qty-input">
                <button type="button" class="cart-qty-btn">&plus;</button>
            </div>
            <button type="button" style="background: none; border: none; cursor: pointer; color: var(--neo-pink); font-size: 20px;" aria-label="Remove item">
                <i class="ph-bold ph-trash"></i>
            </button>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
            <a href="/shop.php" style="font-size: 14px; font-weight: 800; text-decoration: underline; display: inline-flex; align-items: center; gap: 6px;">
                <i class="ph-bold ph-arrow-left"></i> Continue Shopping
            </a>
            <span style="font-size: 13px; font-weight: 700; color: var(--neo-teal); display: inline-flex; align-items: center; gap: 6px;">
                <i class="ph-bold ph-shield-check"></i> Cash on Delivery Guaranteed
            </span>
        </div>
    </div>

    <aside class="cart-summary-card">
        <h2 style="font-size: 20px; font-weight: 800; text-transform: uppercase; padding-bottom: 12px; border-bottom: 2px solid var(--neo-black);">
            Order Summary
        </h2>

        <div class="summary-row">
            <span>Subtotal</span>
            <span>$67.50</span>
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
            <span>$67.50</span>
        </div>

        <button type="button" class="btn-checkout-cod">
            Place Order (COD) <i class="ph-bold ph-arrow-right"></i>
        </button>
    </aside>
</div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
