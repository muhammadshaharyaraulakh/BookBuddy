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

    if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_verified'])) {
        $response['field'] = "general";
        throw new Exception("Unauthorized request. Please verify your code first");
    }

    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['cpassword'] ?? '';

    if (empty($password) || strlen($password) < 8 || !preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) {
        $response['field'] = "password";
        throw new Exception("Password must be at least 8 characters and contain both letters and numbers");
    }

    if ($password !== $cpassword) {
        $response['field'] = "cpassword";
        throw new Exception("Passwords do not match");
    }

    $email = $_SESSION['reset_email'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $update = $connection->prepare("UPDATE user SET password = :password WHERE email = :email");
    $update->execute([
        ':password' => $hashedPassword,
        ':email'    => $email
    ]);

    unset($_SESSION['reset_email']);
    unset($_SESSION['reset_name']);
    unset($_SESSION['reset_verified']);

    $response = [
        'status'   => "success",
        'message'  => "Password has been successfully updated",
        'redirect' => "/login"
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
