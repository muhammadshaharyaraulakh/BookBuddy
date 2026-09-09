<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/expireDeals.php";

$user_id = $_SESSION['id'] ?? null;
$cartItems = [];
$allTotal = 0.0;
$shipping = 0.0;
$total = 0.0;
$hasStockIssue = false;
$cartNotifications = [];

if ($user_id) {
    // Section 1: Fetch Raw Cart Entries with Catalog Join
    $stmt = $connection->prepare("
        SELECT 
            c.id AS cart_id,
            c.quantity AS cart_quantity,
            c.book_id,
            b.id,
            b.title,
            b.author,
            b.Publisher,
            b.Original_Price,
            b.Discount_Price,
            b.Discount_Percentage,
            b.Stock,
            b.coverImage,
            b.category_id,
            cat.title AS category_name,
            (
                SELECT COUNT(*) 
                FROM deals d 
                WHERE d.book_id = b.id 
                  AND d.status = 'active' 
                  AND CURRENT_TIMESTAMP < d.end_time
            ) AS is_on_deal
        FROM cart c
        LEFT JOIN book b ON c.book_id = b.id
        LEFT JOIN categories cat ON b.category_id = cat.id
        WHERE c.user_id = :uid
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([':uid' => $user_id]);
    $rawItems = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Section 2: Catalog Integrity & Auto-Cleanup of Deleted Books
    foreach ($rawItems as $rawItem) {
        if (empty($rawItem->id) || $rawItem->title === null) {
            $delOrphan = $connection->prepare("DELETE FROM cart WHERE id = :cid AND user_id = :uid");
            $delOrphan->execute([':cid' => $rawItem->cart_id, ':uid' => $user_id]);
            $cartNotifications[] = "An item previously in your cart was removed because it is no longer available in our catalog.";
            continue;
        }

        // Section 3: Live Price Calculation (Respecting Price Changes & Active Deals)
        $effectivePrice = ($rawItem->Discount_Percentage !== null && $rawItem->Discount_Percentage > 0)
            ? (float)$rawItem->Discount_Price
            : (float)$rawItem->Original_Price;

        $rawItem->effective_price = $effectivePrice;

        // Section 4: Live Inventory Audit & Active Daily Deal Check
        $stock = (int)$rawItem->Stock;
        $qty   = (int)$rawItem->cart_quantity;

        $rawItem->is_out_of_stock = ($stock <= 0);
        $rawItem->is_daily_deal   = (!empty($rawItem->is_on_deal) && (int)$rawItem->is_on_deal > 0);

        if ($rawItem->is_daily_deal) {
            $hasStockIssue = true;
            $rawItem->is_over_stock = false;
            $rawItem->stock_message = "Featured in Daily Deal";
            $cartNotifications[] = "'" . htmlspecialchars($rawItem->title) . "' is currently featured in an active Daily Deal. Daily deal items cannot be purchased via standard cart; please remove it to proceed to checkout.";
        } elseif ($rawItem->is_out_of_stock) {
            $hasStockIssue = true;
            $rawItem->is_over_stock = false;
            $rawItem->stock_message = "Out of stock";
        } elseif ($stock > 0 && $qty > $stock) {
            // Auto-adjust cart quantity to match live available stock
            $adjustStmt = $connection->prepare("
                UPDATE cart 
                SET quantity = :new_qty, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :cid AND user_id = :uid
            ");
            $adjustStmt->execute([
                ':new_qty' => $stock,
                ':cid'     => $rawItem->cart_id,
                ':uid'     => $user_id
            ]);

            $cartNotifications[] = "Quantity for '" . htmlspecialchars($rawItem->title) . "' was automatically adjusted from {$qty} to {$stock} because only {$stock} " . ($stock === 1 ? "copy remains" : "copies remain") . " in stock.";
            $qty = $stock;
            $rawItem->cart_quantity = $stock;
            $rawItem->is_over_stock = false;
            $rawItem->stock_message = "Only {$stock} left in stock";
        } else {
            $rawItem->is_over_stock = false;
            $rawItem->stock_message = ($stock < 5) ? "Only {$stock} left in stock" : "In Stock ({$stock})";
        }

        // Section 5: Item Subtotal & Accumulation
        $rawItem->item_subtotal = $effectivePrice * $qty;
        if (!$rawItem->is_out_of_stock && !$rawItem->is_daily_deal) {
            $allTotal += $rawItem->item_subtotal;
        }

        $cartItems[] = $rawItem;
    }

    // Section 6: Grand Total Calculation
    $allTotal = round($allTotal, 2);
    $total = round($allTotal + $shipping, 2);
}
?>