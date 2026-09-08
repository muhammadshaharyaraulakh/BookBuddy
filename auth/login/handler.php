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
    $gmail = trim($_POST['gmail'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($gmail) || !filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $response['field'] = "gmail";
        throw new Exception("Valid Gmail is Required");
    }

    if (empty($password)) {
        $response['field'] = "password";
        throw new Exception("Password is required");
    }

    $search = $connection->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
    $search->execute([':email' => $gmail]);
    $result = $search->fetch(PDO::FETCH_OBJ);

    if (empty($result)) {
        $response['field'] = "gmail";
        throw new Exception("Account not Found");
    }
    if (!password_verify($password, $result->password)) {
        $response['field'] = "password";
        throw new Exception("Wrong Password");
    } else {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);

        $_SESSION['id']    = $result->id;
        $_SESSION['name']  = $result->fullname;
        $_SESSION['gmail'] = $result->email;
        $_SESSION['image'] = $result->profileImage;
        $_SESSION['role']  = $result->role;
        $redirects = [
            'user'      => '/index.php',
            'moderator' => '/moderator/dashboard.php',
            'admin'     => '/books'
        ];
        $redirect = $redirects[$result->role] ?? '/index.php';
        $response = [
            'status'   => "success",
            'message'  => "Login Successful",
            'redirect' => $redirect
        ];
    }
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