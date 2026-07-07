<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="sidebar admin-sidebar" data-sidebar>
    <a class="sidebar-brand" href="<?= base_url('admin/index.php') ?>">
        <span class="brand-icon">LC</span>
        Admin Panel
    </a>

    <div class="sidebar-section">Utama</div>
    <a class="sidebar-link <?= $currentDir === 'admin' && $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= base_url('admin/index.php') ?>">
        <i class="fa-solid fa-chart-line"></i> Dashboard
    </a>
    <a class="sidebar-link <?= $currentDir === 'orders' ? 'active' : '' ?>" href="<?= base_url('admin/orders/index.php') ?>">
        <i class="fa-solid fa-receipt"></i> Pesanan
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Konten</div>
    <a class="sidebar-link <?= $currentDir === 'menus' ? 'active' : '' ?>" href="<?= base_url('admin/menus/index.php') ?>">
        <i class="fa-solid fa-utensils"></i> Menu
    </a>
    <a class="sidebar-link <?= $currentDir === 'categories' ? 'active' : '' ?>" href="<?= base_url('admin/categories/index.php') ?>">
        <i class="fa-solid fa-tags"></i> Kategori
    </a>
    <a class="sidebar-link <?= $currentDir === 'sauces' ? 'active' : '' ?>" href="<?= base_url('admin/sauces/index.php') ?>">
        <i class="fa-solid fa-pepper-hot"></i> Saus
    </a>
    <a class="sidebar-link <?= $currentDir === 'banners' ? 'active' : '' ?>" href="<?= base_url('admin/banners/index.php') ?>">
        <i class="fa-solid fa-images"></i> Banner
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Operasional</div>
    <a class="sidebar-link <?= $currentDir === 'branches' ? 'active' : '' ?>" href="<?= base_url('admin/branches/index.php') ?>">
        <i class="fa-solid fa-store"></i> Cabang
    </a>
    <a class="sidebar-link <?= $currentDir === 'users' ? 'active' : '' ?>" href="<?= base_url('admin/users/index.php') ?>">
        <i class="fa-solid fa-users-gear"></i> Staff
    </a>
    <a class="sidebar-link <?= $currentDir === 'operating-hours' ? 'active' : '' ?>" href="<?= base_url('admin/operating-hours/index.php') ?>">
        <i class="fa-solid fa-clock"></i> Jam Operasional
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Laporan</div>
    <a class="sidebar-link <?= $currentDir === 'reports' ? 'active' : '' ?>" href="<?= base_url('admin/reports/index.php') ?>">
        <i class="fa-solid fa-file-lines"></i> Reports
    </a>
    <a class="sidebar-link <?= $currentDir === 'settings' ? 'active' : '' ?>" href="<?= base_url('admin/settings/index.php') ?>">
        <i class="fa-solid fa-sliders"></i> Settings
    </a>

    <div class="sidebar-footer">
        <a href="<?= base_url('index.php') ?>">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Website
        </a>
    </div>
</aside>
