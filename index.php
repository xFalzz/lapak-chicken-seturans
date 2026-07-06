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
$selectedBranch = (int) ($_SESSION['branch_id'] ?? ($branches[0]['id'] ?? 0));

$popularMenus = $db->query('SELECT m.*, c.name category_name FROM menus m JOIN categories c ON c.id = m.category_id WHERE m.is_active = 1 LIMIT 4')->fetchAll();

$pageTitle = 'Beranda';
require __DIR__ . '/includes/header.php';
?>

<?php if (is_logged_in()): ?>
    <!-- BERANDA LOGGED IN -->
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
    <!-- BERANDA TAMU -->
    <section class="hero-tamu">
        <div class="container">
            <div class="hero-tamu-inner">
                <div class="hero-tamu-content">
                    <div class="hero-eyebrow">
                        Promo Spesial!
                    </div>
                    <h1>Ayam Geprek<br><span>Paling Juicy</span> di Jogja.</h1>
                    <p style="font-size:1.05rem;">Nikmati diskon 20% pemesanan hari ini khusus untuk pengguna Lapak Chicken. Pesan sekarang dan langsung diantar ke depan rumah kamu.</p>
                    
                    <form method="post" style="display:flex;gap:16px;margin-bottom:24px;">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <select name="branch_id" class="form-field" style="max-width:250px;min-height:48px;border:1px solid var(--outline);border-radius:12px;padding:0 16px;">
                            <?php foreach ($branches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary" style="border-radius:99px;padding:0 32px;font-weight:700;">Pesan Sekarang</button>
                    </form>
                    
                    <a href="#kenapa-lapak-chicken" class="btn btn-outline" style="border:none;padding-left:0;font-weight:600;"><i class="fa-solid fa-arrow-down"></i> Lihat Menu Pilihan</a>
                </div>
                <div class="hero-tamu-image">
                    <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80" alt="Ayam Geprek" style="border-radius:24px;box-shadow:0 20px 40px rgba(0,0,0,0.1);">
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="kenapa-lapak-chicken">
        <div class="container">
            <div class="section-header-row" style="margin-bottom:32px;">
                <div>
                    <h2 style="font-size:1.6rem;">Kenapa Lapak Chicken?</h2>
                    <p style="color:var(--secondary);font-size:1rem;margin-top:4px;">Rahasia ayam geprek yang bikin kamu gagal move on.</p>
                </div>
                <a href="<?= base_url('customer/login.php') ?>" class="link-see-all">Lihat Selengkapnya <i class="fa-solid fa-chevron-right" style="font-size:0.8rem;"></i></a>
            </div>

            <div class="bento-grid bento-kenapa">
                <div class="bento-card card-large" style="border-radius:24px;">
                    <div style="max-width:300px;position:relative;z-index:2;">
                        <h3 style="font-weight:800;">Bahan Segar Setiap Hari</h3>
                        <p style="font-size:0.95rem;opacity:0.9;line-height:1.5;">Ayam pilihan berkualitas tinggi yang diproses dan dimasak di hari yang sama.</p>
                    </div>
                </div>
                <div class="bento-card card-white" style="border-radius:24px;">
                    <div class="bento-icon-box"><i class="fa-solid fa-pepper-hot"></i></div>
                    <h4 style="font-size:1.15rem;margin-bottom:8px;font-weight:700;">Sambal Khas & Otentik</h4>
                    <p style="font-size:0.95rem;color:var(--secondary);">Pilihan level pedas yang pas dengan rasa yang khas.</p>
                </div>
                <div class="bento-card card-yellow" style="border-radius:24px;">
                    <div class="bento-icon-box" style="color:var(--primary-container);"><i class="fa-solid fa-wallet"></i></div>
                    <h4 style="font-size:1.15rem;margin-bottom:8px;font-weight:700;">Harga Mahasiswa</h4>
                    <p style="font-size:0.95rem;opacity:0.9;">Pasti ngenyangin meski dompet lagi menipis.</p>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
