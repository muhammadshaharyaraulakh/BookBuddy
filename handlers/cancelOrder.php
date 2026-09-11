<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";

header('Content-Type: application/json');

$response = [
    "status"  => "error",
    "message" => "Unexpected error occurred."
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid request method.";
    echo json_encode($response);
    exit;
}

try {
    // Section 1: Authentication Check
    if (empty($_SESSION['id'])) {
        $response['auth_required'] = true;
        throw new Exception("Please log in to manage your orders.");
    }
    $user_id = (int)$_SESSION['id'];

    // Section 2: Input Validation
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    if (!$order_id || $order_id <= 0) {
        throw new Exception("Invalid order ID specified.");
    }

    // Section 3: Fetch Order & Verify Ownership and Cancelable State
    $orderStmt = $connection->prepare("
        SELECT id, user_id, order_status 
        FROM orders 
        WHERE id = :oid AND user_id = :uid 
        LIMIT 1
    ");
    $orderStmt->execute([':oid' => $order_id, ':uid' => $user_id]);
    $order = $orderStmt->fetch(PDO::FETCH_OBJ);

    if (!$order) {
        throw new Exception("Order not found or unauthorized access.");
    }

    if ($order->order_status !== 'processing') {
        throw new Exception("Order #ORD-{$order_id} cannot be cancelled because it is already {$order->order_status}.");
    }

    // Section 4: Database Transaction (Cancel Order & Atomically Restore Inventory)
    $connection->beginTransaction();

    // Fetch order items to restock
    $itemsStmt = $connection->prepare("
        SELECT book_id, quantity 
        FROM order_items 
        WHERE order_id = :oid AND book_id IS NOT NULL
    ");
    $itemsStmt->execute([':oid' => $order_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_OBJ);

    // Restock each book
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

    // Update order status to cancelled
    $updateStmt = $connection->prepare("
        UPDATE orders 
        SET order_status = 'cancelled', payment_status = 'failed' 
        WHERE id = :oid AND user_id = :uid
    ");
    $updateStmt->execute([
        ':oid' => $order_id,
        ':uid' => $user_id
    ]);

    $connection->commit();

    $response = [
        "status"   => "success",
        "message"  => "Order #ORD-{$order_id} has been cancelled successfully. Inventory has been restored.",
        "order_id" => $order_id
    ];

} catch (Exception $e) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    $response['status']  = "error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
