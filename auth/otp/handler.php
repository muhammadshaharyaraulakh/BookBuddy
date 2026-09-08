<?php
require_once __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

postRequest();

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_otp'])) {
        $response['field'] = "general";
        throw new Exception("Verification session expired. Please request a new code");
    }

    if (time() > ($_SESSION['reset_otp_expiry'] ?? 0)) {
        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_otp_expiry']);
        $response['field'] = "otp";
        throw new Exception("Verification code has expired. Please request a new one");
    }

    $enteredOtp = trim($_POST['otp'] ?? '');

    if (empty($enteredOtp)) {
        $response['field'] = "otp";
        throw new Exception("Please enter the 6 digit verification code");
    }

    if (strlen($enteredOtp) !== 6 || !ctype_digit($enteredOtp)) {
        $response['field'] = "otp";
        throw new Exception("Verification code must be exactly 6 digits");
    }

    if ($enteredOtp !== (string)$_SESSION['reset_otp']) {
        $response['field'] = "otp";
        throw new Exception("Invalid verification code. Please check and try again");
    }

    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_otp_expiry']);
    $_SESSION['reset_verified'] = true;

    $response = [
        'status'   => "success",
        'message'  => "Verification successful",
        'redirect' => "/resetpassword"
    ];

} catch (PDOException $e) {
    $response['status']  = "error";
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
    $response['field']   = "general";
} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
