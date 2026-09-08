<?php
require __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Failed to delete category.",
    "field" => "general"
];

try {
    $category_id = $_POST['id'] ?? null;

    if (empty($category_id) || !filter_var($category_id, FILTER_VALIDATE_INT)) {
        throw new Exception("Invalid Category ID.");
    }

    $checkCategoryDeal = $connection->prepare("
        SELECT d.id 
        FROM deals d 
        JOIN book b ON d.book_id = b.id 
        WHERE b.category_id = :id 
          AND d.status = 'active' 
          AND CURRENT_TIMESTAMP < d.end_time 
        LIMIT 1
    ");
    $checkCategoryDeal->execute([':id' => $category_id]);
    if ($checkCategoryDeal->fetch()) {
        http_response_code(400);
        throw new Exception("This category cannot be deleted because a book in this category is currently in the daily deal.");
    }

    $AllPosts = $connection->prepare("SELECT coverImage FROM book WHERE category_id = :id");
    $AllPosts->execute([':id' => $category_id]);
    $posts = $AllPosts->fetchAll(PDO::FETCH_OBJ);

    $images_dir = realpath(__DIR__ . "/../../images/");

    foreach ($posts as $post) {
        if (!empty($post->coverImage)) {
            $image_path = realpath($images_dir . "/" . $post->coverImage);
            if ($image_path && str_starts_with($image_path, $images_dir) && file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }

    $deletePosts = $connection->prepare("DELETE FROM book WHERE category_id = :id");
    $deletePosts->execute([':id' => $category_id]);

    $deleteCategory = $connection->prepare("DELETE FROM categories WHERE id = :id");
    $deleteCategory->execute([':id' => $category_id]);

    if ($deleteCategory->rowCount() === 0) {
        throw new Exception("Category not found or already deleted.");
    }

    $response = [
        "status" => "success",
        "message" => "Category and related posts deleted successfully!"
    ];

} catch (Exception $e) {
    http_response_code(400);
    $response["message"] = $e->getMessage();
}

echo json_encode($response);
exit;
