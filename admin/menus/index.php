<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    if (isset($_POST['toggle'])) {
        $db->prepare('UPDATE menus SET is_active = 1 - is_active WHERE id = ?')->execute([(int) $_POST['id']]);
    } elseif (isset($_POST['delete'])) {
        $db->prepare('DELETE FROM menus WHERE id = ?')->execute([(int) $_POST['id']]);
    }
    redirect('index.php');
}
$category = (int) ($_GET['category_id'] ?? 0);
$categories = $db->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$stmt = $db->prepare('SELECT m.*, c.name category_name FROM menus m JOIN categories c ON c.id=m.category_id WHERE (?=0 OR m.category_id=?) ORDER BY m.name');
$stmt->execute([$category, $category]);
$pageTitle = 'Menu Admin';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="page-title"><h1>Menu</h1><a class="btn btn-primary" href="add.php"><i class="fa-solid fa-plus"></i>Tambah</a></div>
    <form class="admin-toolbar"><div class="form-field"><label>Filter kategori</label><select name="category_id" onchange="this.form.submit()"><option value="0">Semua</option><?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>" <?= $category === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div></form>
    <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($stmt->fetchAll() as $m): ?><tr><td><?= e($m['name']) ?></td><td><?= e($m['category_name']) ?></td><td><?= format_rupiah((float) $m['price']) ?></td><td><span class="badge <?= $m['is_active'] ? 'badge-green' : 'badge-red' ?>"><?= $m['is_active'] ? 'Aktif' : 'Habis' ?></span></td><td class="crud-actions"><a class="btn btn-primary" href="edit.php?id=<?= (int) $m['id'] ?>">Edit</a><form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="btn btn-outline" name="toggle">Toggle</button><button class="btn btn-danger" name="delete">Delete</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
