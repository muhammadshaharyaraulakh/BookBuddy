<?php
session_start();
require_once __DIR__ . "/../config/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request");
}

if (!isset($_SESSION['id'])) {
    header("Location: /pages/login.html");
    exit;
}

$user_id = $_SESSION['id'];

// Get user's cart totals
require __DIR__ . "/cartDetails.php";

if (empty($cartItems)) {
    die("Your cart is empty. Cannot process payment.");
}

// Check for user address
$addrStmt = $connection->prepare("SELECT id FROM user_address WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
$addrStmt->execute([':uid' => $user_id]);
$address = $addrStmt->fetch(PDO::FETCH_OBJ);

$shipping_address_id = $address ? $address->id : null;

if (!$shipping_address_id) {
    die("Please provide a shipping address before checking out.");
}

$payment_method = $_POST['payment_method'] ?? 'Credit Card';

try {
    $connection->beginTransaction();

    // Create Order
    $orderStmt = $connection->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status, shipping_address_id) 
        VALUES (:uid, :total, :method, 'pending', 'processing', :addr_id)
    ");
    $orderStmt->execute([
        ':uid' => $user_id,
        ':total' => $total,
        ':method' => $payment_method,
        ':addr_id' => $shipping_address_id
    ]);
    
    $order_id = $connection->lastInsertId();

    // Insert Order Items
    $itemStmt = $connection->prepare("
        INSERT INTO order_items (order_id, book_id, quantity, price_at_purchase) 
        VALUES (:oid, :bid, :qty, :price)
    ");

    foreach ($cartItems as $item) {
        $price = ($item->Discount_Percentage === NULL || $item->Discount_Percentage == 0)
            ? $item->Original_Price
            : $item->Discount_Price;

        $itemStmt->execute([
            ':oid' => $order_id,
            ':bid' => $item->id,
            ':qty' => $item->quantity,
            ':price' => $price
        ]);
    }

    // Clear Cart
    $clearCart = $connection->prepare("DELETE FROM cart WHERE user_id = :uid");
    $clearCart->execute([':uid' => $user_id]);
    
    $connection->commit();
    
    echo "<h1>Payment Successful!</h1>";
    echo "<p>Your order #" . $order_id . " has been placed successfully.</p>";
    echo "<a href='/index.php'>Return to Home</a>";

} catch (Exception $e) {
    $connection->rollBack();
    die("Error processing payment: " . $e->getMessage());
}
?>
