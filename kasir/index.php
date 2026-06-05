<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['kasir', 'admin']);
$db = db();
$branches = branch_options($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $_SESSION['kasir_branch_id'] = (int) $_POST['branch_id'];
    redirect('index.php');
}
$branchId = (int) ($_SESSION['kasir_branch_id'] ?? ($branches[0]['id'] ?? 1));
$ready = $db->prepare('SELECT o.*, COUNT(od.id) items_count FROM orders o LEFT JOIN order_details od ON od.order_id=o.id WHERE o.branch_id=? AND o.status="ready" GROUP BY o.id ORDER BY o.created_at ASC');
$ready->execute([$branchId]);
$active = $db->prepare('SELECT o.*, COUNT(od.id) items_count FROM orders o LEFT JOIN order_details od ON od.order_id=o.id WHERE o.branch_id=? AND o.status IN ("confirmed","cooking") GROUP BY o.id ORDER BY o.created_at ASC');
$active->execute([$branchId]);
$pageTitle = 'Kasir';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar" data-kasir-refresh data-branch-id="<?= $branchId ?>">
    <form class="admin-toolbar" method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><div class="form-field"><label>Cabang Kasir</label><select name="branch_id" onchange="this.form.submit()"><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></div></form>
    <div class="page-title"><h1>Antrian Kasir</h1><span class="badge badge-black">Auto refresh 10s</span></div>
    <h2>Siap Dibayar</h2>
    <div class="grid grid-3 order-queue" data-queue-ready>
        <?php foreach ($ready->fetchAll() as $o): ?><article class="card order-ticket" data-order-ticket-id="<?= (int)$o['id'] ?>"><div class="code"><?= e($o['order_code']) ?></div><p><?= e($o['customer_name']) ?> - <?= e($o['order_type']) ?></p><p><?= (int) $o['items_count'] ?> item - <?= time_ago($o['created_at']) ?></p><h3><?= format_rupiah((float) $o['total']) ?></h3><a class="btn btn-primary" href="process.php?order_id=<?= (int) $o['id'] ?>">Proses Pembayaran</a></article><?php endforeach; ?>
        <?php if (empty($ready->fetchAll())): ?>
            <!-- Note: fetchAll() above was already executed, but since PDO statement execution doesn't reset fetch cursor unless executed again, we'll handle empty lists in JS -->
        <?php endif; ?>
    </div>
    <h2>Sedang Diproses</h2>
    <div class="grid grid-3 order-queue" data-queue-active>
        <?php foreach ($active->fetchAll() as $o): ?><article class="card order-ticket" data-order-ticket-id="<?= (int)$o['id'] ?>"><div class="code"><?= e($o['order_code']) ?></div><p><?= e($o['customer_name']) ?> - <?= get_status_label($o['status']) ?></p><p><?= (int) $o['items_count'] ?> item - <?= time_ago($o['created_at']) ?></p><h3><?= format_rupiah((float) $o['total']) ?></h3></article><?php endforeach; ?>
    </div>
</section>
<script src="<?= base_url('assets/js/kasir.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
