<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

    if (!$order_id) {
        throw new Exception("Invalid order ID.");
    }

    // Delete order_items first (if foreign keys don't ON DELETE CASCADE)
    $delItems = $connection->prepare("DELETE FROM order_items WHERE order_id = :id");
    $delItems->execute([':id' => $order_id]);

    $delOrder = $connection->prepare("DELETE FROM orders WHERE id = :id");
    $delOrder->execute([':id' => $order_id]);

    if ($delOrder->rowCount() === 0) {
        throw new Exception("Order not found.");
    }

    $response = [
        "status" => "success",
        "message" => "Order deleted successfully!"
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
