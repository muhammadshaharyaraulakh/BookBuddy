<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $book_id = filter_input(INPUT_POST, 'bookId', FILTER_VALIDATE_INT);

    if (!$book_id) {
        throw new Exception("Invalid Book ID.");
    }

    $update = $connection->prepare("UPDATE book SET category_id = NULL WHERE id = :id");
    $update->execute([':id' => $book_id]);

    $response = [
        "status" => "success",
        "message" => "Removed from featured successfully"
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
