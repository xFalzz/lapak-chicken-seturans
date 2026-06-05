<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $slug = sanitize($_POST['slug']) ?: strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name']), '-'));
    if (!empty($_POST['id'])) {
        $db->prepare('UPDATE categories SET name=?, slug=?, icon=?, is_active=? WHERE id=?')->execute([sanitize($_POST['name']), $slug, sanitize($_POST['icon']), isset($_POST['is_active']) ? 1 : 0, (int) $_POST['id']]);
    } else {
        $db->prepare('INSERT INTO categories (name,slug,icon,is_active) VALUES (?,?,?,?)')->execute([sanitize($_POST['name']), $slug, sanitize($_POST['icon']), isset($_POST['is_active']) ? 1 : 0]);
    }
    redirect('index.php');
}
$rows = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$pageTitle = 'Kategori';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="grid grid-2">
        <form class="card form-grid" method="post"><h1>Kategori</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Nama</label><input name="name" required></div><div class="form-field"><label>Slug</label><input name="slug"></div><div class="form-field"><label>Icon FontAwesome</label><input name="icon" value="fa-solid fa-utensils"></div><label><input type="checkbox" name="is_active" checked> Aktif</label><button class="btn btn-primary">Tambah</button></form>
        <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Slug</th><th>Icon</th><th>Status</th></tr></thead><tbody><?php foreach ($rows as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['slug']) ?></td><td><i class="<?= e($r['icon']) ?>"></i> <?= e($r['icon']) ?></td><td><?= $r['is_active'] ? 'Aktif' : 'Nonaktif' ?></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
