<?php 
require __DIR__."/../../config/config.php";
require __DIR__."/../../includes/dashboardHeader.php";

// Fetch dynamic counts for all filter tabs in a single efficient query
$countsStmt = $connection->query("
    SELECT 
        COUNT(*) as count_all,
        SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as count_processing,
        SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as count_shipped,
        SUM(CASE WHEN order_status = 'delivered' AND (COALESCE(updated_at, created_at) >= NOW() - INTERVAL 1 DAY) THEN 1 ELSE 0 END) as count_delivered_24h,
        SUM(CASE WHEN order_status IN ('cancelled', 'returned') AND (COALESCE(updated_at, created_at) >= NOW() - INTERVAL 1 DAY) THEN 1 ELSE 0 END) as count_cancelled_24h
    FROM orders
");
$counts = $countsStmt->fetch(PDO::FETCH_OBJ);
$countAll          = (int)($counts->count_all ?? 0);
$countProcessing   = (int)($counts->count_processing ?? 0);
$countShipped      = (int)($counts->count_shipped ?? 0);
$countDelivered24h = (int)($counts->count_delivered_24h ?? 0);
$countCancelled24h = (int)($counts->count_cancelled_24h ?? 0);

// Determine active filter
$filter = filter_input(INPUT_GET, 'filter', FILTER_DEFAULT) ?? 'all';
if (!in_array($filter, ['all', 'processing', 'shipped', 'delivered_24h', 'cancelled_24h'])) {
    $filter = 'all';
}

$whereClause = "";
switch ($filter) {
    case 'processing':
        $whereClause = "WHERE o.order_status = 'processing'";
        break;
    case 'shipped':
        $whereClause = "WHERE o.order_status = 'shipped'";
        break;
    case 'delivered_24h':
        $whereClause = "WHERE o.order_status = 'delivered' AND (COALESCE(o.updated_at, o.created_at) >= NOW() - INTERVAL 1 DAY)";
        break;
    case 'cancelled_24h':
        $whereClause = "WHERE o.order_status IN ('cancelled', 'returned') AND (COALESCE(o.updated_at, o.created_at) >= NOW() - INTERVAL 1 DAY)";
        break;
    default:
        $whereClause = "";
        break;
}

$fetchOrders = $connection->prepare("
    SELECT o.*, u.fullname as customer_name, u.email as customer_email,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o
    JOIN user u ON o.user_id = u.id
    {$whereClause}
    ORDER BY o.created_at DESC
");
$fetchOrders->execute();
$orders = $fetchOrders->fetchAll(PDO::FETCH_OBJ);
?>
<div id="content">
    <div class="orders-page-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div>
                <h1 style="font-size: clamp(22px, 4vw, 28px); font-weight: 800; color: var(--neo-black);">
                    Orders Management
                </h1>
                <p style="font-size: 13px; color: var(--neo-gray-muted); font-weight: 600; margin-top: 2px;">
                    Track customer purchases, update live fulfillment status and manage inventory
                </p>
            </div>
            <div style="font-size: 13px; font-weight: 800; background: var(--neo-yellow); border: 2px solid var(--neo-black); border-radius: var(--neo-radius-pill); padding: 6px 14px; box-shadow: 2px 2px 0px var(--neo-black);">
                Total Orders: <?= $countAll ?>
            </div>
        </div>

        <!-- Filter Tabs / Links -->
        <div class="order-filter-bar">
            <a href="/adminorders?filter=all" class="filter-pill <?= $filter === 'all' ? 'active' : '' ?>">
                <i class="ph-bold ph-list-bullets"></i> All Orders
                <span class="filter-count"><?= $countAll ?></span>
            </a>
            <a href="/adminorders?filter=processing" class="filter-pill <?= $filter === 'processing' ? 'active' : '' ?>">
                <i class="ph-bold ph-hourglass"></i> Processing
                <span class="filter-count"><?= $countProcessing ?></span>
            </a>
            <a href="/adminorders?filter=shipped" class="filter-pill <?= $filter === 'shipped' ? 'active' : '' ?>">
                <i class="ph-bold ph-truck"></i> Shipped
                <span class="filter-count"><?= $countShipped ?></span>
            </a>
            <a href="/adminorders?filter=delivered_24h" class="filter-pill <?= $filter === 'delivered_24h' ? 'active' : '' ?>">
                <i class="ph-bold ph-check-circle"></i> Delivered (Last 24h)
                <span class="filter-count"><?= $countDelivered24h ?></span>
            </a>
            <a href="/adminorders?filter=cancelled_24h" class="filter-pill <?= $filter === 'cancelled_24h' ? 'active' : '' ?>">
                <i class="ph-bold ph-x-circle"></i> Cancelled (Last 24h)
                <span class="filter-count"><?= $countCancelled24h ?></span>
            </a>
        </div>
    </div>

    <!-- Orders Table Container -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items & Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 800; font-size: 15px; color: var(--neo-black);">
                                    #ORD-<?= (int)$order->id ?>
                                </div>
                                <div style="font-size: 11px; color: var(--neo-gray-muted); font-weight: 600; margin-top: 2px;">
                                    <?= date('M d, Y', strtotime($order->created_at)) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--neo-black);">
                                    <?= htmlspecialchars($order->customer_name) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--neo-gray-muted); font-weight: 600;">
                                    <?= htmlspecialchars($order->customer_email) ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 800; font-size: 15px; color: var(--neo-black);">
                                    $<?= number_format($order->total_amount, 2) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--neo-gray-muted); font-weight: 700;">
                                    <?= (int)$order->item_count ?> <?= (int)$order->item_count === 1 ? 'item' : 'items' ?>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($order->order_status) ?>">
                                    <?= ucfirst(htmlspecialchars($order->order_status)) ?>
                                </span>
                                <div style="margin-top: 4px;">
                                    <small style="font-size: 11px; font-weight: 700; color: <?= $order->payment_status === 'completed' || $order->payment_status === 'paid' ? '#059669' : ($order->payment_status === 'failed' ? '#DC2626' : '#6B7280') ?>;">
                                        Pay: <?= ucfirst(htmlspecialchars($order->payment_status)) ?>
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="order-actions-cell">
                                    <form action="/admin/handlers/updateOrderStatus.php" method="POST" class="ajax-form">
                                        <input type="hidden" name="order_id" value="<?= (int)$order->id ?>">
                                        <select name="status" class="order-status-select">
                                            <option value="processing" <?= $order->order_status === 'processing' ? 'selected' : '' ?>>Processing</option>
                                            <option value="shipped" <?= $order->order_status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="delivered" <?= $order->order_status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                            <option value="cancelled" <?= $order->order_status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="returned" <?= $order->order_status === 'returned' ? 'selected' : '' ?>>Returned</option>
                                        </select>
                                        <button class="btn btn-primary" type="submit">
                                            Update
                                        </button>
                                    </form>

                                    <a href="/orderdetails?id=<?= (int)$order->id ?>" class="btn btn-secondary" title="View Order Details">
                                        View
                                    </a>

                                    <?php if ($order->order_status === 'cancelled'): ?>
                                        <button type="button" class="btn btn-danger openDeleteOrderModal" data-order-id="<?= (int)$order->id ?>" data-order-number="#ORD-<?= (int)$order->id ?>" title="Delete Cancelled Order">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px 16px;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                <i class="ph-bold ph-clipboard-text" style="font-size: 36px; color: var(--neo-gray-muted);"></i>
                                <span style="font-size: 16px; font-weight: 800; color: var(--neo-black);">No orders found</span>
                                <span style="font-size: 13px; color: var(--neo-gray-muted); font-weight: 600;">
                                    There are currently no orders in the <strong><?= htmlspecialchars(str_replace('_', ' ', $filter)) ?></strong> category.
                                </span>
                                <?php if ($filter !== 'all'): ?>
                                    <a href="/adminorders?filter=all" class="btn btn-primary" style="margin-top: 8px; font-size: 12px; padding: 6px 14px;">
                                        View All Orders
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Order Confirmation Modal -->
<div class="modal-overlay" id="deleteOrderOverlay"></div>

