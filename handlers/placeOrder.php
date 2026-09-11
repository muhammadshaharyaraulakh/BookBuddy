<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/expireDeals.php";

header('Content-Type: application/json');

$response = [
    "status"  => "error",
    "message" => "Unexpected error occurred"
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid request method.";
    echo json_encode($response);
    exit;
}

try {
    // Section 1: Authentication Verification
    if (empty($_SESSION['id'])) {
        $response['auth_required'] = true;
        throw new Exception("Please log in to place your order.");
    }
    $user_id = (int)$_SESSION['id'];

    // Section 2: Delivery Address Verification & Strict Validation
    $address_id = filter_var($_POST['address_id'] ?? '', FILTER_VALIDATE_INT);
    if (!$address_id || $address_id <= 0) {
        throw new Exception("Please select a valid delivery address.");
    }

    $addrStmt = $connection->prepare("
        SELECT id, province, district, city, postcode, address, contact 
        FROM user_address 
        WHERE id = :aid AND user_id = :uid 
        LIMIT 1
    ");
    $addrStmt->execute([':aid' => $address_id, ':uid' => $user_id]);
    $address = $addrStmt->fetch(PDO::FETCH_OBJ);

    if (!$address) {
        throw new Exception("Selected delivery address was not found in your account.");
    }

    // Validate 5-digit postal code
    if (!preg_match('/^[0-9]{5}$/', trim($address->postcode))) {
        throw new Exception("Postal code on the selected address is invalid. It must be exactly 5 digits.");
    }

    // Validate 11-digit phone number
    if (!preg_match('/^[0-9]{11}$/', trim($address->contact))) {
        throw new Exception("Contact number on the selected address is invalid. It must be an 11-digit number.");
    }

    // Generate frozen address snapshot
    $districtPart = !empty($address->district) ? ", District: " . $address->district : "";
    $addressSnapshot = "{$address->address}, {$address->city}{$districtPart}, {$address->province} (Postal Code: {$address->postcode}) - Contact: {$address->contact}";

    // Ensure database column exists
    try {
        $colOrder = $connection->query("SHOW COLUMNS FROM orders LIKE 'shipping_address_text'");
        if ($colOrder->rowCount() === 0) {
            $connection->exec("ALTER TABLE orders ADD COLUMN shipping_address_text TEXT DEFAULT NULL AFTER shipping_address_id");
        }
    } catch (Exception $e) {}

    // Section 3: Database Atomicity (Begin Transaction)
    $connection->beginTransaction();

    // Section 4: Cart Integrity & Concurrency Row Lock (FOR UPDATE)
    $cartStmt = $connection->prepare("
        SELECT 
            c.id AS cart_id, 
            c.quantity AS cart_quantity, 
            b.id AS book_id, 
            b.title, 
            b.Stock, 
            b.Original_Price, 
            b.Discount_Price, 
            b.Discount_Percentage,
            (
                SELECT COUNT(*) 
                FROM deals d 
                WHERE d.book_id = b.id 
                  AND d.status = 'active' 
                  AND CURRENT_TIMESTAMP < d.end_time
            ) AS is_on_deal
        FROM cart c 
        JOIN book b ON c.book_id = b.id 
        WHERE c.user_id = :uid
        FOR UPDATE
    ");
    $cartStmt->execute([':uid' => $user_id]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_OBJ);

    if (empty($cartItems)) {
        throw new Exception("Your cart is empty. Please add books before placing an order.");
    }

    // Section 5: Real-Time Live Stock & Active Daily Deals Validation
    $orderGrandTotal = 0.0;
    foreach ($cartItems as $item) {
        if (!empty($item->is_on_deal) && (int)$item->is_on_deal > 0) {
            throw new Exception("'" . htmlspecialchars($item->title) . "' was recently added to an active Daily Deal. Daily deal items cannot be purchased through the standard cart; please remove it to proceed.");
        }

        $liveStock = (int)$item->Stock;
        $orderQty  = (int)$item->cart_quantity;

        if ($liveStock <= 0) {
            throw new Exception("Sorry, '" . htmlspecialchars($item->title) . "' just went out of stock. Please remove it from your cart.");
        }

        if ($orderQty > $liveStock) {
            throw new Exception("Stock changed: only {$liveStock} " . ($liveStock === 1 ? "copy" : "copies") . " of '" . htmlspecialchars($item->title) . "' remain available. Please adjust your cart quantity.");
        }

        $price = ($item->Discount_Percentage !== null && $item->Discount_Percentage > 0)
            ? (float)$item->Discount_Price
            : (float)$item->Original_Price;

        $orderGrandTotal += $price * $orderQty;
    }

    $orderGrandTotal = round($orderGrandTotal, 2);

    // Section 6: Create Cash on Delivery Order with Locked Address Snapshot
    $orderStmt = $connection->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status, shipping_address_id, shipping_address_text) 
        VALUES (:uid, :total, 'Cash on Delivery', 'pending', 'processing', :addr_id, :addr_text)
    ");
    $orderStmt->execute([
        ':uid'       => $user_id,
        ':total'     => $orderGrandTotal,
        ':addr_id'   => $address_id,
        ':addr_text' => $addressSnapshot
    ]);
    
    $order_id = $connection->lastInsertId();

    // Section 7: Insert Order Items and Atomic Inventory Deduction
    $itemStmt = $connection->prepare("
        INSERT INTO order_items (order_id, book_id, book_title, quantity, price_at_purchase) 
        VALUES (:oid, :bid, :title, :qty, :price)
    ");

    $deductStmt = $connection->prepare("
        UPDATE book 
        SET Stock = Stock - :qty 
        WHERE id = :bid AND Stock >= :min_qty
    ");

    foreach ($cartItems as $item) {
        $price = ($item->Discount_Percentage !== null && $item->Discount_Percentage > 0)
            ? (float)$item->Discount_Price
            : (float)$item->Original_Price;

        $qtyPurchased = (int)$item->cart_quantity;

        $itemStmt->execute([
            ':oid'   => $order_id,
            ':bid'   => $item->book_id,
            ':title' => $item->title,
            ':qty'   => $qtyPurchased,
            ':price' => $price
        ]);

        $deductStmt->execute([
            ':qty'     => $qtyPurchased,
            ':min_qty' => $qtyPurchased,
            ':bid'     => $item->book_id
        ]);

        if ($deductStmt->rowCount() === 0) {
            throw new Exception("Inventory update conflict for '" . htmlspecialchars($item->title) . "'. The last copies were just purchased by another reader.");
        }
    }

    // Section 8: Clear User Cart
    $clearCart = $connection->prepare("DELETE FROM cart WHERE user_id = :uid");
    $clearCart->execute([':uid' => $user_id]);

    // Commit Transaction
    $connection->commit();

    $response = [
        "status"   => "success",
        "message"  => "Order placed successfully!",
        "order_id" => $order_id,
        "redirect" => "/orders.php?success=1&order_id=" . $order_id
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
