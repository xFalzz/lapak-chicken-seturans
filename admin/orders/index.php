<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin', 'kasir']);
$db = db();
$branches = branch_options($db);
$where = ['1=1'];
$params = [];
foreach (['branch_id', 'status', 'order_type'] as $field) {
    if (!empty($_GET[$field])) {
        $where[] = "o.$field = ?";
        $params[] = sanitize($_GET[$field]);
    }
}
if (!empty($_GET['date_from'])) {
    $where[] = 'DATE(o.created_at) >= ?';
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $where[] = 'DATE(o.created_at) <= ?';
    $params[] = $_GET['date_to'];
}
$sql = 'SELECT o.*, b.name branch_name, COALESCE(p.payment_status, "unpaid") payment_status FROM orders o JOIN branches b ON b.id=o.branch_id LEFT JOIN payments p ON p.order_id=o.id WHERE ' . implode(' AND ', $where) . ' ORDER BY o.created_at DESC';
if (isset($_GET['export'])) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="orders.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['order_code', 'branch', 'customer', 'order_type', 'status', 'total', 'payment_status', 'created_at']);
    foreach ($stmt->fetchAll() as $row) {
        fputcsv($out, [$row['order_code'], $row['branch_name'], $row['customer_name'], $row['order_type'], $row['status'], $row['total'], $row['payment_status'], $row['created_at']]);
    }
    exit;
}
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
$pageTitle = 'Pesanan';
$bodyClass = user_role() === 'kasir' ? 'kasir-layout' : 'admin-layout';
require __DIR__ . '/../../includes/header.php';
if (user_role() === 'kasir') {
    require __DIR__ . '/../../includes/sidebar-kasir.php';
} else {
    require __DIR__ . '/../../includes/sidebar-admin.php';
}
?>
<section class="content-with-sidebar">
    <div class="page-title"><h1>Pesanan</h1><a class="btn btn-primary" href="?<?= e(http_build_query([...$_GET, 'export' => 1])) ?>"><i class="fa-solid fa-file-csv"></i>Export CSV</a></div>
    <form class="admin-toolbar">
        <div class="form-field"><label>Cabang</label><select name="branch_id"><option value="">Semua</option><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= ($_GET['branch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label>Status</label><select name="status"><option value="">Semua</option><?php foreach (['pending','confirmed','cooking','ready','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= get_status_label($s) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label>Tipe</label><select name="order_type"><option value="">Semua</option><?php foreach (['dine_in','takeaway','delivery'] as $t): ?><option value="<?= $t ?>" <?= ($_GET['order_type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label>Dari</label><input type="date" name="date_from" value="<?= e($_GET['date_from'] ?? '') ?>"></div>
        <div class="form-field"><label>Sampai</label><input type="date" name="date_to" value="<?= e($_GET['date_to'] ?? '') ?>"></div>
        <button class="btn btn-primary">Filter</button>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Kode</th><th>Cabang</th><th>Customer</th><th>Tipe</th><th>Status</th><th>Total</th><th>Payment</th><th>Waktu</th><th>Aksi</th></tr></thead><tbody>
    <?php foreach ($orders as $o): ?><tr><td><?= e($o['order_code']) ?></td><td><?= e($o['branch_name']) ?></td><td><?= e($o['customer_name']) ?></td><td><span class="badge badge-gray"><?= e($o['order_type']) ?></span></td><td><select data-order-status="<?= (int) $o['id'] ?>"><?php foreach (['pending','confirmed','cooking','ready','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= get_status_label($s) ?></option><?php endforeach; ?></select></td><td><?= format_rupiah((float) $o['total']) ?></td><td><?= e($o['payment_status']) ?></td><td><?= e($o['created_at']) ?></td><td><a class="btn btn-primary" href="detail.php?id=<?= (int) $o['id'] ?>">Detail</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
