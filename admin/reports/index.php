<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
$branches = branch_options($db);
$branchId = (int) ($_GET['branch_id'] ?? 0);
$from = $_GET['date_from'] ?? date('Y-m-01');
$to = $_GET['date_to'] ?? date('Y-m-d');
$branchSql = $branchId ? ' AND o.branch_id = ?' : '';
$params = [$from, $to];
if ($branchId) $params[] = $branchId;
$summary = $db->prepare('SELECT COUNT(*) orders_count, COALESCE(SUM(total),0) revenue FROM orders o WHERE DATE(o.created_at) BETWEEN ? AND ?' . $branchSql);
$summary->execute($params);
$summary = $summary->fetch();
$top = $db->prepare('SELECT m.name, SUM(od.quantity) qty FROM order_details od JOIN menus m ON m.id=od.menu_id JOIN orders o ON o.id=od.order_id WHERE DATE(o.created_at) BETWEEN ? AND ?' . $branchSql . ' GROUP BY m.id ORDER BY qty DESC LIMIT 5');
$top->execute($params);
$types = $db->prepare('SELECT o.order_type, COUNT(*) total FROM orders o WHERE DATE(o.created_at) BETWEEN ? AND ?' . $branchSql . ' GROUP BY o.order_type');
$types->execute($params);
$ratings = $db->query('SELECT b.name, ROUND(AVG(r.rating),2) rating FROM reviews r JOIN orders o ON o.id=r.order_id JOIN branches b ON b.id=o.branch_id GROUP BY b.id')->fetchAll();
$pageTitle = 'Reports';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <form class="admin-toolbar"><div class="form-field"><label>Cabang</label><select name="branch_id"><option value="0">Semua</option><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label>Dari</label><input type="date" name="date_from" value="<?= e($from) ?>"></div><div class="form-field"><label>Sampai</label><input type="date" name="date_to" value="<?= e($to) ?>"></div><button class="btn btn-primary">Filter</button><button class="btn btn-outline" type="button" onclick="window.print()">Print</button></form>
    <div class="grid grid-3">
        <div class="card stat-card"><span>Revenue</span><strong><?= format_rupiah((float) $summary['revenue']) ?></strong></div>
        <div class="card stat-card"><span>Orders</span><strong><?= (int) $summary['orders_count'] ?></strong></div>
        <div class="card"><h2>Rating Cabang</h2><?php foreach ($ratings as $r): ?><p><?= e($r['name']) ?>: <strong><?= e($r['rating']) ?></strong></p><?php endforeach; ?></div>
    </div>
    <div class="grid grid-2 section">
        <div class="card"><h2>Top 5 Menu</h2><?php foreach ($top->fetchAll() as $r): ?><p><?= e($r['name']) ?> <strong><?= (int) $r['qty'] ?></strong></p><?php endforeach; ?></div>
        <div class="card"><h2>Orders by Type</h2><?php foreach ($types->fetchAll() as $r): ?><p><?= e($r['order_type']) ?> <strong><?= (int) $r['total'] ?></strong></p><?php endforeach; ?></div>
    </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
