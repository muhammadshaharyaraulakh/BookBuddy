<?php
require_once __DIR__."/../config/config.php";
function render404(){
    http_response_code(404);
    header("Location: /404.php");
    die();
}
if(!defined('SECURE_ACCESS')){
    render404();
}
function ProtectFile($file){
    if(basename($file)===basename($_SERVER['PHP_SELF'])){
        render404();
    }
}
function postRequest(){
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        render404();
    }
}

function getRequest(){
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        render404();
    }
}
function getCategories(PDO $connection){
    $fetch=$connection->prepare("SELECT * FROM categories");
    $fetch->execute();
    return $fetch->fetchAll(PDO::FETCH_OBJ);
}
function getbooks(PDO $connection){
    $fetch=$connection->prepare("SELECT * FROM book");
    $fetch->execute();
    return $fetch->fetchAll(PDO::FETCH_OBJ);
}

function getCount(PDO $connection, $id){
    $fetch = $connection->prepare("SELECT * FROM book WHERE category_id = :id");
    $fetch->execute([
        ':id' => $id
    ]);
    $result = $fetch->fetchAll(PDO::FETCH_OBJ);
    return count($result);
}
function getBookDetails(PDO $connection,$categoryTitle){
    $fetch = $connection->prepare("
    SELECT b.*, c.title AS category_title
    FROM book b
    INNER JOIN categories c 
        ON b.category_id = c.id
    WHERE c.title = :title
");
$fetch->execute([
    ':title' => $categoryTitle
]);

return $fetch->fetchAll(PDO::FETCH_OBJ);

}
function register(PDO $connection, string $role) {
    $response = [
        "status" => "error",
        "message" => "Unexpected Error Occurred",
        "field" => "general" 
    ];

    try {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $gmail = trim($_POST['gmail'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['cpassword'] ?? '';
        $image = $_FILES['profile'] ?? NULL;

        if (empty($name) || strlen($name) < 8 || !preg_match("/^[a-zA-Z ]+$/", $name)) {
            $response['field'] = "name";
            throw new Exception("Minimum 8 characters required and only letters are allowed.");
        }

        if (empty($username) || strlen($username) < 8 || !preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $username)) {
            $response['field'] = "username";
            throw new Exception("Username must be at least 8 characters and contain both letters and numbers.");
        }

        if (empty($gmail) || !filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
            $response['field'] = "gmail";
            throw new Exception("Valid Gmail is Required");
        }

        $search = $connection->prepare("SELECT id, username, email FROM user WHERE email = :email OR username = :username");
        $search->execute([':email' => $gmail, ':username' => $username]);
        $result = $search->fetch(PDO::FETCH_OBJ);

        if ($result) {
            if ($result->username === $username) {
                $response['field'] = "username";
                throw new Exception("Username already taken.");
            }
            if ($result->email === $gmail) {
                $response['field'] = "gmail";
                throw new Exception("Gmail already taken.");
            }
        }

        if (empty($password) || strlen($password) < 8 || !preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $password)) {
            $response['field'] = "password";
            throw new Exception("Password must be at least 8 characters and contain both letters and numbers.");
        }

        if ($confirmPassword !== $password) {
            $response['field'] = "cpassword";
            throw new Exception("Passwords do not match.");
        }

        if (!$image || $image['error'] !== UPLOAD_ERR_OK || $image['size'] > 2000000) {
            $response['field'] = "profile";
            throw new Exception("Please upload a PNG image less than 2MB.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($image['tmp_name']) !== 'image/png') {
            $response['field'] = "profile";
            throw new Exception("Only PNG images are allowed.");
        }

        $upload_dir = __DIR__ . "/../userImages/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $image_name = time() . "_" . $username . ".png";
        $target_path = $upload_dir . $image_name;

        if (!move_uploaded_file($image['tmp_name'], $target_path)) {
            $response['field'] = "profile";
            throw new Exception("Failed to upload image.");
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $connection->prepare("INSERT INTO user (fullname, username, email, password, profileImage, role) 
                                        VALUES (:fullname, :username, :email, :password, :image, :role)");
        
        $insert->execute([
            ':fullname' => $name,
            ':username' => $username,
            ':email'    => $gmail,
            ':password' => $passwordHash,
            ':image'    => $image_name,
            ':role'     => $role
        ]);

        if ($role == "user") {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $newID = $connection->lastInsertId(); 
            
            $_SESSION['name'] = $name;
            $_SESSION['id']   = $newID;
            $_SESSION['gmail'] = $gmail;
            $_SESSION['image']= $image_name;
        }

        $response = [
            'status'  => "success",
            'message' => "Registration Successful",
            'redirect' => "/index.php"
        ];

    } catch (PDOException $e) {
        $response['status']  = "error";
        $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
        $response['field']   = "general";
    } catch (Exception $e) {
        $response['status']  = "error";
        $response['message'] =  htmlspecialchars($e->getMessage());
    }

    return $response;
}
?>