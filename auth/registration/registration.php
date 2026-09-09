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

<section class="auth-page registration">
    <div class="neo-window wide">
        <div class="window-titlebar">
            <div class="window-dots">
                <span class="window-dot dot-pink"></span>
                <span class="window-dot dot-yellow"></span>
                <span class="window-dot dot-teal"></span>
            </div>
            <div class="window-label">
                <i class="ph-bold ph-user-plus"></i>
                <span>Create Account</span>
            </div>
        </div>

        <div class="window-content">
            <div class="auth-headline">
                <div class="auth-badge-pill">
                    <i class="ph-bold ph-user"></i> Community Join
                </div>
                <h2>Create Account</h2>
                <p>Join BookBuddy to explore curated books, reviews, and reading collections.</p>
            </div>

            <div class="general-alert-box" id="general-error"></div>

            <form action="/auth/registration/handler.php" method="post" enctype="multipart/form-data" class="neo-form ajax-form">
                <div class="field-group">
                    <label for="name">
                        <span><i class="ph-bold ph-user"></i> Full Name</span>
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-user input-icon"></i>
                        <input type="text" name="name" id="name" class="neo-input" placeholder="At least 8 letters" required>
                    </div>
                    <span class="error-text" id="name-error"></span>
                </div>

                <div class="field-group">
                    <label for="username">
                        <span><i class="ph-bold ph-at"></i> Username</span>
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-identification-badge input-icon"></i>
                        <input type="text" name="username" id="username" class="neo-input" placeholder="Letters and numbers, min 8 chars" required>
                    </div>
                    <span class="error-text" id="username-error"></span>
                </div>

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
                    </label>
                    <div class="input-box">
                        <i class="ph-bold ph-lock-key input-icon"></i>
                        <input type="password" name="password" id="password" class="neo-input" placeholder="Min 8 chars with letters & numbers" required autocomplete="new-password">
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
                        <input type="password" name="cpassword" id="cpassword" class="neo-input" placeholder="Repeat your chosen password" required autocomplete="new-password">
                        <button type="button" class="eye-toggle-btn" aria-label="Toggle password visibility">
                            <i class="ph-bold ph-eye"></i>
                        </button>
                    </div>
                    <span class="error-text" id="cpassword-error"></span>
                </div>

                <div class="field-group">
                    <label>
                        <span><i class="ph-bold ph-image"></i> Profile Avatar (PNG only, max 2MB)</span>
                    </label>
                    <div class="file-upload-row">
                        <label for="picture" class="upload-btn-neo">
                            <i class="ph-bold ph-upload-simple"></i> Choose File
                        </label>
                        <input type="file" name="profile" id="picture" accept="image/png" hidden required>
                        <span id="file-chosen">No file selected</span>
                    </div>
                    <span class="error-text" id="profile-error"></span>
                </div>

                <button type="submit" class="neo-btn btn-teal">
                    Create Account <i class="ph-bold ph-arrow-right"></i>
                </button>

                <div class="auth-bottom-switch">
                    Already have an account? <a href="/login">Login Now</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script src="/assests/js/auth.js"></script>
<?php 
require __DIR__ . "/../../includes/footer.php";
?>