<?php
require __DIR__ . "/../../config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['id'])) {
    header("Location: /403.php");
    exit;
}

require __DIR__ . "/../../includes/header.php";
?>
<link rel="stylesheet" href="/assests/css/auth.css" />

<section class="auth-page forgot-page">
    <div class="neo-window">
        <div class="window-titlebar">
            <div class="window-dots">
                <span class="window-dot dot-pink"></span>
                <span class="window-dot dot-yellow"></span>
                <span class="window-dot dot-teal"></span>
            </div>
            <div class="window-label">
                <i class="ph-bold ph-key"></i>
                <span>Password Recovery</span>
            </div>
        </div>

        <div class="window-content">
            <div class="auth-headline">
                <h2>Forgot Password</h2>
                <p>Enter your registered email address and we will dispatch a 6 digit verification code to help you reset your credentials.</p>
            </div>

            <div class="general-alert-box" id="general-error"></div>

            <form action="/auth/forgot/handler.php" method="post" class="neo-form ajax-form">
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

                <button type="submit" class="neo-btn btn-yellow">
                    Send Verification Code <i class="ph-bold ph-paper-plane-tilt"></i>
                </button>

                <div class="auth-bottom-switch">
                    Remember your password? <a href="/login">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="/assests/js/auth.js"></script>
<?php 
require __DIR__ . "/../../includes/footer.php";
?>
