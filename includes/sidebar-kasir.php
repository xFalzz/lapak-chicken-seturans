<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar kasir-sidebar" data-sidebar>
    <a class="sidebar-brand" href="<?= base_url('kasir/index.php') ?>">
        <span class="brand-icon">LC</span>
        Kasir
    </a>

    <div class="sidebar-section">Menu</div>
    <a class="sidebar-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= base_url('kasir/index.php') ?>">
        <i class="fa-solid fa-cash-register"></i> Antrian
    </a>
    <a class="sidebar-link" href="<?= base_url('admin/orders/index.php') ?>">
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
