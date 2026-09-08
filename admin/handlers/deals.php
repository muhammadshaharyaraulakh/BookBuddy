<?php
require __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../handlers/expireDeals.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $id = $_POST['id'] ?? null;
    $percentage = $_POST['percentage'] ?? null;

    if (empty($id)) {
        $response['field'] = 'id';
        throw new Exception("Please select a book");
    }

    if (!is_numeric($percentage) || $percentage <= 0 || $percentage > 70) {
        $response['field'] = 'percentage';
        throw new Exception("Please add a valid discount percentage (1–70)");
    }

    $checkActive = $connection->prepare("
        SELECT id FROM deals 
        WHERE status = 'active' 
          AND CURRENT_TIMESTAMP < end_time 
        LIMIT 1
    ");
    $checkActive->execute();
    if ($checkActive->fetch()) {
        throw new Exception("An active daily deal is already running. Only 1 book can be on daily deal at a time.");
    }

    $checkBook = $connection->prepare("SELECT id FROM book WHERE id = :id LIMIT 1");
    $checkBook->execute([':id' => $id]);
    if (!$checkBook->fetch()) {
        $response['field'] = 'id';
        throw new Exception("Selected book does not exist");
    }

    $insert = $connection->prepare("
        INSERT INTO deals (book_id, discount_percentage) 
        VALUES (:id, :discount)
    ");

    $result = $insert->execute([
        ':id' => $id,
        ':discount' => $percentage
    ]);
      
    if ($result) {
        $update = $connection->prepare("
            UPDATE book 
            SET Discount_Percentage = :discount
            WHERE id = :id
        ");
        $update->execute([
            ':discount' => $percentage,
            ':id' => $id
        ]);
        $response = [
            "status" => "success",
            "message" => "Deal added successfully",
            "field" => "general"
        ];
    }

} catch (PDOException $e) {
    $response['status']  = "error";
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
