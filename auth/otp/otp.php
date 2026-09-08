<?php
require __DIR__ . "/../../config/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['id'])) {
    header("Location: /403.php");
    exit;
}

if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_otp'])) {
    header("Location: /forgot");
    exit;
}

require __DIR__ . "/../../includes/header.php";

$maskedEmail = htmlspecialchars($_SESSION['reset_email']);
$parts = explode("@", $maskedEmail);
if (count($parts) === 2) {
    $namePart = $parts[0];
    $domainPart = $parts[1];
    $visibleLen = min(3, strlen($namePart));
    $maskedEmail = substr($namePart, 0, $visibleLen) . str_repeat("*", max(3, strlen($namePart) - $visibleLen)) . "@" . $domainPart;
}
?>
<link rel="stylesheet" href="/assests/css/auth.css" />

<section class="auth-page otp-page">
    <div class="neo-window">
        <div class="window-titlebar">
            <div class="window-dots">
                <span class="window-dot dot-pink"></span>
                <span class="window-dot dot-yellow"></span>
                <span class="window-dot dot-teal"></span>
            </div>
            <div class="window-label">
                <i class="ph-bold ph-shield-check"></i>
                <span>Verify Code</span>
            </div>
        </div>

        <div class="window-content">
            <div class="auth-headline">
                <h2>Enter Security Code</h2>
                <p>Enter the 6 digit verification code sent to <strong><?= $maskedEmail ?></strong>.</p>
            </div>

            <div class="general-alert-box" id="general-error"></div>

            <form action="/auth/otp/handler.php" method="post" class="neo-form ajax-form">
                <div class="otp-section">
                    <div class="otp-grid">
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" autofocus autocomplete="off">
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                        <input type="text" maxlength="1" class="otp-box" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                    </div>
                    <input type="hidden" name="otp" id="otp-value" value="">

                    <span class="error-text" id="otp-error"></span>

                    <div class="otp-status-bar" style="justify-content: center;">
                        <a href="/forgot">Resend Code</a>
                    </div>
                </div>

                <button type="submit" class="neo-btn btn-teal">
                    Verify Code <i class="ph-bold ph-check-circle"></i>
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
