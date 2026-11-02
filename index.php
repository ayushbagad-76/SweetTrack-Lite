<?php
require_once __DIR__ . "/config/db.php";

$page = "dashboard";
include "includes/header.php";

$products = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$orders   = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$sales    = $conn->query("SELECT IFNULL(SUM(total_price),0) AS s FROM orders")->fetch_assoc()['s'];
$avg      = $conn->query("SELECT IFNULL(AVG(total_price),0) AS a FROM orders")->fetch_assoc()['a'];

$best = $conn->query("
  SELECT p.name, SUM(o.quantity) AS sold
  FROM orders o JOIN products p ON o.product_id = p.product_id
  GROUP BY p.product_id ORDER BY sold DESC LIMIT 1
")->fetch_assoc();
?>
<div class="card-grid">
  <div class="card"><p>Total Products</p><h3><?= $products ?></h3></div>
  <div class="card"><p>Total Orders</p><h3><?= $orders ?></h3></div>
  <div class="card"><p>Total Sales</p><h3>₹<?= number_format($sales, 2) ?></h3></div>
  <div class="card"><p>Avg Order Value</p><h3>₹<?= number_format($avg, 2) ?></h3></div>
  <div class="card"><p>Best Seller</p><h3 style="font-size:18px"><?= $best ? htmlspecialchars($best['name']) . " ({$best['sold']})" : "-" ?></h3></div>
</div>
<?php include "includes/footer.php"; ?>
