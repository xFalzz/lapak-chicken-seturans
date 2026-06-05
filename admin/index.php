<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['admin']);
$db = db();
if (isset($_GET['branch_id'])) {
    $_SESSION['admin_branch_id'] = $_GET['branch_id'] === 'all' ? null : (int) $_GET['branch_id'];
}
$branchId = $_SESSION['admin_branch_id'] ?? null;
$branches = branch_options($db);
$params = [];
$branchWhere = '';
if ($branchId) {
    $branchWhere = ' AND branch_id = ?';
    $params[] = $branchId;
}
$stats = [];
foreach ([
    'orders' => 'SELECT COUNT(*) FROM orders WHERE DATE(created_at)=CURDATE()' . $branchWhere,
    'revenue' => 'SELECT COALESCE(SUM(total),0) FROM orders WHERE status="completed" AND DATE(created_at)=CURDATE()' . $branchWhere,
    'pending' => 'SELECT COUNT(*) FROM orders WHERE status="pending"' . $branchWhere,
    'completed' => 'SELECT COUNT(*) FROM orders WHERE status="completed" AND DATE(created_at)=CURDATE()' . $branchWhere,
] as $key => $sql) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats[$key] = $stmt->fetchColumn();
}
$recent = $db->prepare('SELECT o.*, b.name branch_name FROM orders o JOIN branches b ON b.id=o.branch_id WHERE (? IS NULL OR o.branch_id=?) ORDER BY o.created_at DESC LIMIT 10');
$recent->execute([$branchId, $branchId]);
$chart = $db->prepare('SELECT DATE(created_at) d, COALESCE(SUM(total),0) revenue FROM orders WHERE status="completed" AND created_at >= CURDATE() - INTERVAL 6 DAY AND (? IS NULL OR branch_id=?) GROUP BY DATE(created_at) ORDER BY d');
$chart->execute([$branchId, $branchId]);
$bars = $chart->fetchAll();
$maxRevenue = max(1, ...array_map(fn($r) => (float) $r['revenue'], $bars ?: [['revenue' => 1]]));
$pageTitle = 'Admin Dashboard';
$bodyClass = 'admin-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="page-title">
        <h1>Dashboard</h1>
        <form class="admin-toolbar">
            <div class="form-field"><label>Cabang</label><select name="branch_id" onchange="this.form.submit()"><option value="all">All Branches</option><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= (int) $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div>
        </form>
    </div>
    <div class="grid grid-4">
        <div class="card stat-card"><span>Total Orders Hari Ini</span><strong><?= (int) $stats['orders'] ?></strong></div>
        <div class="card stat-card"><span>Revenue Hari Ini</span><strong><?= format_rupiah((float) $stats['revenue']) ?></strong></div>
        <div class="card stat-card"><span>Pending</span><strong><?= (int) $stats['pending'] ?></strong></div>
        <div class="card stat-card"><span>Completed</span><strong><?= (int) $stats['completed'] ?></strong></div>
    </div>
    <div class="grid grid-2 section">
        <div class="card">
            <h2>Revenue 7 Hari</h2>
            <div class="chart"><?php foreach ($bars as $bar): ?><div class="bar" style="height:<?= max(8, ((float) $bar['revenue'] / $maxRevenue) * 160) ?>px"><?= date('d/m', strtotime($bar['d'])) ?></div><?php endforeach; ?></div>
        </div>
        <div class="card">
            <h2>Quick Info</h2>
            <p>Cabang aktif: <?= count($branches) ?></p>
            <p>Banner aktif: <?= (int) $db->query('SELECT COUNT(*) FROM banners WHERE is_active=1')->fetchColumn() ?></p>
            <p>Jam hari ini: 10:00-22:00</p>
        </div>
    </div>
    <div class="table-wrap">
        <table><thead><tr><th>Kode</th><th>Cabang</th><th>Customer</th><th>Status</th><th>Total</th><th>Waktu</th></tr></thead><tbody>
        <?php foreach ($recent->fetchAll() as $o): ?><tr><td><?= e($o['order_code']) ?></td><td><?= e($o['branch_name']) ?></td><td><?= e($o['customer_name']) ?></td><td><span class="badge <?= get_status_color($o['status']) ?>"><?= get_status_label($o['status']) ?></span></td><td><?= format_rupiah((float) $o['total']) ?></td><td><?= e($o['created_at']) ?></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
</section>
<script src="<?= base_url('assets/js/admin.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
