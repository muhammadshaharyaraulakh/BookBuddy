<?php
require __DIR__ . "/config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

http_response_code(403);
require __DIR__ . "/includes/header.php";
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
                <h2>Already Logged In</h2>
                <p>
                    <?php if (!empty($_SESSION['name'])): ?>
                        Signed in as <strong><?= htmlspecialchars($_SESSION['name']) ?></strong> (<?= htmlspecialchars($_SESSION['role'] ?? 'user') ?>). Guest authentication pages are restricted while logged in.
                    <?php else: ?>
                        You do not have permission to access authentication routes while an active account session exists.
                    <?php endif; ?>
                </p>
            </div>

            <div class="forbidden-actions">
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="/admin/adminPages/book.php" class="neo-btn btn-yellow">
                        Admin Dashboard <i class="ph-bold ph-arrow-right"></i>
                    </a>
                <?php endif; ?>

                <a href="/index.php" class="neo-btn btn-teal">
                    Return to Home <i class="ph-bold ph-house"></i>
                </a>

                <a href="/auth/logout.php" class="neo-btn btn-pink">
                    Logout Account <i class="ph-bold ph-sign-out"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php 
require __DIR__ . "/includes/footer.php";
?>
