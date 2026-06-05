<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
$id = (int) ($_GET['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $slug = sanitize($_POST['slug']) ?: strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $_POST['name']), '-'));
    $stmt = $db->prepare('UPDATE menus SET category_id=?, name=?, slug=?, description=?, price=?, is_active=? WHERE id=?');
    $stmt->execute([(int) $_POST['category_id'], sanitize($_POST['name']), $slug, sanitize($_POST['description']), (float) $_POST['price'], isset($_POST['is_active']) ? 1 : 0, $id]);
    redirect('index.php');
}
$menu = $db->prepare('SELECT * FROM menus WHERE id=?');
$menu->execute([$id]);
$menu = $menu->fetch();
$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$pageTitle = 'Edit Menu';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar"><form class="card form-grid" method="post"><h1>Edit Menu</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Nama</label><input name="name" value="<?= e($menu['name'] ?? '') ?>" required></div><div class="form-field"><label>Slug</label><input name="slug" value="<?= e($menu['slug'] ?? '') ?>"></div><div class="form-field"><label>Kategori</label><select name="category_id"><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= ($menu['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label>Deskripsi</label><textarea name="description"><?= e($menu['description'] ?? '') ?></textarea></div><div class="form-field"><label>Harga</label><input type="number" name="price" value="<?= e($menu['price'] ?? 0) ?>" min="0" required></div><label><input type="checkbox" name="is_active" <?= !empty($menu['is_active']) ? 'checked' : '' ?>> Aktif</label><button class="btn btn-primary">Simpan</button></form></section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
