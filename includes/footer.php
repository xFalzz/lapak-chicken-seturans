</main>
<footer class="footer">
    <div>
        <div class="brand" style="margin-bottom: 16px;"><span>LC</span> Lapak Chicken</div>
        <p>Ayam crispy, saus pilihan, dan layanan cepat dari Seturan.<br>Melayani dine-in, takeaway, dan delivery.</p>
        <p style="margin-top: 16px; font-size: 0.85rem; color: rgba(255,255,255,0.4);">&copy; <?= date('Y') ?> Lapak Chicken Seturan.</p>
    </div>
    <div class="footer-links">
        <a href="<?= base_url('index.php') ?>">Beranda</a>
        <a href="<?= base_url('customer/menu.php') ?>">Lihat Menu</a>
        <?php if (is_logged_in()): ?>
            <?php $role = user_role(); ?>
            <?php if ($role === 'admin'): ?>
                <a href="<?= base_url('admin/index.php') ?>" style="color: var(--primary);">Portal Admin</a>
            <?php endif; ?>
            <?php if (in_array($role, ['admin', 'kasir'])): ?>
                <a href="<?= base_url('kasir/index.php') ?>" style="color: var(--primary);">Portal Kasir</a>
            <?php endif; ?>
            <?php if (in_array($role, ['admin', 'dapur'])): ?>
                <a href="<?= base_url('dapur/index.php') ?>" style="color: var(--primary);">Portal Dapur</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= base_url('customer/login.php') ?>">Login</a>
        <?php endif; ?>
    </div>
</footer>
<div id="toast-root"></div>
<script src="<?= base_url('assets/js/main.js') ?>?v=1.1"></script>
</body>
</html>
