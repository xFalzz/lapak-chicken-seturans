<?php
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$cartCount = 0;
try {
    if (!str_starts_with($_SERVER['SCRIPT_NAME'] ?? '', '/lapak-chicken-seturan/api/')) {
        $cartCount = get_cart(db())['count'] ?? 0;
    }
} catch (Throwable $e) {
    $cartCount = 0;
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/customer.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/kasir.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/dapur.css') ?>">
    <script>window.APP = { baseUrl: "<?= BASE_URL ?>", csrf: "<?= csrf_token() ?>" };</script>
</head>
<body class="<?= e($bodyClass) ?>">
<header class="topbar">
    <a class="brand" href="<?= base_url('index.php') ?>"><span>LC</span> Lapak Chicken</a>
    <button class="icon-btn mobile-menu" type="button" data-toggle-sidebar aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    <nav class="topnav">
        <?php if (isset($_SESSION['branch_name'])): ?>
            <span class="branch-pill"><i class="fa-solid fa-location-dot"></i><?= e($_SESSION['branch_name']) ?></span>
        <?php endif; ?>
        <a href="<?= base_url('customer/menu.php') ?>">Menu</a>
        <a class="cart-link" href="<?= base_url('customer/cart.php') ?>"><i class="fa-solid fa-cart-shopping"></i><span data-cart-count><?= (int) $cartCount ?></span></a>
        <?php if (is_logged_in()): ?>
            <span class="role-pill"><?= e(user_role()) ?></span>
            <button class="btn btn-ghost" type="button" data-logout>Logout</button>
        <?php else: ?>
            <a class="btn btn-outline" href="<?= base_url('customer/login.php') ?>">Login</a>
        <?php endif; ?>
    </nav>
</header>
<main>
<?php if ($msg = get_flash('success')): ?><div class="toast server-toast success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = get_flash('error')): ?><div class="toast server-toast error"><?= e($msg) ?></div><?php endif; ?>
