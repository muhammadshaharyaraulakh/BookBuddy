<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . "/config/config.php";

// Section 1: Fetch Live Customer Orders & Items
$user_id = $_SESSION['id'] ?? null;
$userOrders = [];

if ($user_id) {
    $ordersStmt = $connection->prepare("
        SELECT o.* 
        FROM orders o 
        WHERE o.user_id = :uid 
        ORDER BY o.created_at DESC
    ");
    $ordersStmt->execute([':uid' => $user_id]);
    $userOrders = $ordersStmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($userOrders as $order) {
        $itemsStmt = $connection->prepare("
            SELECT oi.*, b.coverImage, b.title AS catalog_title, b.author 
            FROM order_items oi 
            LEFT JOIN book b ON oi.book_id = b.id 
            WHERE oi.order_id = :oid
        ");
        $itemsStmt->execute([':oid' => $order->id]);
        $order->items = $itemsStmt->fetchAll(PDO::FETCH_OBJ);
    }
}

$successOrderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

require __DIR__ . "/includes/header.php";
require __DIR__ . "/includes/navigationbar.php";
?>

<!-- Section 2: Header Banner -->
<div class="page-header-banner">
    <h1 class="page-header-title">My Orders</h1>
    <p class="page-header-subtitle">Track your literary shipments, delivery status and purchase history</p>
</div>

<?php if ($successOrderId): ?>
    <div style="max-width: 900px; margin: 0 auto 24px; padding: 0 20px;">
        <div style="background: #D1FAE5; border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 18px 24px; font-weight: 800; color: #065F46; display: flex; align-items: center; gap: 14px; box-shadow: var(--neo-shadow);">
            <i class="ph-bold ph-check-circle" style="font-size: 28px;"></i>
            <div>
                <div style="font-size: 16px;">Order #ORD-<?= htmlspecialchars((string)$successOrderId) ?> placed successfully!</div>
                <div style="font-size: 13px; font-weight: 600; color: #047857; margin-top: 2px;">Your order has been recorded with Cash on Delivery payment. Our team is preparing your shipment.</div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="orders-layout">
    <?php if (!$user_id): ?>
        <!-- Section 3: Guest Login Required -->
        <div style="max-width: 600px; margin: 40px auto; padding: 0 20px; width: 100%;">
            <div style="background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 48px 24px; text-align: center; box-shadow: var(--neo-shadow-lg);">
                <div style="font-size: 48px; margin-bottom: 12px; color: var(--neo-pink);">
                    <i class="ph-bold ph-lock-key"></i>
                </div>
                <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Log In to View Orders</h2>
                <p style="color: var(--neo-gray-muted); font-size: 15px; margin-bottom: 24px;">Please sign in to track your current shipments and view complete order history.</p>
                <a href="/login" class="neo-btn btn-yellow" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; font-weight: 800; text-decoration: none; border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); box-shadow: var(--neo-shadow); background: var(--neo-yellow); color: var(--neo-black);">
                    <i class="ph-bold ph-sign-in"></i> Sign In to Account
                </a>
            </div>
        </div>
    <?php elseif (empty($userOrders)): ?>
        <!-- Section 4: Empty Orders -->
        <div style="max-width: 600px; margin: 40px auto; padding: 0 20px; width: 100%;">
            <div style="background: var(--neo-white); border: var(--neo-border-width) solid var(--neo-black); border-radius: var(--neo-radius); padding: 48px 24px; text-align: center; box-shadow: var(--neo-shadow-lg);">
                <div style="font-size: 48px; margin-bottom: 12px; color: var(--neo-yellow);">
                    <i class="ph-bold ph-package"></i>
                </div>
                <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">No Orders Found</h2>
                <p style="color: var(--neo-gray-muted); font-size: 15px; margin-bottom: 24px;">You haven't placed any orders yet. Browse our collection to find your next great read.</p>
                <a href="/shop.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; font-weight: 800; text-decoration: none; border: 2.5px solid var(--neo-black); border-radius: var(--neo-radius-sm); box-shadow: var(--neo-shadow); background: var(--neo-teal); color: var(--neo-black);">
                    <i class="ph-bold ph-storefront"></i> Browse Books
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Section 5: Dynamic Orders List -->
        <?php foreach ($userOrders as $order): ?>
            <?php 
                $statusPillClass = 'order-badge-processing';
                $statusIcon = 'ph-clock';
                if ($order->order_status === 'delivered') {
                    $statusPillClass = 'order-badge-delivered';
                    $statusIcon = 'ph-check';
                } elseif ($order->order_status === 'shipped') {
                    $statusPillClass = 'order-badge-processing';
                    $statusIcon = 'ph-truck';
                } elseif ($order->order_status === 'cancelled') {
                    $statusPillClass = 'order-badge-cancelled';
                    $statusIcon = 'ph-x';
                }
            ?>
            <div class="order-history-card">
                <div class="order-card-header">
                    <div>
                        <div style="font-size: 16px; font-weight: 800;">Order #ORD-<?= htmlspecialchars((string)$order->id) ?></div>
                        <div style="font-size: 13px; font-weight: 600; color: #6B7280;">
                            Placed on <?= date("F j, Y", strtotime($order->created_at)) ?> &bull; <?= htmlspecialchars($order->payment_method) ?>
                        </div>
                    </div>
                    <span class="order-badge-pill <?= $statusPillClass ?>">
                        <i class="ph-bold <?= $statusIcon ?>"></i> <?= ucfirst(htmlspecialchars($order->order_status)) ?>
                    </span>
                </div>

                <?php if (!empty($order->items)): ?>
                    <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 8px;">
                        <?php foreach ($order->items as $item): ?>
                            <?php 
                                $itemTitle = $item->book_title ?? $item->catalog_title ?? 'Book';
                                $itemCover = $item->coverImage ?? 'logo.png';
                            ?>
                            <div style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                                <img src="/images/<?= htmlspecialchars($itemCover) ?>" alt="<?= htmlspecialchars($itemTitle) ?>" style="width: 50px; height: 65px; border: 2px solid var(--neo-black); border-radius: 4px; object-fit: cover;" onerror="this.src='/images/logo.png'" />
                                <div style="flex-grow: 1;">
                                    <div style="font-size: 15px; font-weight: 800;"><?= htmlspecialchars($itemTitle) ?></div>
                                    <div style="font-size: 13px; font-weight: 600; color: #6B7280;">
                                        Qty: <?= (int)$item->quantity ?> &bull; $<?= number_format((float)$item->price_at_purchase, 2) ?> each
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 16px; font-weight: 800;">$<?= number_format((float)$item->price_at_purchase * (int)$item->quantity, 2) ?></div>
                                    <span style="font-size: 12px; font-weight: 700; color: var(--neo-teal);">Cash on Delivery</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($order->shipping_address_text)): ?>
                    <div style="margin-top: 14px; padding: 10px 14px; background: #F8FAFC; border: 1.5px solid #000; border-radius: 6px; font-size: 13px; color: #1F2937;">
                        <div style="font-weight: 800; display: flex; align-items: center; gap: 6px; margin-bottom: 2px; color: #000;">
                            <i class="ph-bold ph-map-pin" style="color: var(--neo-teal);"></i> Delivery Address (Locked for this order):
                        </div>
                        <div style="font-weight: 600;"><?= htmlspecialchars($order->shipping_address_text) ?></div>
                    </div>
                <?php endif; ?>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 12px; border-top: 1.5px solid #E5E7EB;">
                    <span style="font-size: 13px; font-weight: 700; color: #6B7280;">Order Total</span>
                    <span style="font-size: 18px; font-weight: 800; color: var(--neo-black);">$<?= number_format((float)$order->total_amount, 2) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php 
require __DIR__ . "/includes/footersection.php";
require __DIR__ . "/includes/footer.php";
?>
