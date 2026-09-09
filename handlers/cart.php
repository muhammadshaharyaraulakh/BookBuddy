<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/expireDeals.php";

header('Content-Type: application/json');

$response = [
    "status"  => "error",
    "message" => "Unexpected error occurred",
    "field"   => "general"
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid request method";
    echo json_encode($response);
    exit;
}

try {
    // Section 1: Authentication Check (Login Verification)
    if (empty($_SESSION['id'])) {
        $response['auth_required'] = true;
        throw new Exception("Please log in to add books to your cart.");
    }

    $user_id = (int)$_SESSION['id'];

    // Section 2: Input Sanitization & Type Validation (Negative, Zero, Decimal Protection)
    $book_id = filter_var($_POST['bookId'] ?? '', FILTER_VALIDATE_INT);
    $raw_qty = $_POST['quantity'] ?? 1;

    if (!$book_id || $book_id <= 0) {
        throw new Exception("Invalid book selected.");
    }

    if (!is_numeric($raw_qty)) {
        throw new Exception("Quantity must be a valid number.");
    }

    $quantity = (int)$raw_qty;
    if ($quantity < 1) {
        throw new Exception("Quantity must be at least 1.");
    }

    // Section 3: Maximum Purchase Limit Verification (Max 10 per book)
    $MAX_LIMIT = 10;
    if ($quantity > $MAX_LIMIT) {
        throw new Exception("You cannot add more than {$MAX_LIMIT} copies of a single book.");
    }

    // Section 4: Catalog Existence Check (Verify Valid Book in Database)
    $bookStmt = $connection->prepare("
        SELECT id, title, Stock, Original_Price, Discount_Price, Discount_Percentage 
        FROM book 
        WHERE id = :id 
        LIMIT 1
    ");
    $bookStmt->execute([':id' => $book_id]);
    $book = $bookStmt->fetch(PDO::FETCH_OBJ);

    if (!$book) {
        throw new Exception("This book is no longer available in our catalog.");
    }

    // Section 4B: Active Daily Deal Validation (Daily Deal Books Cannot Be Added to Standard Cart)
    $dealCheck = $connection->prepare("
        SELECT id FROM deals 
        WHERE book_id = :bid 
          AND status = 'active' 
          AND CURRENT_TIMESTAMP < end_time 
        LIMIT 1
    ");
    $dealCheck->execute([':bid' => $book_id]);
    if ($dealCheck->fetch()) {
        throw new Exception("'" . htmlspecialchars($book->title) . "' is currently featured in an active Daily Deal and cannot be added to the standard cart.");
    }

    // Section 5: Current Live Stock & Out-of-Stock Validation
    $currentStock = (int)$book->Stock;
    if ($currentStock <= 0) {
        throw new Exception("Sorry, '" . htmlspecialchars($book->title) . "' is currently out of stock.");
    }

    if ($quantity > $currentStock) {
        throw new Exception("Only {$currentStock} " . ($currentStock === 1 ? "copy" : "copies") . " available in stock. You requested {$quantity}.");
    }

    // Section 6: Existing Cart Inspection & Cumulative Quantity Check
    $cartStmt = $connection->prepare("
        SELECT id, quantity 
        FROM cart 
        WHERE user_id = :uid AND book_id = :bid 
        LIMIT 1
    ");
    $cartStmt->execute([
        ':uid' => $user_id,
        ':bid' => $book_id
    ]);
    $existingCartItem = $cartStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingCartItem) {
        $existingQuantity = (int)$existingCartItem['quantity'];
        $newTotalQuantity = $existingQuantity + $quantity;

        if ($newTotalQuantity > $MAX_LIMIT) {
            $allowedMore = $MAX_LIMIT - $existingQuantity;
            if ($allowedMore <= 0) {
                throw new Exception("You already have {$existingQuantity} copies in your cart. Limit is {$MAX_LIMIT}.");
            }
            throw new Exception("You already have {$existingQuantity} in your cart. You can only add {$allowedMore} more.");
        }

        if ($newTotalQuantity > $currentStock) {
            $stockRemaining = $currentStock - $existingQuantity;
            if ($stockRemaining <= 0) {
                throw new Exception("You already have all {$currentStock} available " . ($currentStock === 1 ? "copy" : "copies") . " in your cart.");
            }
            throw new Exception("Only {$stockRemaining} more " . ($stockRemaining === 1 ? "copy" : "copies") . " available in stock. (Total in cart would be {$newTotalQuantity}).");
        }

        // Section 7: Database Persistence (Atomic Insert or Update)
        $updateStmt = $connection->prepare("
            UPDATE cart 
            SET quantity = :qty, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id AND user_id = :uid
        ");
        $updateStmt->execute([
            ':qty' => $newTotalQuantity,
            ':id'  => $existingCartItem['id'],
            ':uid' => $user_id
        ]);
        $finalItemQuantity = $newTotalQuantity;
    } else {
        // Section 7: Database Persistence (Atomic Insert or Update)
        $insertStmt = $connection->prepare("
            INSERT INTO cart (user_id, book_id, quantity, created_at, updated_at) 
            VALUES (:uid, :bid, :qty, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $insertStmt->execute([
            ':uid' => $user_id,
            ':bid' => $book_id,
            ':qty' => $quantity
        ]);
        $finalItemQuantity = $quantity;
    }

    // Section 8: Live Cart Count & Response Generation
    $countStmt = $connection->prepare("
        SELECT COALESCE(SUM(quantity), 0) AS total_count 
        FROM cart 
        WHERE user_id = :uid
    ");
    $countStmt->execute([':uid' => $user_id]);
    $totalCartCount = (int)$countStmt->fetchColumn();

    $response = [
        "status"        => "success",
        "message"       => "Added {$quantity} " . ($quantity === 1 ? "copy" : "copies") . " of '" . htmlspecialchars($book->title) . "' to your cart!",
        "cart_count"    => $totalCartCount,
        "item_quantity" => $finalItemQuantity,
        "book_title"    => $book->title
    ];

} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
