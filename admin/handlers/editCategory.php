<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Unexpected Error"];

try {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $category = trim($_POST['category'] ?? '');

    if (!$id) {
        throw new Exception("Invalid ID");
    }
    if (empty($category)) {
        throw new Exception("Category name required");
    }

    $check = $connection->prepare("SELECT id FROM categories WHERE title=:title AND id!=:id LIMIT 1");
    $check->execute([':title' => $category, ':id' => $id]);
    if ($check->fetch()) {
        throw new Exception("Category already exists");
    }

    $update = $connection->prepare("UPDATE categories SET title=:title WHERE id=:id");
    $update->execute([':title' => $category, ':id' => $id]);

    $response = ["status" => "success", "message" => "Updated successfully"];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
