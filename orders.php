<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<div class="page-header-banner">
    <h1 class="page-header-title">My Orders</h1>
    <p class="page-header-subtitle">Track your literary shipments, delivery status and purchase history</p>
</div>

<div class="orders-layout">
    <div class="order-history-card">
        <div class="order-card-header">
            <div>
                <div style="font-size: 16px; font-weight: 800;">Order #ORD-104</div>
                <div style="font-size: 13px; font-weight: 600; color: #6B7280;">Placed on September 04, 2026 &bull; Cash on Delivery</div>
            </div>
            <span class="order-badge-pill order-badge-delivered">
                <i class="ph-bold ph-check"></i> Delivered
            </span>
        </div>

        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <img src="/images/logo.png" alt="Dune" style="width: 50px; height: 65px; border: 2px solid var(--neo-black); border-radius: 4px; object-fit: cover;" />
            <div style="flex-grow: 1;">
                <div style="font-size: 15px; font-weight: 800;">Dune (Deluxe Edition)</div>
                <div style="font-size: 13px; font-weight: 600; color: #6B7280;">Qty: 1 &bull; $20.00</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 16px; font-weight: 800;">$20.00</div>
                <span style="font-size: 12px; font-weight: 700; color: var(--neo-teal);">Paid upon delivery</span>
            </div>
        </div>
    </div>

    <div class="order-history-card">
        <div class="order-card-header">
            <div>
                <div style="font-size: 16px; font-weight: 800;">Order #ORD-109</div>
                <div style="font-size: 13px; font-weight: 600; color: #6B7280;">Placed on September 07, 2026 &bull; Cash on Delivery</div>
            </div>
            <span class="order-badge-pill order-badge-processing">
                <i class="ph-bold ph-clock"></i> In Transit
            </span>
        </div>

        <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
            <img src="/images/logo.png" alt="The Pragmatic Programmer" style="width: 50px; height: 65px; border: 2px solid var(--neo-black); border-radius: 4px; object-fit: cover;" />
            <div style="flex-grow: 1;">
                <div style="font-size: 15px; font-weight: 800;">The Pragmatic Programmer (20th Anniversary)</div>
                <div style="font-size: 13px; font-weight: 600; color: #6B7280;">Qty: 1 &bull; $45.00</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 16px; font-weight: 800;">$45.00</div>
                <span style="font-size: 12px; font-weight: 700; color: #D97706;">Pay on Arrival</span>
            </div>
        </div>
    </div>
</div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
