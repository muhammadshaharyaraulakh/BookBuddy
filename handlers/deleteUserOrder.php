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

    // Section 3: Fetch Order & Verify Ownership and Cancelled State
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

    if ($order->order_status !== 'cancelled') {
        throw new Exception("Only cancelled orders can be removed from history.");
    }

    // Section 4: Delete Cancelled Order (Cascade removes order_items)
    $deleteStmt = $connection->prepare("
        DELETE FROM orders 
        WHERE id = :oid AND user_id = :uid AND order_status = 'cancelled'
    ");
    $deleteStmt->execute([
        ':oid' => $order_id,
        ':uid' => $user_id
    ]);

    if ($deleteStmt->rowCount() === 0) {
        throw new Exception("Could not remove order from history.");
    }

    // Check remaining orders count for this user
    $countStmt = $connection->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid");
    $countStmt->execute([':uid' => $user_id]);
    $remainingOrders = (int)$countStmt->fetchColumn();

    $response = [
        "status"           => "success",
        "message"          => "Order #ORD-{$order_id} removed from your history.",
        "order_id"         => $order_id,
        "remaining_orders" => $remainingOrders
    ];

} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
