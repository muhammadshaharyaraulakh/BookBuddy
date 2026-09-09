<?php
require __DIR__ . "/../../config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['id'])) {
    header("Location: /index.php");
    exit;
}

require __DIR__ . "/../../includes/header.php";
?>
<link rel="stylesheet" href="/assests/css/auth.css" />

<section class="auth-page login">
    <div class="neo-window">
        <div class="window-titlebar">
            <div class="window-dots">
                <span class="window-dot dot-pink"></span>
                <span class="window-dot dot-yellow"></span>
                <span class="window-dot dot-teal"></span>
            </div>
            <div class="window-label">
                <i class="ph-bold ph-sign-in"></i>
                <span>BookBuddy Login</span>
            </div>
        </div>

        <div class="window-content">
            <div class="auth-headline">
                <div class="auth-badge-pill">
                    <i class="ph-bold ph-user"></i> Member Access
                </div>
                <h2>Welcome Back</h2>
                <p>Enter your account credentials to access your library sanctuary.</p>
            </div>

            <div class="general-alert-box" id="general-error"></div>

            <form action="/auth/login/handler.php" method="post" class="neo-form ajax-form">
                <div class="field-group">
                    <label for="email">
                        <span><i class="ph-bold ph-envelope-simple"></i> Email Address</span>
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-at input-icon"></i>
                        <input type="email" name="gmail" id="email" class="neo-input" placeholder="reader@bookbuddy.com" required autocomplete="email">
                    </div>
                    <span class="error-text" id="gmail-error"></span>
                </div>

                <div class="field-group">
                    <label for="password">
                        <span><i class="ph-bold ph-lock-key"></i> Password</span>
                        <a href="/forgot">Forgot Password?</a>
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" name="password" id="password" class="neo-input" placeholder="Enter your password" required autocomplete="current-password">
                        <button type="button" class="eye-toggle-btn" aria-label="Toggle password visibility">
                            <i class="ph-bold ph-eye"></i>
                        </button>
                    </div>
                    <span class="error-text" id="password-error"></span>
                </div>

                <button type="submit" class="neo-btn btn-yellow">
                    Log In <i class="ph-bold ph-arrow-right"></i>
                </button>

                <div class="auth-bottom-switch">
                    Need a new account? <a href="/register">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="/assests/js/auth.js"></script>
<?php 
require __DIR__ . "/../../includes/footer.php";
?>