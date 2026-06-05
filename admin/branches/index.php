<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$branches = branch_options(db());
$pageTitle = 'Cabang';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="page-title"><h1>Cabang</h1><a class="btn btn-primary" href="add.php"><i class="fa-solid fa-plus"></i>Tambah</a></div>
    <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Alamat</th><th>Phone</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($branches as $b): ?><tr><td><?= e($b['name']) ?></td><td><?= e($b['address']) ?></td><td><?= e($b['phone']) ?></td><td><?= e($b['created_at']) ?></td><td><a class="btn btn-primary" href="edit.php?id=<?= (int) $b['id'] ?>">Edit</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
