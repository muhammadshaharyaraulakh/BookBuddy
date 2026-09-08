<?php
require __DIR__ . "/config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

http_response_code(403);
require __DIR__ . "/includes/header.php";

$isLoggedIn = !empty($_SESSION['id']) || !empty($_SESSION['name']);
$isAdmin = !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<link rel="stylesheet" href="/assests/css/auth.css" />

<section class="auth-page forbidden-page">
    <div class="neo-window">
        <div class="window-titlebar">
            <div class="window-dots">
                <span class="window-dot dot-pink"></span>
                <span class="window-dot dot-yellow"></span>
                <span class="window-dot dot-teal"></span>
            </div>
            <div class="window-label">
                <i class="ph-bold ph-shield-slash"></i>
                <span>403 Forbidden</span>
            </div>
        </div>

        <div class="window-content error-window-body">
            <div class="error-giant-code">403</div>

            <div class="auth-headline">
                <div class="auth-badge-pill pill-pink">
                    <i class="ph-bold ph-prohibit"></i> Access Restricted
                </div>
                <?php if ($isAdmin): ?>
                    <h2>Restricted Resource</h2>
                    <p>Signed in as <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></strong>. This resource is restricted or unavailable.</p>
                <?php elseif ($isLoggedIn): ?>
                    <h2>Administrator Required</h2>
                    <p>Signed in as <strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($_SESSION['role'] ?? 'user') ?>). You do not have administrator permissions to access this page or resource.</p>
                <?php else: ?>
                    <h2>Access Denied</h2>
                    <p>You do not have permission to view this resource. An authorized administrator account is required.</p>
                <?php endif; ?>
            </div>

            <div class="forbidden-actions">
                <?php if ($isAdmin): ?>
                    <a href="/books" class="neo-btn btn-yellow">
                        Admin Dashboard <i class="ph-bold ph-arrow-right"></i>
                    </a>
                    <a href="/index.php" class="neo-btn btn-teal">
                        Return to Home <i class="ph-bold ph-house"></i>
                    </a>
                    <a href="/logout" class="neo-btn btn-pink">
                        Logout Account <i class="ph-bold ph-sign-out"></i>
                    </a>
                <?php elseif ($isLoggedIn): ?>
                    <a href="/index.php" class="neo-btn btn-yellow">
                        Return to Home <i class="ph-bold ph-house"></i>
                    </a>
                    <a href="/shop" class="neo-btn btn-teal">
                        Browse Books <i class="ph-bold ph-book-open"></i>
                    </a>
                    <a href="/logout" class="neo-btn btn-pink">
                        Logout Account <i class="ph-bold ph-sign-out"></i>
                    </a>
                <?php else: ?>
                    <a href="/login" class="neo-btn btn-yellow">
                        Sign In <i class="ph-bold ph-sign-in"></i>
                    </a>
                    <a href="/index.php" class="neo-btn btn-teal">
                        Return to Home <i class="ph-bold ph-house"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
require __DIR__ . "/includes/footer.php";
?>
