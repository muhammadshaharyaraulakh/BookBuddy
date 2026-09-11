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
        throw new Exception("Please log in to claim this daily deal.");
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

    // Section 3: Daily Deal Verification
    $deal_id = filter_var($_POST['deal_id'] ?? '', FILTER_VALIDATE_INT);
    $book_id = filter_var($_POST['book_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$deal_id && !$book_id) {
        throw new Exception("Invalid Daily Deal reference.");
    }

    // Begin Atomic Database Transaction
    $connection->beginTransaction();

    $dealQuery = "
        SELECT 
            d.id AS deal_id, 
            d.discount_percentage, 
            d.end_time,
            d.status,
            b.id AS book_id, 
            b.title, 
            b.Original_Price, 
            b.Stock
        FROM deals d
        JOIN book b ON d.book_id = b.id
        WHERE d.status = 'active' 
          AND CURRENT_TIMESTAMP < d.end_time
    ";

    $params = [];
    if ($deal_id) {
        $dealQuery .= " AND d.id = :did";
        $params[':did'] = $deal_id;
    } else {
        $dealQuery .= " AND b.id = :bid";
        $params[':bid'] = $book_id;
    }
    $dealQuery .= " LIMIT 1 FOR UPDATE";

    $dealStmt = $connection->prepare($dealQuery);
    $dealStmt->execute($params);
    $deal = $dealStmt->fetch(PDO::FETCH_OBJ);

    if (!$deal) {
        throw new Exception("This Daily Deal is no longer active or has expired.");
    }

    $liveStock = (int)$deal->Stock;
    if ($liveStock <= 0) {
        throw new Exception("Sorry, '" . htmlspecialchars($deal->title) . "' has just sold out!");
    }

    // Section 4: Price Calculation
    $discountPct = (float)$deal->discount_percentage;
    $dealPrice   = round((float)$deal->Original_Price * (1 - ($discountPct / 100)), 2);

    // Section 5: Create Order Record
    $orderStmt = $connection->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status, shipping_address_id, shipping_address_text) 
        VALUES (:uid, :total, 'Cash on Delivery', 'pending', 'processing', :addr_id, :addr_text)
    ");
    $orderStmt->execute([
        ':uid'       => $user_id,
        ':total'     => $dealPrice,
        ':addr_id'   => $address_id,
        ':addr_text' => $addressSnapshot
    ]);
    $order_id = $connection->lastInsertId();

    // Section 6: Insert Order Item (Daily Deal, Qty 1)
    $itemStmt = $connection->prepare("
        INSERT INTO order_items (order_id, book_id, book_title, quantity, price_at_purchase) 
        VALUES (:oid, :bid, :title, 1, :price)
    ");
    $itemStmt->execute([
        ':oid'   => $order_id,
        ':bid'   => $deal->book_id,
        ':title' => $deal->title,
        ':price' => $dealPrice
    ]);

    // Section 7: Atomic Stock Deduction
    $deductStmt = $connection->prepare("
        UPDATE book 
        SET Stock = Stock - 1 
        WHERE id = :bid AND Stock >= 1
    ");
    $deductStmt->execute([':bid' => $deal->book_id]);

    if ($deductStmt->rowCount() === 0) {
        throw new Exception("Inventory update conflict. The last available copy was just claimed by another reader.");
    }

    // Commit Transaction
    $connection->commit();

    $response = [
        "status"   => "success",
        "message"  => "Daily Deal order placed successfully!",
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
