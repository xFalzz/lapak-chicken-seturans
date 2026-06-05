<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    if (!empty($_POST['reset_id']) && $password) {
        $db->prepare('UPDATE users SET password=? WHERE id=?')->execute([$password, (int) $_POST['reset_id']]);
    } else {
        $db->prepare('INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,?)')->execute([sanitize($_POST['name']), filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ?: null, sanitize($_POST['phone']), $password, sanitize($_POST['role'])]);
    }
    redirect('index.php');
}
$users = $db->query('SELECT * FROM users WHERE role <> "customer" ORDER BY role, name')->fetchAll();
$pageTitle = 'Staff';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="grid grid-2">
        <form class="card form-grid" method="post"><h1>Tambah Staff</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Nama</label><input name="name" required></div><div class="form-field"><label>Email</label><input type="email" name="email"></div><div class="form-field"><label>Phone</label><input name="phone" required></div><div class="form-field"><label>Role</label><select name="role"><option value="kasir">Kasir</option><option value="dapur">Dapur</option><option value="admin">Admin</option></select></div><div class="form-field"><label>Password</label><input type="password" name="password" required></div><button class="btn btn-primary">Simpan</button></form>
        <div class="table-wrap"><table><thead><tr><th>Nama</th><th>Email</th><th>Phone</th><th>Role</th><th>Reset</th></tr></thead><tbody><?php foreach ($users as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td><td><span class="badge badge-black"><?= e($u['role']) ?></span></td><td><form method="post" class="crud-actions"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="reset_id" value="<?= (int) $u['id'] ?>"><input type="password" name="password" placeholder="Password baru" required><button class="btn btn-primary">Reset</button></form></td></tr><?php endforeach; ?></tbody></table></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
