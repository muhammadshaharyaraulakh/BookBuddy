<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";

header('Content-Type: application/json');

$response = [
    "status"  => "error",
    "message" => "Unexpected error occurred"
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
        throw new Exception("Please log in to manage your cart.");
    }

    $user_id = (int)$_SESSION['id'];

    // Section 2: Input Sanitization & Type Validation
    $cart_id = filter_var($_POST['cart_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$cart_id || $cart_id <= 0) {
        throw new Exception("Invalid cart item selected for removal.");
    }

    // Section 3: Cart Item Ownership & Existence Verification (IDOR Protection)
    $checkStmt = $connection->prepare("
        SELECT id, book_id 
        FROM cart 
        WHERE id = :cid AND user_id = :uid 
        LIMIT 1
    ");
    $checkStmt->execute([':cid' => $cart_id, ':uid' => $user_id]);
    $item = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        throw new Exception("Item not found in your cart.");
    }

    // Section 4: Database Persistence (Atomic Deletion)
    $delStmt = $connection->prepare("
        DELETE FROM cart 
        WHERE id = :cid AND user_id = :uid
    ");
    $delStmt->execute([':cid' => $cart_id, ':uid' => $user_id]);

    // Section 5: Recalculate Live Totals After Deletion
    $totalsStmt = $connection->prepare("
        SELECT 
            c.quantity, 
            b.Original_Price, 
            b.Discount_Price, 
            b.Discount_Percentage 
        FROM cart c
        JOIN book b ON c.book_id = b.id
        WHERE c.user_id = :uid
    ");
    $totalsStmt->execute([':uid' => $user_id]);
    $remainingItems = $totalsStmt->fetchAll(PDO::FETCH_OBJ);

    $cartGrandTotal = 0.0;
    $totalItemCount = 0;
    $distinctItemsCount = count($remainingItems);

    foreach ($remainingItems as $row) {
        $rowPrice = ($row->Discount_Percentage !== null && $row->Discount_Percentage > 0)
            ? (float)$row->Discount_Price
            : (float)$row->Original_Price;
        $cartGrandTotal += $rowPrice * (int)$row->quantity;
        $totalItemCount += (int)$row->quantity;
    }

    // Section 6: Check Remaining Issues (Stock or Active Daily Deals) Across User Cart
    $auditStmt = $connection->prepare("
        SELECT COUNT(*) 
        FROM cart c 
        JOIN book b ON c.book_id = b.id 
        WHERE c.user_id = :uid 
          AND (
            b.Stock <= 0 
            OR c.quantity > b.Stock 
            OR EXISTS (
                SELECT 1 FROM deals d 
                WHERE d.book_id = b.id AND d.status = 'active' AND CURRENT_TIMESTAMP < d.end_time
            )
          )
    ");
    $auditStmt->execute([':uid' => $user_id]);
    $remainingStockIssues = (int)$auditStmt->fetchColumn();
    $hasStockIssue = ($remainingStockIssues > 0);

    $response = [
        "status"          => "success",
        "message"         => "Item removed from cart successfully.",
        "cart_id"         => $cart_id,
        "items_count"     => $distinctItemsCount,
        "total_quantity"  => $totalItemCount,
        "has_stock_issue" => $hasStockIssue,
        "cart_subtotal"   => number_format($cartGrandTotal, 2),
        "cart_total"      => number_format($cartGrandTotal, 2)
    ];

} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
