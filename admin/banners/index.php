<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    if (!empty($_POST['id'])) {
        $db->prepare('UPDATE banners SET branch_id=?, title=?, image=?, is_active=? WHERE id=?')->execute([(int) $_POST['branch_id'], sanitize($_POST['title']), sanitize($_POST['image']), isset($_POST['is_active']) ? 1 : 0, (int) $_POST['id']]);
    } else {
        $db->prepare('INSERT INTO banners (branch_id,title,image,is_active) VALUES (?,?,?,?)')->execute([(int) $_POST['branch_id'], sanitize($_POST['title']), sanitize($_POST['image']), isset($_POST['is_active']) ? 1 : 0]);
    }
    redirect('index.php');
}
$branchFilter = (int) ($_GET['branch_id'] ?? 0);
$branches = branch_options($db);
$stmt = $db->prepare('SELECT ba.*, br.name branch_name FROM banners ba JOIN branches br ON br.id=ba.branch_id WHERE (?=0 OR ba.branch_id=?) ORDER BY ba.id DESC');
$stmt->execute([$branchFilter, $branchFilter]);
$pageTitle = 'Banner';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <form class="admin-toolbar"><div class="form-field"><label>Filter cabang</label><select name="branch_id" onchange="this.form.submit()"><option value="0">Semua</option><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $branchFilter === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div></form>
    <div class="grid grid-2">
        <form class="card form-grid" method="post"><h1>Tambah Banner</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Cabang</label><select name="branch_id"><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label>Title</label><input name="title"></div><div class="form-field"><label>Image URL</label><input name="image" data-image-preview-input></div><label><input type="checkbox" name="is_active" checked> Aktif</label><button class="btn btn-primary">Tambah</button></form>
        <div class="grid"><?php foreach ($stmt->fetchAll() as $ba): ?><article class="card"><img src="<?= e($ba['image']) ?>" alt=""><h3><?= e($ba['title']) ?></h3><p><?= e($ba['branch_name']) ?> - <?= $ba['is_active'] ? 'Aktif' : 'Nonaktif' ?></p></article><?php endforeach; ?></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
