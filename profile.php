<?php
require __DIR__ . "/config/config.php";
require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";

$userName = $_SESSION['name'] ?? 'Muhammad Shaharyar';
$userEmail = $_SESSION['gmail'] ?? 'reader@bookbuddy.com';
$userRole = $_SESSION['role'] ?? 'Member';
?>

<div class="page-header-banner">
    <h1 class="page-header-title">Reader Profile</h1>
    <p class="page-header-subtitle">Manage your BookBuddy credentials, preferences and library activity</p>
</div>

<div class="profile-layout">
    <div class="neo-panel-card">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="width: 84px; height: 84px; border: 2.5px solid var(--neo-black); border-radius: 999px; overflow: hidden; box-shadow: var(--neo-shadow-sm); background: var(--neo-yellow);">
                <img src="/userImages/<?= htmlspecialchars($_SESSION['image'] ?? '1788851470_aliraza1.png') ?>" alt="Profile" onerror="this.src='/images/logo.png'" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
            <div>
                <h2 style="font-size: 24px; font-weight: 800;"><?= htmlspecialchars($userName) ?></h2>
                <span style="font-size: 14px; font-weight: 600; color: #6B7280; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph-bold ph-envelope-simple"></i> <?= htmlspecialchars($userEmail) ?>
                </span>
                <div style="margin-top: 6px;">
                    <span style="background: var(--neo-teal); border: 2px solid var(--neo-black); border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 800; text-transform: uppercase;">
                        <?= htmlspecialchars($userRole) ?>
                    </span>
                </div>
            </div>
        </div>

        <hr style="border: 1px solid var(--neo-black); margin: 24px 0;">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <a href="/orders.php" style="background: var(--neo-gray-light); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; align-items: center; gap: 12px;">
                <i class="ph-bold ph-package" style="font-size: 24px; color: var(--neo-teal);"></i>
                <div>
                    <div style="font-size: 15px; font-weight: 800;">My Orders</div>
                    <div style="font-size: 12px; color: #6B7280; font-weight: 600;">Track active deliveries</div>
                </div>
            </a>

            <a href="/cart.php" style="background: var(--neo-gray-light); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; align-items: center; gap: 12px;">
                <i class="ph-bold ph-shopping-cart" style="font-size: 24px; color: var(--neo-yellow);"></i>
                <div>
                    <div style="font-size: 15px; font-weight: 800;">My Cart</div>
                    <div style="font-size: 12px; color: #6B7280; font-weight: 600;">2 items pending</div>
                </div>
            </a>

            <a href="/auth/logout.php" style="background: var(--neo-gray-light); border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); padding: 16px; box-shadow: var(--neo-shadow-sm); display: flex; align-items: center; gap: 12px;">
                <i class="ph-bold ph-sign-out" style="font-size: 24px; color: var(--neo-pink);"></i>
                <div>
                    <div style="font-size: 15px; font-weight: 800;">Log Out</div>
                    <div style="font-size: 12px; color: #6B7280; font-weight: 600;">End current session</div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
