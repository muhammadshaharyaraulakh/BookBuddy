<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";

$fetchOrders = $connection->prepare("
    SELECT o.*, u.fullname as customer_name 
    FROM orders o
    JOIN user u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$fetchOrders->execute();
$orders = $fetchOrders->fetchAll(PDO::FETCH_OBJ);
?>
<div id="content">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#ORD-<?= $order->id ?></td>
                            <td><?= htmlspecialchars($order->customer_name) ?></td>
                            <td>$<?= number_format($order->total_amount, 2) ?></td>
                            <td>
                                <span style="font-weight:600; text-transform:capitalize; 
                                    color: <?= $order->order_status === 'delivered' ? 'green' : ($order->order_status === 'cancelled' ? 'red' : 'var(--orange)') ?>;">
                                    <?= htmlspecialchars($order->order_status) ?>
                                </span>
                                <br>
                                <small style="color: <?= $order->payment_status === 'completed' ? 'green' : 'gray' ?>;">
                                    Payment: <?= htmlspecialchars($order->payment_status) ?>
                                </small>
                            </td>
                            <td style="display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                
                                <form action="/admin/handlers/updateOrderStatus.php" method="POST" class="ajax-form" style="display: flex; gap: 0.5rem;">
                                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                                    <select name="status" style="padding: 5px; border-radius: 5px; border: 1px solid #ccc; outline: none;">
                                        <option value="processing" <?= $order->order_status === 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order->order_status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $order->order_status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order->order_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button class="btn btn-primary" type="submit">Update</button>
                                </form>

                                <a href="/orderdetails?id=<?= $order->id ?>" class="btn btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="ph-bold ph-eye"></i>
                                </a>

                                <form action="/admin/handlers/deleteOrder.php" method="POST" class="ajax-form" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                    <input type="hidden" name="order_id" value="<?= $order->id ?>">
                                    <button class="btn btn-danger" type="submit"><i class="ph-bold ph-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/assests/js/admin.js"></script>
</body>
</html>