<?php
$page = isset($page) && is_string($page) ? strtolower($page) : 'dashboard';

$pageTitles = [
    'dashboard' => 'Dashboard',
    'products' => 'Products',
    'orders' => 'Orders',
    'customers' => 'Customers',
];

$pageTitle = $pageTitles[$page] ?? 'Dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | SweetTrack</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="index.php">SweetTrack <span aria-hidden="true">🧁</span></a>

            <nav class="main-navigation" aria-label="Main navigation">
                <a class="nav-link<?= $page === 'dashboard' ? ' active' : '' ?>" href="index.php"<?= $page === 'dashboard' ? ' aria-current="page"' : '' ?>>Dashboard</a>
                <a class="nav-link<?= $page === 'products' ? ' active' : '' ?>" href="products.php"<?= $page === 'products' ? ' aria-current="page"' : '' ?>>Products</a>
                <a class="nav-link<?= $page === 'orders' ? ' active' : '' ?>" href="orders.php"<?= $page === 'orders' ? ' aria-current="page"' : '' ?>>Orders</a>
                <a class="nav-link<?= $page === 'customers' ? ' active' : '' ?>" href="customers.php"<?= $page === 'customers' ? ' aria-current="page"' : '' ?>>Customers</a>
            </nav>

            <p class="sidebar-note">Your bakery, at a glance.</p>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div>
                    <p class="eyebrow">SweetTrack Lite</p>
                    <h1><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
                </div>
                <span class="workspace-label">Bakery workspace</span>
            </header>
