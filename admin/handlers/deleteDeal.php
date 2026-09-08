<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $deal_id = filter_input(INPUT_POST, 'deal_id', FILTER_VALIDATE_INT) 
        ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $book_id = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);

    if (!$deal_id && !$book_id) {
        throw new Exception("Invalid deal or book identifier.");
    }

    if ($deal_id) {
        $find = $connection->prepare("SELECT id, book_id FROM deals WHERE id = :deal_id LIMIT 1");
        $find->execute([':deal_id' => $deal_id]);
        $deal = $find->fetch(PDO::FETCH_OBJ);
    } else {
        $find = $connection->prepare("SELECT id, book_id FROM deals WHERE book_id = :book_id AND status = 'active' LIMIT 1");
        $find->execute([':book_id' => $book_id]);
        $deal = $find->fetch(PDO::FETCH_OBJ);
    }

    if (!$deal) {
        throw new Exception("Active daily deal not found.");
    }

    $updateDeal = $connection->prepare("
        UPDATE deals 
        SET status = 'expired', processed_at = NOW() 
        WHERE id = :deal_id
    ");
    $updateDeal->execute([':deal_id' => $deal->id]);

    $updateBook = $connection->prepare("
        UPDATE book 
        SET Discount_Percentage = 0 
        WHERE id = :book_id
    ");
    $updateBook->execute([':book_id' => $deal->book_id]);

    $deleteCart = $connection->prepare("DELETE FROM cart WHERE book_id = :book_id");
    $deleteCart->execute([':book_id' => $deal->book_id]);

    $response = [
        "status" => "success",
        "message" => "Book removed from daily deal successfully."
    ];

} catch (PDOException $e) {
    $response['status'] = "error";
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['status'] = "error";
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
