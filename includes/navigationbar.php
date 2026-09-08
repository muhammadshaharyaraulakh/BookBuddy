<header class="site-header">
    <nav class="navbar">
        <a href="/index.php" class="nav-brand">
            <img src="/images/logo.png" alt="BookBuddy Logo" class="nav-brand-img" />
            <div class="nav-brand-text">
                <span class="nav-brand-name">BookBuddy</span>
                <span class="nav-brand-tagline">Literary Sanctuary</span>
            </div>
        </a>

        <div class="nav-center">
            <div class="nav-dropdown">
                <button type="button" class="nav-dropdown-btn">
                    <i class="ph-bold ph-squares-four"></i>
                    <span>Categories</span>
                    <i class="ph-bold ph-caret-down"></i>
                </button>
                <div class="nav-dropdown-menu">
                    <div class="nav-search-box">
                        <i class="ph-bold ph-magnifying-glass"></i>
                        <input type="text" placeholder="Filter Categories" autocomplete="off">
                    </div>
                    <a href="/shop.php?category=programming" class="category-item">
                        Programming
                    </a>
                    <a href="/shop.php?category=scifi" class="category-item">
                        Fantasy
                    </a>
                    <a href="/shop.php?category=business" class="category-item">
                        Business
                    </a>
                    <a href="/shop.php?category=biography" class="category-item">
                        Biographies
                    </a>
                    <a href="/shop.php?category=classics" class="category-item">
                        History
                    </a>
                </div>
            </div>

            <a href="/shop.php" class="nav-link">
                <i class="ph-bold ph-storefront"></i>
                <span>Shop</span>
            </a>
            <a href="/shop.php?filter=sale" class="nav-link">
                <i class="ph-bold ph-tag"></i>
                <span>Sale</span>
            </a>
            <a href="/index.php#deals" class="nav-link">
                <i class="ph-bold ph-lightning"></i>
                <span>Deals</span>
            </a>
        </div>

        <div class="nav-right">
            <?php if (empty($_SESSION['id'])): ?>
                <a href="/login" class="btn-nav-login">
                    <i class="ph-bold ph-sign-in"></i>
                    <span>Log In</span>
                </a>
                <a href="/register" class="btn-nav-signup">
                    <i class="ph-bold ph-user-plus"></i>
                    <span>Sign Up</span>
                </a>
            <?php else: ?>
                <div class="nav-dropdown nav-profile-header">
                    <div class="user-profile-toggle">
                        <img src="/userImages/<?= htmlspecialchars($_SESSION['image'] ?? '1788851470_aliraza1.png') ?>" alt="User Avatar" onerror="this.src='/images/logo.png'" />
                        <span class="user-profile-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Reader') ?></span>
                        <i class="ph-bold ph-caret-down"></i>
                    </div>
                    <div class="user-profile-menu">
                        <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="/books">
                                <i class="ph-bold ph-shield"></i> Admin Panel
                            </a>
                        <?php endif; ?>
                        <a href="/profile.php">
                            <i class="ph-bold ph-user"></i> My Profile
                        </a>
                        <a href="/cart.php">
                            <i class="ph-bold ph-shopping-cart"></i> My Cart
                        </a>
                        <a href="/orders.php">
                            <i class="ph-bold ph-package"></i> Order History
                        </a>
                        <a href="/auth/logout.php" class="menu-logout">
                            <i class="ph-bold ph-sign-out"></i> Logout
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <button type="button" class="nav-hamburger" aria-label="Open Navigation">
                <i class="ph-bold ph-list"></i>
            </button>
        </div>
    </nav>
</header>

<div class="mobile-drawer">
    <div class="mobile-drawer-content">
        <div class="mobile-drawer-header">
            <span class="nav-brand-name">BookBuddy</span>
            <button type="button" class="mobile-close-btn" aria-label="Close Menu">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <?php if (!empty($_SESSION['id'])): ?>
            <div class="mobile-user-card">
                <img src="/userImages/<?= htmlspecialchars($_SESSION['image'] ?? '1788851470_aliraza1.png') ?>" alt="User Avatar" onerror="this.src='/images/logo.png'" class="mobile-user-avatar" />
                <div class="mobile-user-info">
                    <span class="mobile-user-name"><?= htmlspecialchars($_SESSION['name'] ?? 'Reader') ?></span>
                    <span class="mobile-user-role"><?= (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Verified Member' ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="mobile-nav-links">
            <a href="/index.php" class="nav-link"><i class="ph-bold ph-house"></i> Home</a>
            <a href="/shop.php" class="nav-link"><i class="ph-bold ph-storefront"></i> Shop</a>
            <a href="/shop.php?filter=sale" class="nav-link"><i class="ph-bold ph-tag"></i> Sale</a>
            <a href="/index.php#deals" class="nav-link"><i class="ph-bold ph-lightning"></i> Deals</a>
        </div>

        <hr class="mobile-divider">

        <div class="mobile-user-links">
            <?php if (empty($_SESSION['id'])): ?>
                <a href="/login" class="btn-nav-login" style="justify-content: center;"><i class="ph-bold ph-sign-in"></i> Log In</a>
                <a href="/register" class="btn-nav-signup" style="justify-content: center;"><i class="ph-bold ph-user-plus"></i> Sign Up</a>
            <?php else: ?>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/books" class="nav-link"><i class="ph-bold ph-shield"></i> Admin Panel</a>
                <?php endif; ?>
                <a href="/profile.php" class="nav-link"><i class="ph-bold ph-user"></i> My Profile</a>
                <a href="/cart.php" class="nav-link"><i class="ph-bold ph-shopping-cart"></i> My Cart</a>
                <a href="/orders.php" class="nav-link"><i class="ph-bold ph-package"></i> Order History</a>
                <a href="/auth/logout.php" class="nav-link mobile-logout-link"><i class="ph-bold ph-sign-out"></i> Logout</a>
            <?php endif; ?>
        </div>
    </div>
</div>