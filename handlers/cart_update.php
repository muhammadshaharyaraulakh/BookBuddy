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
    $action  = trim($_POST['action'] ?? '');
    $new_qty = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);

    if (!$cart_id || $cart_id <= 0) {
        throw new Exception("Invalid cart item.");
    }

    // Section 3: Cart Item Ownership & Existence Check (IDOR Protection)
    $stmt = $connection->prepare("
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
        WHERE c.id = :cid AND c.user_id = :uid
        LIMIT 1
    ");
    $stmt->execute([':cid' => $cart_id, ':uid' => $user_id]);
    $item = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$item) {
        throw new Exception("Cart item not found or no longer available.");
    }

    // Section 3B: Active Daily Deal Validation
    if (!empty($item->is_on_deal) && (int)$item->is_on_deal > 0) {
        throw new Exception("'" . htmlspecialchars($item->title) . "' is currently featured in an active Daily Deal and cannot be modified. Please remove it from your cart.");
    }

    // Section 4: Target Quantity Calculation & Over-Stock Resolution
    $currentCartQty = (int)$item->cart_quantity;
    $liveStock      = (int)$item->Stock;

    if ($liveStock <= 0) {
        throw new Exception("Sorry, '" . htmlspecialchars($item->title) . "' is now out of stock.");
    }

    if ($action === 'inc') {
        if ($currentCartQty >= $liveStock) {
            throw new Exception("Only {$liveStock} " . ($liveStock === 1 ? "copy" : "copies") . " available in stock.");
        }
        $targetQty = $currentCartQty + 1;
    } elseif ($action === 'dec') {
        // If current cart quantity exceeds available stock, decrement down to liveStock (or lower)
        if ($currentCartQty > $liveStock) {
            $targetQty = min($currentCartQty - 1, $liveStock);
        } else {
            $targetQty = $currentCartQty - 1;
        }
    } elseif ($new_qty !== null && $new_qty !== false) {
        $targetQty = (int)$new_qty;
    } else {
        throw new Exception("Invalid quantity change requested.");
    }

    if ($targetQty < 1) {
        throw new Exception("Quantity cannot be less than 1. Use remove to delete this item.");
    }

    // Section 5: Maximum Purchase Limit Verification (Max 10 per book)
    $MAX_LIMIT = 10;
    if ($targetQty > $MAX_LIMIT) {
        throw new Exception("Maximum purchase limit is {$MAX_LIMIT} copies per book.");
    }

    // Section 6: Current Live Stock Verification
    if ($targetQty > $liveStock) {
        throw new Exception("Only {$liveStock} " . ($liveStock === 1 ? "copy" : "copies") . " available in stock.");
    }

    // Section 7: Database Persistence (Atomic Update)
    $updateStmt = $connection->prepare("
        UPDATE cart 
        SET quantity = :qty, updated_at = CURRENT_TIMESTAMP 
        WHERE id = :cid AND user_id = :uid
    ");
    $updateStmt->execute([
        ':qty' => $targetQty,
        ':cid' => $cart_id,
        ':uid' => $user_id
    ]);

    // Section 8: Live Calculation & Recalculation of Totals
    $price = ($item->Discount_Percentage !== null && $item->Discount_Percentage > 0)
        ? (float)$item->Discount_Price
        : (float)$item->Original_Price;

    $itemSubtotal = $price * $targetQty;

    // Recalculate Grand Total for User
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
    $allItems = $totalsStmt->fetchAll(PDO::FETCH_OBJ);

    $cartGrandTotal = 0.0;
    $totalItemCount = 0;
    foreach ($allItems as $row) {
        $rowPrice = ($row->Discount_Percentage !== null && $row->Discount_Percentage > 0)
            ? (float)$row->Discount_Price
            : (float)$row->Original_Price;
        $cartGrandTotal += $rowPrice * (int)$row->quantity;
        $totalItemCount += (int)$row->quantity;
    }

    // Audit remaining issues (out of stock, over limit, or active daily deals) across cart
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
        "message"         => "Quantity updated successfully",
        "cart_id"         => $cart_id,
        "new_quantity"    => $targetQty,
        "live_stock"      => $liveStock,
        "has_stock_issue" => $hasStockIssue,
        "item_price"      => number_format($price, 2),
        "item_subtotal"   => number_format($itemSubtotal, 2),
        "cart_subtotal"   => number_format($cartGrandTotal, 2),
        "cart_total"      => number_format($cartGrandTotal, 2),
        "total_count"     => $totalItemCount
    ];

} catch (Exception $e) {
    $response['status']  = "error";
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;
