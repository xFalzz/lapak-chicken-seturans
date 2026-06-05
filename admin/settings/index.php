<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
$branches = branch_options($db);
$branchId = (int) ($_POST['branch_id'] ?? $_GET['branch_id'] ?? ($branches[0]['id'] ?? 1));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    foreach ($_POST['settings'] ?? [] as $key => $value) {
        $stmt = $db->prepare('INSERT INTO settings (branch_id, `key`, `value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
        $stmt->execute([$branchId, sanitize($key), sanitize($value)]);
    }
    if (!empty($_POST['custom_key'])) {
        $db->prepare('INSERT INTO settings (branch_id, `key`, `value`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)')->execute([$branchId, sanitize($_POST['custom_key']), sanitize($_POST['custom_value'])]);
    }
    redirect('index.php?branch_id=' . $branchId);
}
$stmt = $db->prepare('SELECT * FROM settings WHERE branch_id=? ORDER BY `key`');
$stmt->execute([$branchId]);
$settings = array_column($stmt->fetchAll(), 'value', 'key');
$pageTitle = 'Settings';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <form class="admin-toolbar"><div class="form-field"><label>Cabang</label><select name="branch_id" onchange="this.form.submit()"><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div></form>
    <form class="card form-grid" method="post"><h1>Settings</h1><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="branch_id" value="<?= $branchId ?>"><?php foreach (['tax_rate','min_order','whatsapp','address_note'] as $key): ?><div class="form-field"><label><?= e($key) ?></label><input name="settings[<?= e($key) ?>]" value="<?= e($settings[$key] ?? '') ?>"></div><?php endforeach; ?><div class="grid grid-2"><div class="form-field"><label>Custom key</label><input name="custom_key"></div><div class="form-field"><label>Custom value</label><input name="custom_value"></div></div><button class="btn btn-primary">Simpan</button></form>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
