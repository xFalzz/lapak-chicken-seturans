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
$pageTitle = 'Pilih Cabang';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <h1>Lapak Chicken Seturan</h1>
        <p>Ayam crispy panas, saus pilihan, dan checkout cepat untuk dine-in, takeaway, atau delivery di cabang terdekat.</p>
        <a class="btn btn-primary" href="#branches"><i class="fa-solid fa-location-dot"></i>Pilih Cabang</a>
    </div>
</section>
<section id="branches" class="section">
    <div class="container">
        <div class="page-title">
            <h1>Pilih cabang</h1>
            <span class="badge badge-black">Buka 10:00-22:00</span>
        </div>
        <div class="grid grid-2">
            <?php foreach ($branches as $branch): ?>
                <form method="post" class="card branch-card">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="branch_id" value="<?= (int) $branch['id'] ?>">
                    <h2><?= e($branch['name']) ?></h2>
                    <p class="muted"><?= e($branch['address']) ?></p>
                    <p><i class="fa-solid fa-phone"></i> <?= e($branch['phone']) ?></p>
                    <span class="badge <?= is_branch_open($db, (int) $branch['id']) ? 'badge-green' : 'badge-red' ?>">
                        <?= is_branch_open($db, (int) $branch['id']) ? 'Buka sekarang' : 'Tutup' ?>
                    </span>
                    <button class="btn btn-primary" type="submit">Pesan dari cabang ini</button>
                </form>
            <?php endforeach; ?>
        </div>
        <?php if ($banners): ?>
            <div class="section">
                <div class="banner-track" data-carousel>
                    <?php foreach ($banners as $banner): ?>
                        <article class="banner" style="background-image: linear-gradient(rgba(0,0,0,.15), rgba(0,0,0,.7)), url('<?= e($banner['image']) ?>')">
                            <h2><?= e($banner['title']) ?></h2>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
