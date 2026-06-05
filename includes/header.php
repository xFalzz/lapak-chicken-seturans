<?php
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$cartCount = 0;
$isCustomerPage = !in_array($bodyClass, ['admin-layout', 'kasir-layout', 'dapur-layout'], true);

try {
    if ($isCustomerPage && !str_starts_with($_SERVER['SCRIPT_NAME'] ?? '', '/lapak-chicken-seturan/api/')) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="description" content="Lapak Chicken Seturan - Ayam crispy panas, saus pilihan, dan checkout cepat untuk dine-in, takeaway, atau delivery.">
    <meta name="theme-color" content="#FFC107">
    <title><?= e($pageTitle) ?> - <?= APP_NAME ?></title>
    
    <!-- Favicon placeholder (would use real ones in prod) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍗</text></svg>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>?v=1.3">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>?v=1.3">
    
    <?php if ($isCustomerPage): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/customer.css') ?>?v=1.3">
    <?php elseif ($bodyClass === 'admin-layout'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>?v=1.3">
    <?php elseif ($bodyClass === 'kasir-layout'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/kasir.css') ?>?v=1.3">
    <?php elseif ($bodyClass === 'dapur-layout'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/dapur.css') ?>?v=1.3">
    <?php endif; ?>
    
    <script>window.APP = { baseUrl: "<?= BASE_URL ?>", csrf: "<?= csrf_token() ?>" };</script>
</head>
<body class="<?= e($bodyClass) ?>">
<header class="topbar">
    <a class="brand" href="<?= base_url('index.php') ?>"><span>LC</span> Lapak Chicken</a>
    
    <?php if ($isCustomerPage): ?>
        <button class="icon-btn mobile-menu" type="button" data-toggle-drawer aria-label="Menu Mobile"><i class="fa-solid fa-bars"></i></button>
    <?php else: ?>
        <button class="icon-btn mobile-menu" type="button" data-toggle-sidebar aria-label="Menu Admin"><i class="fa-solid fa-bars"></i></button>
    <?php endif; ?>
    
    <nav class="topnav">
        <?php if (isset($_SESSION['branch_name'])): ?>
            <span class="branch-pill"><i class="fa-solid fa-location-dot"></i><?= e($_SESSION['branch_name']) ?></span>
        <?php endif; ?>
        
        <?php if ($isCustomerPage): ?>
            <a href="<?= base_url('customer/menu.php') ?>">Menu</a>
        <?php endif; ?>
        
        <?php if ($isCustomerPage || !is_logged_in()): ?>
            <a class="cart-link" href="<?= base_url('customer/cart.php') ?>" aria-label="Keranjang"><i class="fa-solid fa-cart-shopping"></i><span data-cart-count><?= (int) $cartCount ?></span></a>
        <?php endif; ?>
        
        <?php if (is_logged_in()): ?>
            <span class="role-pill"><?= e(user_role()) ?></span>
            <button class="btn btn-outline btn-sm" type="button" data-logout>Logout</button>
        <?php else: ?>
            <a class="btn btn-primary" href="<?= base_url('customer/login.php') ?>">Login</a>
        <?php endif; ?>
    </nav>
</header>

<?php if ($isCustomerPage): ?>
<!-- Mobile Drawer -->
<nav class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-overlay" data-toggle-drawer></div>
    <div class="mobile-drawer-content">
        <div class="drawer-header">
            <h2>Menu</h2>
            <button class="icon-btn" type="button" data-toggle-drawer aria-label="Tutup"><i class="fa-solid fa-times"></i></button>
        </div>
        <?php if (isset($_SESSION['branch_name'])): ?>
            <div style="padding: 12px 16px; background: var(--primary-light); border-radius: var(--radius-sm); font-weight: 600; margin-bottom: 8px;">
                <i class="fa-solid fa-location-dot" style="margin-right:8px;"></i> <?= e($_SESSION['branch_name']) ?>
            </div>
        <?php endif; ?>
        <a href="<?= base_url('index.php') ?>"><i class="fa-solid fa-home"></i> Beranda</a>
        <a href="<?= base_url('customer/menu.php') ?>"><i class="fa-solid fa-utensils"></i> Lihat Menu</a>
        <a href="<?= base_url('customer/cart.php') ?>"><i class="fa-solid fa-cart-shopping"></i> Keranjang <span class="badge badge-red" style="margin-left:auto" data-cart-count><?= (int) $cartCount ?></span></a>
        <hr style="border:0; border-top:1px solid #eee; margin: 8px 0;">
        <?php if (is_logged_in()): ?>
            <a href="<?= base_url('customer/order-status.php') ?>"><i class="fa-solid fa-clock-rotate-left"></i> Pesanan Saya</a>
            <button class="btn btn-outline" type="button" data-logout style="margin-top:auto"><i class="fa-solid fa-sign-out-alt"></i> Logout</button>
        <?php else: ?>
            <a href="<?= base_url('customer/login.php') ?>" class="btn btn-primary" style="margin-top:auto"><i class="fa-solid fa-user"></i> Login / Register</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

<main>
