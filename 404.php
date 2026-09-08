<?php
require __DIR__ . "/config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

http_response_code(404);
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
                <i class="ph-bold ph-compass"></i>
                <span>404 Not Found</span>
            </div>
        </div>

        <div class="window-content error-window-body">
            <div class="error-giant-code">404</div>

            <div class="auth-headline">
                <div class="auth-badge-pill pill-pink">
                    <i class="ph-bold ph-magnifying-glass"></i> Page Not Found
                </div>
                <h2>Lost In The Stacks</h2>
                <p>
                    The page, shelf, or book you are looking for might have been moved, renamed, or does not exist in our library.
                </p>
            </div>

            <div class="forbidden-actions">
                <a href="/index.php" class="neo-btn btn-teal">
                    Return to Home <i class="ph-bold ph-house"></i>
                </a>

                <?php if (!empty($_SESSION['id'])): ?>
                    <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="/books" class="neo-btn btn-yellow">
                            Admin Dashboard <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/login" class="neo-btn btn-yellow">
                        Member Login <i class="ph-bold ph-sign-in"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php 
require __DIR__ . "/includes/footer.php";
?>
