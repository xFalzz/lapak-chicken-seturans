<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$inOrders = strpos($_SERVER['PHP_SELF'], '/orders/') !== false;
?>
<aside class="sidebar kasir-sidebar" data-sidebar>
    <a class="sidebar-brand" href="<?= base_url('kasir/index.php') ?>">
        <img src="<?= base_url('img/Logo.jpeg') ?>" alt="Logo" class="brand-icon" style="object-fit:cover;width:32px;height:32px;border-radius:8px;">
        Kasir
    </a>

    <div class="sidebar-section">Menu</div>
    <a class="sidebar-link <?= ($currentPage === 'index.php' && !$inOrders) ? 'active' : '' ?>" href="<?= base_url('kasir/index.php') ?>">
        <i class="fa-solid fa-cash-register"></i> Antrian
    </a>
    <a class="sidebar-link <?= $currentPage === 'pos.php' ? 'active' : '' ?>" href="<?= base_url('kasir/pos.php') ?>">
        <i class="fa-solid fa-plus-circle"></i> Buat Pesanan (POS)
    </a>
    <a class="sidebar-link <?= $currentPage === 'dapur.php' ? 'active' : '' ?>" href="<?= base_url('kasir/dapur.php') ?>">
        <i class="fa-solid fa-kitchen-set"></i> Dapur (KDS)
    </a>
    <a class="sidebar-link <?= $inOrders ? 'active' : '' ?>" href="<?= base_url('admin/orders/index.php') ?>">
        <i class="fa-solid fa-list-check"></i> Semua Pesanan
    </a>

    <div class="sidebar-divider"></div>
    <a class="sidebar-link" href="<?= base_url('customer/menu.php') ?>">
        <i class="fa-solid fa-bag-shopping"></i> Mode Customer
    </a>

    <div class="sidebar-footer">
        <a href="<?= base_url('index.php') ?>">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Website
        </a>
    </div>
</aside>
