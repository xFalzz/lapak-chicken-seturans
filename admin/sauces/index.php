<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    if (!empty($_POST['id'])) {
        $db->prepare('UPDATE sauces SET name=?, price_extra=?, is_active=? WHERE id=?')->execute([sanitize($_POST['name']), (float) $_POST['price_extra'], isset($_POST['is_active']) ? 1 : 0, (int) $_POST['id']]);
    } else {
        $db->prepare('INSERT INTO sauces (name,price_extra,is_active) VALUES (?,?,?)')->execute([sanitize($_POST['name']), (float) $_POST['price_extra'], isset($_POST['is_active']) ? 1 : 0]);
    }
    redirect('index.php');
}
$rows = $db->query('SELECT * FROM sauces ORDER BY price_extra, name')->fetchAll();
$pageTitle = 'Saus';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="grid grid-2">
        <form class="card form-grid" method="post"><h1>Saus</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Nama</label><input name="name" required></div><div class="form-field"><label>Harga ekstra</label><input type="number" name="price_extra" min="0" value="0"></div><label><input type="checkbox" name="is_active" checked> Aktif</label><button class="btn btn-primary">Tambah</button></form>
        <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Extra</th><th>Status</th></tr></thead><tbody><?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= (float) $r['price_extra'] > 0 ? format_rupiah((float) $r['price_extra']) : 'Gratis' ?></td><td><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
