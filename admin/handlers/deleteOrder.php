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

    // If order was in active state (not cancelled or returned), return books back to stock before deleting
    if (!in_array($order->order_status, ['cancelled', 'returned'])) {
        $itemsStmt = $connection->prepare("
            SELECT book_id, quantity 
            FROM order_items 
            WHERE order_id = :oid AND book_id IS NOT NULL
        ");
        $itemsStmt->execute([':oid' => $order_id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_OBJ);

        $restockStmt = $connection->prepare("
            UPDATE book 
            SET Stock = Stock + :qty 
            WHERE id = :bid
        ");

        foreach ($items as $item) {
            $restockStmt->execute([
                ':qty' => (int)$item->quantity,
                ':bid' => (int)$item->book_id
            ]);
        }
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
