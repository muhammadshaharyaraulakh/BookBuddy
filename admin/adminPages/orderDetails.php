<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$order_id) {
    die("<div id='content'><h2>Invalid Order ID</h2><a href='/adminorders' class='btn btn-primary'>Back to Orders</a></div>");
}

$orderQuery = $connection->prepare("
    SELECT o.*, u.fullname, u.email, a.address, a.city, a.province, a.postcode, a.contact as shipping_contact 
    FROM orders o
    JOIN user u ON o.user_id = u.id
    LEFT JOIN user_address a ON o.shipping_address_id = a.id
    WHERE o.id = :id
");
$orderQuery->execute([':id' => $order_id]);
$order = $orderQuery->fetch(PDO::FETCH_OBJ);

if (!$order) {
    die("<div id='content'><h2>Order Not Found</h2><a href='/adminorders' class='btn btn-primary'>Back to Orders</a></div>");
}

$itemsQuery = $connection->prepare("
    SELECT oi.*, b.title AS catalog_title, b.coverImage 
    FROM order_items oi
    LEFT JOIN book b ON oi.book_id = b.id
    WHERE oi.order_id = :oid
");
$itemsQuery->execute([':oid' => $order_id]);
$items = $itemsQuery->fetchAll(PDO::FETCH_OBJ);
?>
<div id="content" class="order-details-page">
    <div class="order-details-header">
        <div>
            <h2 style="font-size: clamp(20px, 4vw, 26px); font-weight: 800;">Order Details #ORD-<?= htmlspecialchars($order->id) ?></h2>
            <p style="font-size: 13px; color: var(--neo-gray-muted); font-weight: 600; margin-top: 2px;">
                Placed on <?= date('F d, Y \a\t h:i A', strtotime($order->created_at)) ?>
            </p>
        </div>
        <a href="/adminorders" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px;">
            <i class="ph-bold ph-arrow-left"></i> Back to Orders
        </a>
    </div>

    <div class="order-details-grid">
        <div class="order-info-card">
            <div class="card-header-label">
                <i class="ph-bold ph-user"></i> Customer Info
            </div>
            <div class="info-row">
                <span class="info-title">Name:</span>
                <span class="info-value"><?= htmlspecialchars($order->fullname) ?></span>
            </div>
            <div class="info-row">
                <span class="info-title">Email:</span>
                <span class="info-value" style="word-break: break-all;"><?= htmlspecialchars($order->email) ?></span>
            </div>
            <hr class="info-divider">
            <div class="card-header-label">
                <i class="ph-bold ph-map-pin"></i> Shipping Address
            </div>
            <?php if(!empty($order->shipping_address_text)): ?>
                <div class="address-box">
                    <strong>Locked Snapshot:</strong><br>
                    <?= htmlspecialchars($order->shipping_address_text) ?>
                </div>
            <?php elseif($order->address): ?>
                <p><strong>Address:</strong> <?= htmlspecialchars($order->address) ?></p>
                <p><strong>City/Province:</strong> <?= htmlspecialchars($order->city) ?>, <?= htmlspecialchars($order->province) ?> (<?= htmlspecialchars($order->postcode) ?>)</p>
                <p><strong>Shipping Phone:</strong> <?= htmlspecialchars($order->shipping_contact) ?></p>
            <?php else: ?>
                <p style="color: var(--neo-gray-muted);">No shipping address recorded.</p>
            <?php endif; ?>
        </div>

        <div class="order-info-card">
            <div class="card-header-label">
                <i class="ph-bold ph-receipt"></i> Order Summary
            </div>
            <div class="info-row">
                <span class="info-title">Date:</span>
                <span class="info-value"><?= htmlspecialchars($order->created_at) ?></span>
            </div>
            <div class="info-row">
                <span class="info-title">Method:</span>
                <span class="info-value"><?= htmlspecialchars($order->payment_method) ?></span>
            </div>
            <div class="info-row">
                <span class="info-title">Payment Status:</span>
                <span class="status-badge status-<?= strtolower($order->payment_status) ?>">
                    <?= ucfirst(htmlspecialchars($order->payment_status)) ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-title">Order Status:</span>
                <span class="status-badge status-<?= strtolower($order->order_status) ?>">
                    <?= ucfirst(htmlspecialchars($order->order_status)) ?>
                </span>
            </div>
            <hr class="info-divider">
            <div class="total-amount-box">
                <span>Total Amount</span>
                <strong>$<?= number_format($order->total_amount, 2) ?></strong>
            </div>
        </div>
    </div>

    <div class="table-container order-items-container">
        <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-books"></i> Items Ordered (<?= count($items) ?>)
        </h3>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Book Title</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) > 0): ?>
                    <?php foreach($items as $item): ?>
                        <?php 
                            $itemTitle = $item->book_title ?? $item->catalog_title ?? 'Book';
                            $itemCover = (!empty($item->coverImage) && file_exists(__DIR__ . "/../../images/" . $item->coverImage)) ? $item->coverImage : 'logo.png';
                            $isDeleted = empty($item->book_id) || empty($item->catalog_title);
                        ?>
                        <tr>
                            <td>
                                <img src="/images/<?= htmlspecialchars($itemCover) ?>" alt="<?= htmlspecialchars($itemTitle) ?>" class="order-item-img" onerror="this.src='/images/logo.png'">
                            </td>
                            <td>
                                <div style="font-weight: 800; font-size: 15px; color: var(--neo-black);">
                                    <?= htmlspecialchars($itemTitle) ?>
                                </div>
                                <?php if ($isDeleted): ?>
                                    <div style="margin-top: 4px;">
                                        <span class="badge-catalog-deleted">
                                            <i class="ph-bold ph-trash"></i> Deleted from Catalog
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size: 12px; color: var(--neo-gray-muted); margin-top: 2px;">
                                        Catalog ID: #<?= (int)$item->book_id ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($item->price_at_purchase, 2) ?></td>
                            <td><span class="qty-badge"><?= (int)$item->quantity ?></span></td>
                            <td style="font-weight: 800; color: var(--neo-black);">$<?= number_format($item->price_at_purchase * $item->quantity, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: var(--neo-gray-muted);">No items found in this order.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/assests/js/admin.js"></script>
</body>
</html>
