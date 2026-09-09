<?php
protectAdmin();
$currentAdminUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookBuddy - Admin Console</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="/assests/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    
    <div class="overlay" id="overlay"></div>

    <div class="wrapper">
        <button id="sidebarCollapse" class="floating-menu-btn" aria-label="Toggle Menu"><i class="ph-bold ph-list"></i></button>
        <nav id="sidebar">
            <div class="sidebar-header">
                <a href="/index.php" class="logo">
                    <img src="/images/logo.png" alt="BookBuddy Logo">
                    <div class="logo-text">
                        <h3>BookBuddy</h3>
                        <small>Admin Panel</small>
                    </div>
                </a>
                <button id="close-sidebar" aria-label="Close Sidebar"><i class="ph-bold ph-x"></i></button>
            </div>

            <ul class="components">
                <li class="<?= (str_starts_with($currentAdminUri, '/books') || $currentAdminUri === '/admin') ? 'active' : '' ?>"><a href="/books"><i class="ph-bold ph-book-open"></i> Books</a></li>
                <li class="<?= (str_starts_with($currentAdminUri, '/outofstock') || str_starts_with($currentAdminUri, '/out-of-stock')) ? 'active' : '' ?>"><a href="/outofstock"><i class="ph-bold ph-warning-circle"></i> Out of Stock</a></li>
                <li class="<?= (str_starts_with($currentAdminUri, '/addbook') || str_starts_with($currentAdminUri, '/add-book')) ? 'active' : '' ?>"><a href="/addbook"><i class="ph-bold ph-plus-circle"></i> Add Book</a></li>
                <li class="<?= (str_starts_with($currentAdminUri, '/categories')) ? 'active' : '' ?>"><a href="/categories"><i class="ph-bold ph-tag"></i> Categories</a></li>
                <li class="<?= (str_starts_with($currentAdminUri, '/dailydeal') || str_starts_with($currentAdminUri, '/daily-deal')) ? 'active' : '' ?>"><a href="/dailydeal"><i class="ph-bold ph-lightning"></i> Daily Deal</a></li>
                <li class="<?= (str_starts_with($currentAdminUri, '/adminorders') || str_starts_with($currentAdminUri, '/admin-orders')) ? 'active' : '' ?>"><a href="/adminorders"><i class="ph-bold ph-shopping-bag"></i> Orders</a></li>
                <li><a href="/index.php"><i class="ph-bold ph-storefront"></i> View Store</a></li>
                <li><a href="/logout" class="sidebar-logout"><i class="ph-bold ph-sign-out"></i> Logout</a></li>
            </ul>
        </nav>