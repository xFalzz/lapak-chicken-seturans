<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
$id = (int) ($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $stmt = $db->prepare('UPDATE branches SET name=?, address=?, phone=? WHERE id=?');
    $stmt->execute([sanitize($_POST['name']), sanitize($_POST['address']), sanitize($_POST['phone']), $id]);
    flash('success', 'Cabang diperbarui');
    redirect('index.php');
}
$stmt = $db->prepare('SELECT * FROM branches WHERE id=?');
$stmt->execute([$id]);
$branch = $stmt->fetch();
$settingsCount = $db->prepare('SELECT COUNT(*) FROM settings WHERE branch_id=?');
$settingsCount->execute([$id]);
$hoursCount = $db->prepare('SELECT COUNT(*) FROM operating_hours WHERE branch_id=?');
$hoursCount->execute([$id]);
$pageTitle = 'Edit Cabang';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar"><form class="card form-grid" method="post"><h1>Edit Cabang</h1><p class="muted">Settings: <?= (int) $settingsCount->fetchColumn() ?>, jam operasional: <?= (int) $hoursCount->fetchColumn() ?></p><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Nama</label><input name="name" value="<?= e($branch['name'] ?? '') ?>" required></div><div class="form-field"><label>Alamat</label><textarea name="address"><?= e($branch['address'] ?? '') ?></textarea></div><div class="form-field"><label>Phone</label><input name="phone" value="<?= e($branch['phone'] ?? '') ?>"></div><button class="btn btn-primary">Simpan</button></form></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
