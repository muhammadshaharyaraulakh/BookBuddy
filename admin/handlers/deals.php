<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {

    $id = $_POST['id'] ?? null;
    $percentage = $_POST['percentage'] ?? null;

    // --- VALIDATION ---

    if (empty($id)) {
        $response['field'] = 'id';
        throw new Exception("Please select a book");
    }

    if (!is_numeric($percentage) || $percentage <= 0 || $percentage > 70) {
        $response['field'] = 'percentage';
        throw new Exception("Please add a valid discount percentage (1–70)");
    }


    // --- INSERT ---
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
