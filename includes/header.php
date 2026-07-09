<?php
$pageTitle = $pageTitle ?? APP_NAME;
$bodyClass = $bodyClass ?? '';
$cartCount = 0;
$isCustomerPage = !in_array($bodyClass, ['admin-layout', 'kasir-layout', 'dapur-layout'], true);

try {
    if ($isCustomerPage && !str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/')) {
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
    <meta name="description" content="Lapak Chicken Seturan - Ayam crispy premium, saus pilihan, dan checkout cepat untuk dine-in, takeaway, atau delivery di Yogyakarta.">
    <meta name="theme-color" content="#FFFD00">
    <title><?= ($pageTitle === APP_NAME) ? e(APP_NAME) : e($pageTitle) . ' | ' . e(APP_NAME) ?></title>

    <link rel="icon" type="image/jpeg" href="<?= base_url('img/logo2.jpeg') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/main.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>?v=<?= time() ?>">

    <?php if ($isCustomerPage): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/customer.css') ?>?v=<?= time() ?>">
    <?php elseif ($bodyClass === 'admin-layout'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>?v=<?= time() ?>">
    <?php elseif ($bodyClass === 'kasir-layout'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/kasir.css') ?>?v=<?= time() ?>">
        <?php if (basename($_SERVER['PHP_SELF']) === 'dapur.php'): ?>
            <link rel="stylesheet" href="<?= base_url('assets/css/dapur.css') ?>?v=<?= time() ?>">
        <?php endif; ?>
    <?php elseif ($bodyClass === 'dapur-layout'): ?>
        <link rel="stylesheet" href="<?= base_url('assets/css/dapur.css') ?>?v=<?= time() ?>">
    <?php endif; ?>

    <script>window.APP = { baseUrl: "<?= BASE_URL ?>", csrf: "<?= csrf_token() ?>" };</script>
</head>
<body class="<?= e($bodyClass) ?>">

<header class="topbar" id="mainTopbar">
    
    <a class="brand topbar-brand" href="<?= base_url('index.php') ?>">
        <img src="<?= base_url('img/Logo.jpeg') ?>" alt="Logo Lapak Chicken" class="brand-icon" style="object-fit:cover;width:36px;height:36px;border-radius:8px;">
        Lapak <span style="background:var(--primary-container);color:var(--on-primary-container);padding:2px 8px;border-radius:6px;margin-left:2px;">Chicken</span>
    </a>

    <?php if ($isCustomerPage): ?>
        
        <button class="icon-btn mobile-menu" id="mobileMenuBtn" type="button" data-toggle-drawer aria-label="Buka Menu">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        <nav class="topnav" id="mainNav">
            <div class="topnav-links">
                <a href="<?= base_url('index.php') ?>" <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'class="active"' : '' ?>>Beranda</a>
                <a href="<?= base_url('customer/menu.php') ?>" <?= basename($_SERVER['PHP_SELF']) === 'menu.php' ? 'class="active"' : '' ?>>Menu</a>
                <a href="<?= base_url('customer/help.php') ?>" <?= basename($_SERVER['PHP_SELF']) === 'help.php' ? 'class="active"' : '' ?>>Bantuan</a>
            </div>

            <div class="topnav-actions">
                
                <a href="<?= base_url('customer/notifications.php') ?>" style="color:var(--on-surface);font-size:1.2rem;position:relative;" aria-label="Notifikasi">
                    <i class="fa-regular fa-bell"></i>
                    <span style="position:absolute;top:-2px;right:-2px;width:8px;height:8px;background:var(--error);border-radius:50%;border:1.5px solid var(--surface);"></span>
                </a>

                <a href="<?= base_url('customer/cart.php') ?>" style="color:var(--on-surface);font-size:1.2rem;position:relative;" aria-label="Keranjang">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span style="position:absolute;top:-4px;right:-8px;background:var(--primary-container);color:var(--on-primary-container);font-size:0.65rem;font-weight:800;padding:2px 6px;border-radius:10px;border:1.5px solid var(--surface);line-height:1;" data-cart-count><?= (int) $cartCount ?></span>
                </a>

                <?php if (is_logged_in()): ?>
                    
                    <div class="user-dropdown-wrapper" style="position:relative;margin-left:12px;">
                        <button type="button" id="userDropdownBtn" style="display:flex;align-items:center;gap:12px;cursor:pointer;background:none;border:none;padding:0;">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode(current_user()['name']) ?>&background=FFFD00&color=111&bold=true" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;">
                            <div style="display:flex;flex-direction:column;text-align:left;">
                                <span style="font-size:0.85rem;font-weight:700;line-height:1.2;color:var(--on-surface);"><?= e(current_user()['name']) ?></span>
                                <span style="font-size:0.75rem;color:var(--secondary);line-height:1.2;"><?= e(user_role()) ?></span>
                            </div>
                            <i class="fa-solid fa-chevron-down" style="font-size:0.7rem;color:var(--secondary);"></i>
                        </button>

                        <div class="user-dropdown-menu" id="userDropdownMenu" style="display:none;position:absolute;top:calc(100% + 12px);right:0;background:var(--surface);border:1px solid var(--outline-variant);border-radius:16px;box-shadow:0 12px 32px rgba(0,0,0,0.12);min-width:240px;z-index:999;overflow:hidden;">
                            <div style="padding:16px 20px;border-bottom:1px solid var(--outline-variant);display:flex;align-items:center;gap:12px;">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode(current_user()['name']) ?>&background=FFFD00&color=111&bold=true" alt="Avatar" style="width:40px;height:40px;border-radius:50%;">
                                <div>
                                    <strong style="display:block;font-size:0.9rem;"><?= e(current_user()['name']) ?></strong>
                                    <span style="font-size:0.8rem;color:var(--secondary);"><?= e(current_user()['email']) ?></span>
                                </div>
                            </div>
                            <div style="padding:8px;">
                                <a href="<?= base_url('customer/profile.php') ?>" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--on-surface);font-size:0.9rem;font-weight:600;">
                                    <i class="fa-solid fa-user" style="width:20px;text-align:center;color:var(--secondary);"></i> Profil Saya
                                </a>
                                <a href="<?= base_url('customer/orders.php') ?>" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--on-surface);font-size:0.9rem;font-weight:600;">
                                    <i class="fa-solid fa-receipt" style="width:20px;text-align:center;color:var(--secondary);"></i> Riwayat Pesanan
                                </a>
                                <a href="<?= base_url('customer/notifications.php') ?>" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--on-surface);font-size:0.9rem;font-weight:600;">
                                    <i class="fa-solid fa-bell" style="width:20px;text-align:center;color:var(--secondary);"></i> Notifikasi
                                </a>
                                <a href="<?= base_url('customer/help.php') ?>" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--on-surface);font-size:0.9rem;font-weight:600;">
                                    <i class="fa-solid fa-circle-question" style="width:20px;text-align:center;color:var(--secondary);"></i> Pusat Bantuan
                                </a>
                                <?php $role = user_role(); ?>
                                <?php if ($role === 'admin'): ?>
                                    <a href="<?= base_url('admin/index.php') ?>" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:#B29500;font-size:0.9rem;font-weight:600;">
                                        <i class="fa-solid fa-gear" style="width:20px;text-align:center;"></i> Portal Admin
                                    </a>
                                <?php endif; ?>
                                <?php if (in_array($role, ['admin', 'kasir'])): ?>
                                    <a href="<?= base_url('kasir/index.php') ?>" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:#B29500;font-size:0.9rem;font-weight:600;">
                                        <i class="fa-solid fa-cash-register" style="width:20px;text-align:center;"></i> Portal Kasir
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div style="padding:8px;border-top:1px solid var(--outline-variant);">
                                <button type="button" data-logout style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--error);font-size:0.9rem;font-weight:600;background:none;border:none;cursor:pointer;width:100%;">
                                    <i class="fa-solid fa-right-from-bracket" style="width:20px;text-align:center;"></i> Keluar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="btn btn-primary" href="<?= base_url('customer/login.php') ?>" style="border-radius:99px;padding:8px 24px;margin-left:12px;font-weight:700;">
                        Masuk
                    </a>
                <?php endif; ?>
            </div>
        </nav>

    <?php else: ?>
        
        <button class="icon-btn sidebar-toggle-btn" type="button" data-toggle-sidebar aria-label="Menu Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <nav class="topnav" style="gap:16px; margin-left: auto;">
            <?php if (is_logged_in()): ?>
                <div style="display:flex;align-items:center;gap:16px;">
                    <span class="role-pill" style="background:var(--primary-container);color:var(--on-primary-container);font-weight:700;padding:6px 16px;border-radius:99px;font-size:0.8rem;text-transform:uppercase;letter-spacing:0.05em;">
                        <i class="fa-solid fa-shield-halved" style="margin-right:4px;"></i>
                        <?= e(user_role()) ?>
                    </span>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode(current_user()['name']) ?>&background=111111&color=FFFFFF&bold=true&size=36" alt="Avatar" style="width:34px;height:34px;border-radius:50%;border:2px solid var(--outline-variant);">
                        <div style="line-height:1.2;">
                            <div style="font-size:0.85rem;font-weight:700;color:var(--on-surface);"><?= e(current_user()['name']) ?></div>
                            <div style="font-size:0.7rem;color:var(--secondary);">Online</div>
                        </div>
                    </div>
                    <button class="btn btn-outline" type="button" data-logout style="min-height:36px;padding:6px 16px;font-size:0.85rem;border-radius:10px;gap:6px;">
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </button>
                </div>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</header>

