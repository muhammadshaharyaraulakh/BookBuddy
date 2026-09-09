<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/expireDeals.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("Invalid request method");
}

// Section 1: Authentication Verification
if (empty($_SESSION['id'])) {
    header("Location: /login");
    exit;
}

$user_id = (int)$_SESSION['id'];

// Section 2: Load Live Cart Details & Audit Issues
require __DIR__ . "/cartDetails.php";

if (empty($cartItems)) {
    header("Location: /cart?error=" . urlencode("Your cart is empty."));
    exit;
}

if (!empty($hasStockIssue)) {
    header("Location: /cart?error=" . urlencode("Cannot place order. Please remove out-of-stock items, daily deal items, or reduce over-limit quantities."));
    exit;
}

// Section 3: Fetch User Shipping Address
$addrStmt = $connection->prepare("SELECT id FROM user_address WHERE user_id = :uid ORDER BY created_at DESC LIMIT 1");
$addrStmt->execute([':uid' => $user_id]);
$address = $addrStmt->fetch(PDO::FETCH_OBJ);

$shipping_address_id = $address ? $address->id : null;

try {
    $connection->beginTransaction();

    // Section 4: Create Cash on Delivery Order
    $orderStmt = $connection->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status, shipping_address_id) 
        VALUES (:uid, :total, 'Cash on Delivery', 'pending', 'processing', :addr_id)
    ");
    $orderStmt->execute([
        ':uid'     => $user_id,
        ':total'   => $total,
        ':addr_id' => $shipping_address_id
    ]);
    
    $order_id = $connection->lastInsertId();

    // Section 5: Insert Order Items and Deduct Inventory
    $itemStmt = $connection->prepare("
        INSERT INTO order_items (order_id, book_id, book_title, quantity, price_at_purchase) 
        VALUES (:oid, :bid, :title, :qty, :price)
    ");

    $deductStmt = $connection->prepare("
        UPDATE book 
        SET Stock = GREATEST(0, Stock - :qty) 
        WHERE id = :bid
    ");

    foreach ($cartItems as $item) {
        $price = (float)$item->effective_price;
        $qtyPurchased = (int)$item->cart_quantity;

        $itemStmt->execute([
            ':oid'   => $order_id,
            ':bid'   => $item->id,
            ':title' => $item->title,
            ':qty'   => $qtyPurchased,
            ':price' => $price
        ]);

        $deductStmt->execute([
            ':qty' => $qtyPurchased,
            ':bid' => $item->id
        ]);
    }

    // Section 6: Clear Cart After Successful Order Placement
    $clearCart = $connection->prepare("DELETE FROM cart WHERE user_id = :uid");
    $clearCart->execute([':uid' => $user_id]);

    $connection->commit();

    header("Location: /orders?success=1&order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    header("Location: /cart?error=" . urlencode("Could not place order: " . $e->getMessage()));
    exit;
}