<div class="confirm-modal" id="deleteOrderModal">
    <div class="modal-header">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="ph-bold ph-warning-circle" style="font-size: 22px; color: var(--neo-pink);"></i>
            <h3>Delete Cancelled Order</h3>
        </div>
        <span class="close-modal" id="closeDeleteOrder">&times;</span>
    </div>
    <div class="confirm-modal-body">
        <p>Are you sure you want to permanently delete cancelled order <span class="confirm-book-badge" id="deleteOrderNumber"></span>?</p>
        <div class="confirm-modal-warning-box">
            <i class="ph-bold ph-info"></i>
            <p>This action will permanently purge this cancelled order record from database history. This action cannot be reversed.</p>
        </div>
    </div>
    <form action="/admin/handlers/deleteOrder.php" method="POST" class="ajax-form" id="deleteOrderForm">
        <input type="hidden" name="order_id" id="deleteOrderIdField">
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" id="cancelDeleteOrderBtn">Cancel</button>
            <button type="submit" class="btn btn-danger" id="confirmDeleteOrderBtn">Delete Order</button>
        </div>
    </form>
</div>

<script>
    const deleteOrderModal = document.getElementById('deleteOrderModal');
    const deleteOrderOverlay = document.getElementById('deleteOrderOverlay');
    const closeDeleteOrderBtn = document.getElementById('closeDeleteOrder');
    const cancelDeleteOrderBtn = document.getElementById('cancelDeleteOrderBtn');
    const deleteOrderIdField = document.getElementById('deleteOrderIdField');
    const deleteOrderNumber = document.getElementById('deleteOrderNumber');

    document.querySelectorAll('.openDeleteOrderModal').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-order-id');
            const num = this.getAttribute('data-order-number') || `#ORD-${id}`;
            if (deleteOrderIdField) deleteOrderIdField.value = id;
            if (deleteOrderNumber) deleteOrderNumber.innerText = num;
            if (deleteOrderModal) deleteOrderModal.classList.add('active');
            if (deleteOrderOverlay) deleteOrderOverlay.classList.add('active');
        });
    });

    function closeDeleteOrderModal() {
        if (deleteOrderModal) deleteOrderModal.classList.remove('active');
        if (deleteOrderOverlay) deleteOrderOverlay.classList.remove('active');
    }

    if (closeDeleteOrderBtn) closeDeleteOrderBtn.addEventListener('click', closeDeleteOrderModal);
    if (cancelDeleteOrderBtn) cancelDeleteOrderBtn.addEventListener('click', closeDeleteOrderModal);
    if (deleteOrderOverlay) deleteOrderOverlay.addEventListener('click', closeDeleteOrderModal);
</script>
<script src="/assests/js/admin.js"></script>
</body>
</html>