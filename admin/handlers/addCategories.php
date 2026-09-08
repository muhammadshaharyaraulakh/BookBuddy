<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $title = trim($_POST['category'] ?? '');

    if (empty($title)) {
        $response['field'] = 'title';
        throw new Exception("Category title cannot be empty");
    }

    $check = $connection->prepare("SELECT * FROM categories WHERE title = :title");
    $check->execute(['title' => $title]);
    $result = $check->fetch(PDO::FETCH_OBJ);

    if ($result) {
        $response['field'] = 'title';
        throw new Exception("Category already exists");
    }

    $insert = $connection->prepare("INSERT INTO categories(title) VALUES(:title)");
    $insert->execute([':title' => $title]);

    $response = [
        "status" => "success",
        "message" => "New Category Added.",
        "field" => "general"
    ];

} catch (PDOException $e) {
    $response['message'] = "DB Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
