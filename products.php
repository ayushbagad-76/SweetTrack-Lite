<?php
require_once __DIR__ . '/config/db.php';

$page = 'products';
include __DIR__ . '/includes/header.php';

$products = $conn->query(
    'SELECT product_id, name, category, price, stock FROM products ORDER BY product_id ASC'
);
?>

<p>Here are the products currently in your bakery.</p>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $product['product_id'] ?></td>
                        <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format((float) $product['price'], 2) ?></td>
                        <td><?= (int) $product['stock'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php elseif ($products): ?>
                <tr>
                    <td colspan="5">No products found.</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="5">Products could not be loaded.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
