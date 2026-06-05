<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
$branches = branch_options($db);
$branchId = (int) ($_POST['branch_id'] ?? $_GET['branch_id'] ?? ($branches[0]['id'] ?? 1));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $db->prepare('DELETE FROM operating_hours WHERE branch_id=?')->execute([$branchId]);
    $stmt = $db->prepare('INSERT INTO operating_hours (branch_id, day_of_week, open_time, close_time, is_closed) VALUES (?,?,?,?,?)');
    for ($d = 0; $d <= 6; $d++) {
        $stmt->execute([$branchId, $d, $_POST['open_time'][$d] ?: null, $_POST['close_time'][$d] ?: null, isset($_POST['is_closed'][$d]) ? 1 : 0]);
    }
    flash('success', 'Jam operasional disimpan');
    redirect('index.php?branch_id=' . $branchId);
}
$stmt = $db->prepare('SELECT * FROM operating_hours WHERE branch_id=? ORDER BY day_of_week');
$stmt->execute([$branchId]);
$hours = array_column($stmt->fetchAll(), null, 'day_of_week');
$days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$pageTitle = 'Jam Operasional';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <form class="admin-toolbar"><div class="form-field"><label>Cabang</label><select name="branch_id" onchange="this.form.submit()"><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div></form>
    <form class="card form-grid" method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="branch_id" value="<?= $branchId ?>"><div class="table-wrap"><table><thead><tr><th>Hari</th><th>Buka</th><th>Tutup</th><th>Libur</th></tr></thead><tbody><?php foreach ($days as $d => $label): ?><tr><td><?= $label ?></td><td><input type="time" name="open_time[<?= $d ?>]" value="<?= e(substr($hours[$d]['open_time'] ?? '10:00', 0, 5)) ?>"></td><td><input type="time" name="close_time[<?= $d ?>]" value="<?= e(substr($hours[$d]['close_time'] ?? '22:00', 0, 5)) ?>"></td><td><input type="checkbox" name="is_closed[<?= $d ?>]" <?= !empty($hours[$d]['is_closed']) ? 'checked' : '' ?>></td></tr><?php endforeach; ?></tbody></table></div><button class="btn btn-primary">Bulk Save</button></form>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
