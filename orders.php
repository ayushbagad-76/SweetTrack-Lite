<?php
require_once __DIR__ . '/config/db.php';

$page = 'orders';
include __DIR__ . '/includes/header.php';

$orders = $conn->query(
    'SELECT orders.order_id, customers.name AS customer_name,
            products.name AS product_name, orders.quantity,
            orders.total_price, orders.order_date
     FROM orders
     LEFT JOIN customers ON orders.customer_id = customers.customer_id
     LEFT JOIN products ON orders.product_id = products.product_id
     ORDER BY orders.order_date DESC, orders.order_id DESC'
);
?>

<p>Here are the recent orders for your bakery.</p>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer name</th>
                <th>Product name</th>
                <th>Quantity</th>
                <th>Total price</th>
                <th>Order date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders && $orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $order['order_id'] ?></td>
                        <td><?= htmlspecialchars($order['customer_name'] ?? 'Unknown customer', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($order['product_name'] ?? 'Unknown product', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $order['quantity'] ?></td>
                        <td><?= number_format((float) $order['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($order['order_date'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php elseif ($orders): ?>
                <tr>
                    <td colspan="6">No orders found.</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6">Orders could not be loaded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
