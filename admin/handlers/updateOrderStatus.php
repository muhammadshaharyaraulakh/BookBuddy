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
    $status = trim($_POST['status'] ?? '');

    if (!$order_id) {
        throw new Exception("Invalid order ID.");
    }
    
    $allowedStatuses = ['processing', 'shipped', 'delivered', 'cancelled', 'returned'];
    if (!in_array($status, $allowedStatuses)) {
        throw new Exception("Invalid order status: " . htmlspecialchars($status));
    }

    $connection->beginTransaction();

    // Fetch current order status
    $checkStmt = $connection->prepare("
        SELECT id, order_status, payment_status 
        FROM orders 
        WHERE id = :id 
        FOR UPDATE
    ");
    $checkStmt->execute([':id' => $order_id]);
    $order = $checkStmt->fetch(PDO::FETCH_OBJ);

    if (!$order) {
        throw new Exception("Order not found.");
    }

    $oldStatus = $order->order_status;

    // Only update and restock if status is changing
    if ($oldStatus !== $status) {
        $wasCancelledOrReturned = in_array($oldStatus, ['cancelled', 'returned']);
        $isNowCancelledOrReturned = in_array($status, ['cancelled', 'returned']);

        // Case 1: Order marked as Cancelled or Returned -> Atomically restore stock for active books
        if ($isNowCancelledOrReturned && !$wasCancelledOrReturned) {
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

        // Case 2: Order moved BACK to active (processing/shipped/delivered) from cancelled/returned -> Deduct stock
        if (!$isNowCancelledOrReturned && $wasCancelledOrReturned) {
            $itemsStmt = $connection->prepare("
                SELECT book_id, quantity 
                FROM order_items 
                WHERE order_id = :oid AND book_id IS NOT NULL
            ");
            $itemsStmt->execute([':oid' => $order_id]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_OBJ);

            $deductStmt = $connection->prepare("
                UPDATE book 
                SET Stock = GREATEST(0, Stock - :qty) 
                WHERE id = :bid
            ");

            foreach ($items as $item) {
                $deductStmt->execute([
                    ':qty' => (int)$item->quantity,
                    ':bid' => (int)$item->book_id
                ]);
            }
        }

        // Determine payment status update
        $paymentStatus = $order->payment_status;
        if ($status === 'delivered') {
            $paymentStatus = 'completed';
        } else if (in_array($status, ['cancelled', 'returned'])) {
            $paymentStatus = 'failed';
        } else if ($status === 'processing' || $status === 'shipped') {
            if ($paymentStatus === 'failed') {
                $paymentStatus = 'pending';
            }
        }

        $updateStmt = $connection->prepare("
            UPDATE orders 
            SET order_status = :status, 
                payment_status = :payment_status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status'         => $status,
            ':payment_status' => $paymentStatus,
            ':id'             => $order_id
        ]);
    }

    $connection->commit();

    $response = [
        "status" => "success",
        "message" => "Order #ORD-{$order_id} status updated to " . ucfirst($status) . " successfully!"
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
