<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$order_id) {
    die("<div id='content'><h2>Invalid Order ID</h2><a href='/admin/adminPages/order.php' class='btn btn-primary'>Back to Orders</a></div>");
}

// Fetch Order & User Info
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
    die("<div id='content'><h2>Order Not Found</h2><a href='/admin/adminPages/order.php' class='btn btn-primary'>Back to Orders</a></div>");
}

// Fetch Order Items
$itemsQuery = $connection->prepare("
    SELECT oi.*, b.title, b.coverImage 
    FROM order_items oi
    JOIN book b ON oi.book_id = b.id
    WHERE oi.order_id = :oid
");
$itemsQuery->execute([':oid' => $order_id]);
$items = $itemsQuery->fetchAll(PDO::FETCH_OBJ);
?>
<div id="content">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h2>Order Details #ORD-<?= htmlspecialchars($order->id) ?></h2>
        <a href="/admin/adminPages/order.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Customer Info</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($order->fullname) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order->email) ?></p>
            <hr style="margin: 10px 0; border: none; border-top: 1px solid #eee;">
            <h3>Shipping Address</h3>
            <?php if($order->address): ?>
                <p><strong>Address:</strong> <?= htmlspecialchars($order->address) ?></p>
                <p><strong>City/Province:</strong> <?= htmlspecialchars($order->city) ?>, <?= htmlspecialchars($order->province) ?> (<?= htmlspecialchars($order->postcode) ?>)</p>
                <p><strong>Shipping Phone:</strong> <?= htmlspecialchars($order->shipping_contact) ?></p>
            <?php else: ?>
                <p>No shipping address recorded.</p>
            <?php endif; ?>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3>Order Summary</h3>
            <p><strong>Date:</strong> <?= htmlspecialchars($order->created_at) ?></p>
            <p><strong>Method:</strong> <?= htmlspecialchars($order->payment_method) ?></p>
            <p>
                <strong>Payment Status:</strong> 
                <span style="color: <?= $order->payment_status === 'completed' ? 'green' : 'var(--orange)' ?>;">
                    <?= ucfirst(htmlspecialchars($order->payment_status)) ?>
                </span>
            </p>
            <p>
                <strong>Order Status:</strong> 
                <span style="color: <?= $order->order_status === 'delivered' ? 'green' : ($order->order_status === 'cancelled' ? 'red' : 'blue') ?>;">
                    <?= ucfirst(htmlspecialchars($order->order_status)) ?>
                </span>
            </p>
            <hr style="margin: 10px 0; border: none; border-top: 1px solid #eee;">
            <h3 style="color: green;">Total Amount: $<?= number_format($order->total_amount, 2) ?></h3>
        </div>
    </div>

    <div class="table-container">
        <h3>Items Ordered</h3>
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
                <?php foreach($items as $item): ?>
                <tr>
                    <td><img src="/images/<?= htmlspecialchars($item->coverImage) ?>" alt="Cover" style="width: 50px; height: 75px; object-fit: cover; border-radius: 4px;"></td>
                    <td><?= htmlspecialchars($item->title) ?></td>
                    <td>$<?= number_format($item->price_at_purchase, 2) ?></td>
                    <td><?= htmlspecialchars($item->quantity) ?></td>
                    <td>$<?= number_format($item->price_at_purchase * $item->quantity, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/assests/js/admin.js"></script>
</body>
</html>
