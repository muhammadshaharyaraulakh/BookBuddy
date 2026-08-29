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
    $status = $_POST['status'] ?? '';

    if (!$order_id) {
        throw new Exception("Invalid order ID.");
    }
    
    if (!in_array($status, ['processing', 'shipped', 'delivered', 'cancelled'])) {
        throw new Exception("Invalid order status.");
    }

    $query = "UPDATE orders SET order_status = :status";
    if ($status === 'delivered') {
        $query .= ", payment_status = 'completed'";
    } else if ($status === 'cancelled') {
        $query .= ", payment_status = 'failed'";
    }
    $query .= " WHERE id = :id";

    $update = $connection->prepare($query);
    $update->execute([
        ':status' => $status,
        ':id' => $order_id
    ]);

    if ($update->rowCount() === 0) {
        throw new Exception("Order not found or status already matches.");
    }

    $response = [
        "status" => "success",
        "message" => "Order updated successfully!"
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    $response['message'] = htmlspecialchars($e->getMessage());
}

echo json_encode($response);
exit;
