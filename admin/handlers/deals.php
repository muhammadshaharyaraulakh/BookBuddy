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

    // Section: Book Eligibility Verification (Price > 25, 0% Existing Discount, In Stock)
    $checkBook = $connection->prepare("
        SELECT id, title, Original_Price, Discount_Percentage, Stock 
        FROM book 
        WHERE id = :id 
        LIMIT 1
    ");
    $checkBook->execute([':id' => $id]);
    $bookObj = $checkBook->fetch(PDO::FETCH_OBJ);

    if (!$bookObj) {
        $response['field'] = 'id';
        throw new Exception("Selected book does not exist");
    }

    if ((float)$bookObj->Original_Price <= 25) {
        $response['field'] = 'id';
        throw new Exception("Only books with price greater than $25 are eligible for Daily Deals.");
    }

    if (!empty($bookObj->Discount_Percentage) && (float)$bookObj->Discount_Percentage > 0) {
        $response['field'] = 'id';
        throw new Exception("This book already has an active discount and cannot be set on Daily Deal.");
    }

    if ((int)$bookObj->Stock <= 0) {
        $response['field'] = 'id';
        throw new Exception("Out of stock books cannot be added to Daily Deals.");
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
