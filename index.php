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
$bannersStmt = $db->prepare('SELECT * FROM banners WHERE branch_id = ? AND is_active = 1 ORDER BY id DESC');
$bannersStmt->execute([$selectedBranch]);
$banners = $bannersStmt->fetchAll();

$pageTitle = 'Beranda';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <h1>Lapak Chicken Seturan</h1>
        <p>Temukan kenikmatan ayam crispy favoritmu. Pesan untuk dine-in, takeaway, atau delivery dengan cepat dan mudah.</p>
        
        <div class="hero-search-bar" onclick="document.getElementById('branches').scrollIntoView({behavior: 'smooth'})" style="cursor: pointer;">
            <div class="hero-search-input">
                <span>Lokasi Terdekat</span>
                <small>Pilih cabang untuk mulai memesan</small>
            </div>
            <button class="hero-search-btn" type="button" aria-label="Cari Cabang">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </div>
</section>

<section id="branches" class="section section-light">
    <div class="container">
        <div class="page-title" style="margin-bottom: 32px;">
            <h2 style="font-size: 2rem;">Pilih Cabang</h2>
        </div>
        
        <div class="grid grid-3">
            <?php foreach ($branches as $branch): ?>
                <?php $isOpen = is_branch_open($db, (int) $branch['id']); ?>
                <form method="post" class="branch-card" style="opacity: <?= $isOpen ? '1' : '0.6' ?>;">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="branch_id" value="<?= (int) $branch['id'] ?>">
                    
                    <div class="branch-img-wrap">
                        <span class="badge <?= $isOpen ? 'badge-white' : 'badge-red' ?>" style="background: var(--white); color: <?= $isOpen ? 'var(--black)' : 'var(--danger)' ?>;">
                            <?= $isOpen ? 'Buka Sekarang' : 'Tutup' ?>
                        </span>
                        <!-- Placeholder since we don't have branch images in DB yet -->
                        <div class="placeholder"><i class="fa-solid fa-store"></i></div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 4px;">
                        <div>
                            <h3><?= e($branch['name']) ?></h3>
                            <p><?= e($branch['address']) ?></p>
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--black); white-space: nowrap;">
                            <i class="fa-solid fa-star" style="font-size: 0.8rem; margin-right: 2px;"></i> 4.8
                        </div>
                    </div>
                    
                    <button class="btn <?= $isOpen ? 'btn-primary' : 'btn-outline' ?>" type="submit" style="width: 100%; border-radius: var(--radius-pill);" <?= $isOpen ? '' : 'disabled' ?>>
                        <?= $isOpen ? 'Pesan di sini' : 'Tutup' ?>
                    </button>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($banners): ?>
<section class="section">
    <div class="container">
        <div class="page-title" style="margin-bottom: 24px;">
            <h2 style="font-size: 1.8rem;">Promo Menarik</h2>
        </div>
        <div class="banner-carousel" id="bannerCarousel">
            <div class="banner-track" id="bannerTrack">
                <?php foreach ($banners as $index => $banner): ?>
                    <article class="banner" style="background-image: url('<?= e($banner['image']) ?>')">
                        <h3><?= e($banner['title']) ?></h3>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if (count($banners) > 1): ?>
                <div class="banner-nav" id="bannerNav">
                    <?php foreach ($banners as $index => $banner): ?>
                        <div class="banner-dot <?= $index === 0 ? 'active' : '' ?>" data-index="<?= $index ?>"></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
