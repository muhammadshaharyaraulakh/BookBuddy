<?php
require __DIR__ . "/../../config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['id'])) {
    header("Location: /403.php");
    exit;
}

if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_verified'])) {
    header("Location: /forgot");
    exit;
}

require __DIR__ . "/../../includes/header.php";
?>
<link rel="stylesheet" href="/assests/css/auth.css" />

<section class="auth-page reset-page">
    <div class="neo-window">
        <div class="window-titlebar">
            <div class="window-dots">
                <span class="window-dot dot-pink"></span>
                <span class="window-dot dot-yellow"></span>
                <span class="window-dot dot-teal"></span>
            </div>
            <div class="window-label">
                <i class="ph-bold ph-lock-key"></i>
                <span>Reset Password</span>
            </div>
        </div>

        <div class="window-content">
            <div class="auth-headline">
                <h2>Create New Password</h2>
                <p>Choose a new secure password for your BookBuddy account.</p>
            </div>

            <div class="general-alert-box" id="general-error"></div>

            <form action="/auth/reset/handler.php" method="post" class="neo-form ajax-form">
                <div class="field-group">
                    <label for="password">
                        <span><i class="ph-bold ph-lock-key"></i> New Password</span>
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" name="password" id="password" class="neo-input" placeholder="Min 8 chars, letters & numbers" required autocomplete="new-password">
                        <button type="button" class="eye-toggle-btn" aria-label="Toggle password visibility">
                            <i class="ph-bold ph-eye"></i>
                        </button>
                    </div>
                    <span class="error-text" id="password-error"></span>
                </div>

                <div class="field-group">
                    <label for="cpassword">
                        <span><i class="ph-bold ph-shield-check"></i> Confirm Password</span>
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-shield-check input-icon"></i>
                        <input type="password" name="cpassword" id="cpassword" class="neo-input" placeholder="Repeat your new password" required autocomplete="new-password">
                        <button type="button" class="eye-toggle-btn" aria-label="Toggle password visibility">
                            <i class="ph-bold ph-eye"></i>
                        </button>
                    </div>
                    <span class="error-text" id="cpassword-error"></span>
                </div>

                <button type="submit" class="neo-btn btn-yellow">
                    Update Password <i class="ph-bold ph-arrow-right"></i>
                </button>

                <div class="auth-bottom-switch">
                    Remember your credentials? <a href="/login">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="/assests/js/auth.js"></script>
<?php 
require __DIR__ . "/../../includes/footer.php";
?>
