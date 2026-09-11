<?php
require __DIR__ . "/../../config/config.php";
header('Content-Type: application/json');

$response = [
    "status" => "error",
    "message" => "Unexpected Error Occurred",
    "field" => "general"
];

try {
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT) ?: filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$order_id) {
        throw new Exception("Invalid order ID.");
    }

    $connection->beginTransaction();

    // Verify order exists
    $stmt = $connection->prepare("SELECT id, order_status FROM orders WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$order) {
        throw new Exception("Order not found or already deleted.");
    }

    // Admin can delete an order ONLY if it is cancelled
    if ($order->order_status !== 'cancelled') {
        throw new Exception("Only cancelled orders can be deleted. Please update the order status to 'Cancelled' first.");
    }

    // Delete order (cascades to order_items)
    $delStmt = $connection->prepare("DELETE FROM orders WHERE id = :id");
    $delStmt->execute([':id' => $order_id]);

    $connection->commit();

    $response = [
        "status" => "success",
        "message" => "Order #ORD-{$order_id} deleted successfully!"
    ];

} catch (PDOException $e) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
