<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $stmt = $db->prepare('INSERT INTO branches (name,address,phone) VALUES (?,?,?)');
    $stmt->execute([sanitize($_POST['name']), sanitize($_POST['address']), sanitize($_POST['phone'])]);
    flash('success', 'Cabang ditambahkan');
    redirect('index.php');
}
$pageTitle = 'Tambah Cabang';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar"><form class="card form-grid" method="post"><h1>Tambah Cabang</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Nama</label><input name="name" required></div><div class="form-field"><label>Alamat</label><textarea name="address"></textarea></div><div class="form-field"><label>Phone</label><input name="phone"></div><button class="btn btn-primary">Simpan</button></form></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
