<?php
require_once __DIR__ . '/includes/functions.php';
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        flash('error', 'Token CSRF tidak valid');
        redirect(base_url('index.php'));
    }
    $branchId = (int) ($_POST['branch_id'] ?? 0);
    $stmt = $db->prepare('SELECT * FROM branches WHERE id = ?');
    $stmt->execute([$branchId]);
    $branch = $stmt->fetch();
    if ($branch) {
        $_SESSION['branch_id'] = (int) $branch['id'];
        $_SESSION['branch_name'] = $branch['name'];
        redirect(base_url('customer/menu.php'));
    }
}

$branches = branch_options($db);
if (empty($_SESSION['branch_id']) && !empty($branches)) {
    $_SESSION['branch_id'] = (int) $branches[0]['id'];
    $_SESSION['branch_name'] = $branches[0]['name'];
}
$selectedBranch = (int) ($_SESSION['branch_id'] ?? ($branches[0]['id'] ?? 0));

$popularMenus = $db->query('SELECT m.*, c.name category_name FROM menus m JOIN categories c ON c.id = m.category_id WHERE m.is_active = 1 LIMIT 4')->fetchAll();

$pageTitle = 'Beranda';
require __DIR__ . '/includes/header.php';
?>

<?php if (is_logged_in()): ?>
    
    <section class="hero-logged-in">
        <div class="container">
            <div class="hero-logged-in-inner">
                <div class="hero-logged-in-content">
                    <div class="hero-eyebrow">
                        Makan Kenyang Diskon 50%
                    </div>
                    <h1>Nikmati potongan harga spesial hingga 50%</h1>
                    <p>Khusus menu andalan hanya dengan login aplikasimu sekarang juga!</p>
                    <div class="hero-actions">
                        <a href="<?= base_url('customer/menu.php') ?>" class="btn btn-primary" style="border-radius:99px;padding:12px 32px;font-weight:700;">Pesan Sekarang</a>
                        <a href="#menu-pilihan" class="btn btn-outline" style="border-radius:99px;padding:12px 32px;color:white;border-color:white;font-weight:600;">Lihat Menu Pilihan</a>
                    </div>
                </div>
                <div class="hero-logged-in-image"></div>
            </div>
        </div>
    </section>

    <section class="section" id="menu-pilihan" style="padding-top:20px;">
        <div class="container">
            <h2 style="font-size:1.4rem;margin-bottom:20px;">Kategori Pilihan</h2>
            
            <div class="chip-row-icons" style="margin-bottom:48px;">
                <div class="chip-icon active">
                    <div class="chip-icon-circle"><i class="fa-solid fa-list"></i></div>
                    Semua
                </div>
                <div class="chip-icon">
                    <div class="chip-icon-circle"><i class="fa-solid fa-fire"></i></div>
                    Ayam Geprek
                </div>
                <div class="chip-icon">
                    <div class="chip-icon-circle"><i class="fa-solid fa-drumstick-bite"></i></div>
                    Ayam Crispy
                </div>
                <div class="chip-icon">
                    <div class="chip-icon-circle"><i class="fa-solid fa-box"></i></div>
                    Paket Hemat
                </div>
                <div class="chip-icon">
                    <div class="chip-icon-circle"><i class="fa-solid fa-glass-water"></i></div>
                    Minuman
                </div>
            </div>

            <div class="section-header-row" style="margin-bottom:24px;">
                <div>
                    <h2 style="font-size:1.4rem;">Menu Terpopuler</h2>
                    <p style="color:var(--secondary);font-size:0.95rem;margin-top:4px;">Pilihan terbaik yang selalu disukai pelanggan setia kami.</p>
                </div>
                <a href="<?= base_url('customer/menu.php') ?>" class="link-see-all">Lihat Semua <i class="fa-solid fa-chevron-right" style="font-size:0.8rem;"></i></a>
            </div>

            <div class="grid grid-4" style="margin-bottom:64px;">
                <?php foreach ($popularMenus as $menu): ?>
                    <article class="menu-card">
                        <div class="menu-img-wrap">
                            <?php if (!empty($menu['image_url'])): ?>
                                <img src="<?= e($menu['image_url']) ?>" alt="<?= e($menu['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:grid;place-items:center;font-size:3rem;color:var(--outline);">🍗</div>
                            <?php endif; ?>
                        </div>
                        <div class="menu-card-content">
                            <h3><?= e($menu['name']) ?></h3>
                            <div class="menu-rating">
                                <i class="fa-solid fa-star"></i> 4.8 <span>(120)</span>
                            </div>
                            <div class="menu-card-footer">
                                <span class="price"><?= format_rupiah((float) $menu['price']) ?></span>
                                <button class="btn-add-pill" title="Pesan sekarang" onclick="window.location.href='<?= base_url('customer/menu.php') ?>'">
                                    Pesan
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="bento-grid bento-promo">
                <div class="bento-card card-large">
                    <div class="card-large-content">
                        <h3 style="font-weight:800;">Makan Siang Bareng, Bayar Lebih Hemat.</h3>
                        <a href="<?= base_url('customer/menu.php') ?>" class="btn" style="background:#111;color:white;border-radius:99px;padding:10px 24px;">Lihat Paket Hemat</a>
                    </div>
                </div>
                <div class="bento-card card-yellow">
                    <div class="bento-icon-box" style="color:var(--primary-container);"><i class="fa-solid fa-bolt"></i></div>
                    <h4 style="font-size:1.1rem;margin-bottom:8px;font-weight:700;">Delivery Kilat</h4>
                    <p style="font-size:0.9rem;opacity:0.9;">Pesanan tiba sebelum 30 menit atau gratis ongkir!</p>
                </div>
                <div class="bento-card card-white">
                    <div class="bento-icon-box" style="color:var(--on-surface);"><i class="fa-solid fa-utensils"></i></div>
                    <h4 style="font-size:1.1rem;margin-bottom:8px;font-weight:700;">Makan di Tempat</h4>
                    <p style="font-size:0.9rem;color:var(--secondary);">Tempat nyaman dan estetik untuk nongkrong.</p>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>
    
    <section class="hero-tamu">
        <div class="container">
            <div class="hero-tamu-inner">
                <div class="hero-tamu-content">
                    <div class="hero-eyebrow" style="background:#FFF000; color:var(--on-surface); display:inline-block; font-weight:700;">
                        Kuliner Populer Seturan 🔥
                    </div>
                    <h1>Ricebox<br><span style="color:var(--primary-container);font-style:italic;">Terlezat</span> di Jogja.</h1>
                    <p style="font-size:1.05rem;">Nikmati sensasi ayam goreng renyah dengan sambal korek khas Lapak Chicken. Panas, pedas, dan langsung diantar ke depan pintu Anda.</p>
                    
                    <div style="display:flex;gap:16px;margin-bottom:24px;">
                        <a href="<?= base_url('customer/menu.php') ?>" class="btn btn-primary" style="border-radius:99px;padding:14px 32px;font-weight:800;font-size:1.05rem;">Pesan Sekarang</a>
                        <a href="<?= base_url('customer/menu.php') ?>" class="btn btn-outline" style="border-radius:99px;padding:14px 32px;font-weight:700;font-size:1.05rem;">Lihat Menu</a>
                    </div>
                </div>
                <div class="hero-tamu-image">
                    <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80" alt="Ayam Geprek" style="border-radius:32px;box-shadow:0 20px 40px rgba(0,0,0,0.1);">
                    
                    <div class="time-badge" style="position:absolute;bottom:30px;left:-30px;background:white;padding:12px 24px;border-radius:20px;display:flex;align-items:center;gap:16px;box-shadow:0 12px 24px rgba(0,0,0,0.08);">
                        <div style="width:48px;height:48px;background:#FFF000;border-radius:50%;display:grid;place-items:center;font-size:1.4rem;color:var(--on-surface);"><i class="fa-solid fa-stopwatch"></i></div>
                        <div>
                            <div style="font-size:0.85rem;color:var(--secondary);font-weight:600;margin-bottom:2px;">Waktu Bikin</div>
                            <div style="font-size:1.15rem;font-weight:800;color:var(--on-surface);">15-20 Menit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="kenapa-lapak-chicken">
        <div class="container">
            <div class="section-header-row" style="margin-bottom:32px;">
                <div>
                    <h2 style="font-size:2rem;font-weight:900;">Kenapa Lapak Chicken?</h2>
                    <p style="color:var(--secondary);font-size:1.05rem;margin-top:4px;">Bukan sekadar ayam goreng biasa, ini adalah pengalaman rasa.</p>
                </div>
                <a href="#" class="link-see-all" style="font-weight:700;color:var(--primary-container);">Lihat Cerita Kami <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <div class="bento-grid-tamu">
                
                <div class="bento-tamu-card bg-img" style="background-image: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.1) 100%), url('https://images.unsplash.com/photo-1577219491135-ce391730fb2c?auto=format&fit=crop&w=800&q=80'); grid-column: 1 / span 2;">
                    <div class="bento-tamu-content text-white" style="justify-content:flex-end;">
                        <h3 style="font-size:1.8rem;font-weight:800;margin-bottom:8px;">Bahan Segar Setiap Hari</h3>
                        <p style="font-size:0.95rem;opacity:0.9;max-width:80%;">Kami hanya menggunakan ayam pilihan yang diproses langsung hari ini untuk menjaga tekstur juicy dan rasa maksimal.</p>
                    </div>
                </div>

                <div class="bento-tamu-card bg-gray">
                    <div class="bento-icon-tamu"><i class="fa-solid fa-pepper-hot"></i></div>
                    <div class="bento-tamu-content" style="justify-content:flex-end;">
                        <h4 style="font-size:1.2rem;font-weight:800;margin-bottom:8px;">Sambal Korek Otentik</h4>
                        <p style="font-size:0.95rem;color:var(--secondary);">Ulekan cabai segar dengan bawang putih rahasia yang bikin ketagihan.</p>
                    </div>
                </div>

                <div class="bento-tamu-card bg-gray">
                    <div class="bento-icon-tamu"><i class="fa-solid fa-piggy-bank"></i></div>
                    <div class="bento-tamu-content" style="justify-content:flex-end;">
                        <h4 style="font-size:1.2rem;font-weight:800;margin-bottom:8px;">Harga Mahasiswa</h4>
                        <p style="font-size:0.95rem;color:var(--secondary);">Porsi kenyang yang tidak bikin kantong bolong, pas untuk anak Seturan.</p>
                    </div>
                </div>

                <div class="bento-tamu-card bg-yellow has-right-img" style="grid-column: 2 / span 2; overflow:hidden;">
                    <div class="bento-tamu-content" style="position:relative; z-index:2; justify-content:center; align-items:flex-start; max-width:60%;">
                        <h3 style="font-size:1.8rem;font-weight:800;color:var(--on-surface);margin-bottom:8px;">Varian Menu Lengkap</h3>
                        <p style="color:var(--on-surface);opacity:0.8;font-size:0.95rem;margin-bottom:20px;">Dari Original, Krispi, Chicken Katsu, Geprek Mozzarella, semua ada di sini.</p>
                        <a href="<?= base_url('customer/menu.php') ?>" class="btn-dark-pill" style="background:var(--on-surface);color:white;padding:12px 24px;border-radius:99px;font-weight:700;font-size:0.95rem;">Cek Selengkapnya</a>
                    </div>
                    <img src="https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=600&q=80" alt="Menu" class="bento-right-img" style="position:absolute;right:0;top:0;height:100%;width:50%;object-fit:cover;object-position:left;">
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