<?php if ($isCustomerPage): ?>

<nav class="mobile-drawer" id="mobileDrawer">
    <div class="mobile-drawer-overlay" data-toggle-drawer></div>
    <div class="mobile-drawer-content">
        <div class="drawer-header">
            <div class="brand" style="font-size:1.1rem;">
                <img src="<?= base_url('img/Logo.jpeg') ?>" alt="Logo Lapak Chicken" class="brand-icon" style="object-fit:cover;width:30px;height:30px;border-radius:8px;">
                Lapak Chicken
            </div>
            <button class="icon-btn" type="button" data-toggle-drawer aria-label="Tutup" style="width:36px;height:36px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <?php if (isset($_SESSION['branch_name'])): ?>
            <div style="padding: 10px 14px; background: var(--brand-yellow-light); border-radius: var(--radius-md); font-weight: 700; font-size: 0.88rem; color: var(--on-primary-container); margin-bottom: 8px; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-location-dot" style="color:#000000;"></i>
                <?= e($_SESSION['branch_name']) ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('index.php') ?>"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="<?= base_url('customer/menu.php') ?>"><i class="fa-solid fa-utensils"></i> Lihat Menu</a>
        <a href="<?= base_url('customer/cart.php') ?>">
            <i class="fa-solid fa-bag-shopping"></i> Keranjang
            <?php if ($cartCount > 0): ?>
                <span class="badge badge-red" style="margin-left:auto;font-size:0.7rem;"><?= (int)$cartCount ?></span>
            <?php endif; ?>
        </a>

        <hr class="drawer-divider">

        <?php if (is_logged_in()): ?>
            <a href="<?= base_url('customer/profile.php') ?>"><i class="fa-solid fa-user"></i> Profil Saya</a>
            <a href="<?= base_url('customer/orders.php') ?>"><i class="fa-solid fa-receipt"></i> Riwayat Pesanan</a>
            <a href="<?= base_url('customer/notifications.php') ?>"><i class="fa-solid fa-bell"></i> Notifikasi</a>
            <a href="<?= base_url('customer/help.php') ?>"><i class="fa-solid fa-circle-question"></i> Pusat Bantuan</a>
            <?php $role = user_role(); ?>
            <?php if ($role === 'admin'): ?>
                <a href="<?= base_url('admin/index.php') ?>" style="color:var(--on-surface);font-weight:700;"><i class="fa-solid fa-gear"></i> Portal Admin</a>
            <?php endif; ?>
            <?php if (in_array($role, ['admin', 'kasir'])): ?>
                <a href="<?= base_url('kasir/index.php') ?>" style="color:var(--on-surface);font-weight:700;"><i class="fa-solid fa-cash-register"></i> Portal Kasir</a>
            <?php endif; ?>
            <button class="btn btn-outline" style="margin-top:auto; width:100%; justify-content:center;" type="button" data-logout>
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </button>
        <?php else: ?>
            <a class="btn btn-primary" href="<?= base_url('customer/login.php') ?>" style="margin-top:auto; justify-content:center;">
                <i class="fa-solid fa-user"></i> Masuk / Daftar
            </a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

<main>
