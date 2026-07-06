<?php
// includes/footer.php
?>
</main>

<footer class="footer" style="background:#F9FAFB;border-top:1px solid var(--outline-variant);color:var(--on-surface);padding:60px 40px 30px;">
    <div class="footer-inner" style="max-width:var(--container-max);margin:0 auto;display:grid;grid-template-columns:2fr 1fr 1fr 1.5fr;gap:40px;">
        <!-- Brand -->
        <div class="footer-brand">
            <div style="font-size:1.4rem;font-weight:800;color:var(--on-surface);display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <span class="brand-icon" style="background:var(--primary-container);color:var(--on-primary-container);width:34px;height:34px;font-size:0.85rem;display:grid;place-items:center;border-radius:8px;">LC</span>
                Lapak Chicken
            </div>
            <p style="color:var(--secondary);font-size:0.95rem;line-height:1.6;max-width:300px;">Pelopor Ayam Geprek paling juicy di Yogyakarta. Lezat, Kenyang, Hemat. Melayani dine-in, takeaway, dan delivery.</p>
            
            <!-- Social Media -->
            <div style="display:flex;gap:12px;margin-top:20px;">
                <a href="#" style="width:36px;height:36px;background:var(--on-surface);color:var(--surface);border-radius:50%;display:grid;place-items:center;font-size:0.9rem;" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" style="width:36px;height:36px;background:var(--on-surface);color:var(--surface);border-radius:50%;display:grid;place-items:center;font-size:0.9rem;" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" style="width:36px;height:36px;background:var(--on-surface);color:var(--surface);border-radius:50%;display:grid;place-items:center;font-size:0.9rem;" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                <a href="#" style="width:36px;height:36px;background:var(--on-surface);color:var(--surface);border-radius:50%;display:grid;place-items:center;font-size:0.9rem;" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
            </div>
        </div>

        <!-- Menu -->
        <div class="footer-col">
            <h5 style="font-size:1rem;margin-bottom:20px;font-weight:700;">Menu</h5>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <a href="<?= base_url('index.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Beranda</a>
                <a href="<?= base_url('customer/menu.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Lihat Menu</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= base_url('customer/orders.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Riwayat Pesanan</a>
                    <a href="<?= base_url('customer/profile.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Profil Saya</a>
                <?php else: ?>
                    <a href="<?= base_url('customer/login.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Masuk</a>
                    <a href="<?= base_url('customer/register.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Daftar</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bantuan -->
        <div class="footer-col">
            <h5 style="font-size:1rem;margin-bottom:20px;font-weight:700;">Bantuan</h5>
            <div style="display:flex;flex-direction:column;gap:12px;">
                <a href="<?= base_url('customer/help.php') ?>" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Pusat Bantuan</a>
                <a href="#" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Kebijakan Privasi</a>
                <a href="#" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Syarat &amp; Ketentuan</a>
                <a href="#" style="color:var(--secondary);font-size:0.95rem;font-weight:500;">Hubungi Kami</a>
            </div>
        </div>

        <!-- Hubungi Kami -->
        <div class="footer-col">
            <h5 style="font-size:1rem;margin-bottom:20px;font-weight:700;">Hubungi Kami</h5>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div style="display:flex;align-items:flex-start;gap:12px;color:var(--secondary);font-size:0.95rem;">
                    <i class="fa-solid fa-phone" style="margin-top:4px;color:var(--on-surface);"></i>
                    <span>+62 821-2111-2143</span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:12px;color:var(--secondary);font-size:0.95rem;">
                    <i class="fa-regular fa-envelope" style="margin-top:4px;color:var(--on-surface);"></i>
                    <span>cs@lapak-chicken.com</span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:12px;color:var(--secondary);font-size:0.95rem;">
                    <i class="fa-solid fa-location-dot" style="margin-top:4px;color:var(--on-surface);"></i>
                    <span>Jl. Seturan Raya No.12, Caturtunggal, Depok, Sleman, Yogyakarta</span>
                </div>
            </div>
            
            <?php if (is_logged_in()): ?>
                <div style="margin-top:24px;display:flex;gap:12px;">
                    <?php $role = user_role(); ?>
                    <?php if ($role === 'admin'): ?>
                        <a href="<?= base_url('admin/index.php') ?>" style="color:var(--on-surface);font-weight:700;font-size:0.9rem;border-bottom:1px solid var(--on-surface);"><i class="fa-solid fa-gear"></i> Portal Admin</a>
                    <?php endif; ?>
                    <?php if (in_array($role, ['admin', 'kasir'])): ?>
                        <a href="<?= base_url('kasir/index.php') ?>" style="color:var(--on-surface);font-weight:700;font-size:0.9rem;border-bottom:1px solid var(--on-surface);"><i class="fa-solid fa-cash-register"></i> Portal Kasir</a>
                    <?php endif; ?>
                    <?php if (in_array($role, ['admin', 'dapur'])): ?>
                        <a href="<?= base_url('dapur/index.php') ?>" style="color:var(--on-surface);font-weight:700;font-size:0.9rem;border-bottom:1px solid var(--on-surface);"><i class="fa-solid fa-fire-burner"></i> Portal Dapur</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer-bottom" style="max-width:var(--container-max);margin:40px auto 0;padding-top:24px;border-top:1px solid var(--outline-variant);display:flex;justify-content:space-between;align-items:center;font-size:0.85rem;color:var(--secondary);">
        <p>&copy; 2026 Lapak Chicken Seturan. Hak Cipta Dilindungi Undang-Undang.</p>
        <p>Made with ❤️ by Kelompok 4</p>
    </div>
</footer>

<div id="toast-root"></div>
<script src="<?= base_url('assets/js/main.js') ?>?v=3.0"></script>

<script>
// === Header scroll effect ===
(function() {
    const header = document.getElementById('mainTopbar');
    if (!header) return;
    function onScroll() {
        if (window.scrollY > 10) {
            header.style.boxShadow = 'var(--shadow-sm)';
        } else {
            header.style.boxShadow = 'none';
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();

// === Mobile menu button visibility ===
(function() {
    const btn = document.getElementById('mobileMenuBtn');
    if (btn) {
        function checkWidth() {
            btn.style.display = window.innerWidth < 768 ? '' : 'none';
        }
        checkWidth();
        window.addEventListener('resize', checkWidth, { passive: true });
    }
})();

// === User Dropdown Toggle ===
(function() {
    const btn = document.getElementById('userDropdownBtn');
    const menu = document.getElementById('userDropdownMenu');
    if (!btn || !menu) return;

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = menu.style.display === 'block';
        menu.style.display = isOpen ? 'none' : 'block';
    });

    // Hover effect for dropdown items
    menu.querySelectorAll('a, button').forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.background = 'var(--surface-container-low)';
        });
        item.addEventListener('mouseleave', () => {
            item.style.background = 'transparent';
        });
    });

    // Close on click outside
    document.addEventListener('click', () => {
        menu.style.display = 'none';
    });
    menu.addEventListener('click', (e) => e.stopPropagation());
})();
</script>
</body>
</html>
