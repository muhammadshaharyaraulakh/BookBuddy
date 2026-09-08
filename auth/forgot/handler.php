<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../function/mailer.php";
header('Content-Type: application/json');

postRequest();

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $gmail = trim($_POST['gmail'] ?? '');

    if (empty($gmail) || !filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $response['field'] = "gmail";
        throw new Exception("Valid email address is required");
    }

    $search = $connection->prepare("SELECT id, fullname, email FROM user WHERE email = :email LIMIT 1");
    $search->execute([':email' => $gmail]);
    $user = $search->fetch(PDO::FETCH_OBJ);

    if (empty($user)) {
        $response['field'] = "gmail";
        throw new Exception("No account found with this email address");
    }

    $otp = (string) random_int(100000, 999999);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['reset_email'] = $user->email;
    $_SESSION['reset_name']  = $user->fullname;
    $_SESSION['reset_otp']   = $otp;
    $_SESSION['reset_otp_expiry'] = time() + 300;

    $mailSent = sendOtpEmail($user->email, $user->fullname, $otp);
    error_log("[BookBuddy OTP] Verification code for {$user->email} is {$otp}");

    $response = [
        'status'   => "success",
        'message'  => "Verification code sent to your email",
        'redirect' => "/confirm"
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
