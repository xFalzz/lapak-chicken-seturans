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
$readyOrders = $ready->fetchAll();
$active = $db->prepare('SELECT o.*, COUNT(od.id) items_count FROM orders o LEFT JOIN order_details od ON od.order_id=o.id WHERE o.branch_id=? AND o.status IN ("confirmed","cooking") GROUP BY o.id ORDER BY o.created_at ASC');
$active->execute([$branchId]);
$activeOrders = $active->fetchAll();
$pageTitle = 'Kasir';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar" data-kasir-refresh data-branch-id="<?= $branchId ?>">
    <form class="admin-toolbar" method="post">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-field">
            <label>Cabang Kasir</label>
            <select name="branch_id" onchange="this.form.submit()">
                <?php foreach ($branches as $b): ?>
                    <option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
        <div>
            <h1 style="font-size:1.6rem;font-weight:800;margin-bottom:4px;">💳 Antrian Kasir</h1>
            <p style="color:var(--secondary);font-size:0.9rem;">Kelola pembayaran pesanan yang sudah siap</p>
        </div>
        <span class="badge badge-black" style="padding:8px 16px;font-size:0.8rem;border-radius:99px;display:flex;align-items:center;gap:8px;">
            <span style="width:8px;height:8px;background:#4CAF50;border-radius:50%;display:inline-block;"></span>
            Auto refresh 10s
        </span>
    </div>

    <div class="queue-section">
        <div class="queue-header">
            <h2>✅ Siap Dibayar</h2>
            <span class="queue-count" data-ready-count><?= count($readyOrders) ?></span>
        </div>
        <div class="grid grid-3 order-queue" data-queue-ready>
            <?php if (empty($readyOrders)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <h3>Belum ada pesanan siap bayar</h3>
                    <p>Pesanan akan muncul setelah dapur menyelesaikan masakan</p>
                </div>
            <?php else: ?>
                <?php foreach ($readyOrders as $o): ?>
                <article class="card order-ticket">
                    <div class="code"><?= e($o['order_code']) ?></div>
                    <p class="ticket-meta"><i class="fa-solid fa-user"></i> <?= e($o['customer_name']) ?></p>
                    <p class="ticket-meta"><i class="fa-solid fa-tag"></i> <?= e($o['order_type']) ?> • <?= (int) $o['items_count'] ?> item</p>
                    <p class="ticket-meta"><i class="fa-regular fa-clock"></i> <?= time_ago($o['created_at']) ?></p>
                    <div class="ticket-total"><?= format_rupiah((float) $o['total']) ?></div>
                    <a class="btn btn-primary" href="process.php?order_id=<?= (int) $o['id'] ?>" style="width:100%;justify-content:center;border-radius:var(--radius-md);margin-top:4px;">
                        <i class="fa-solid fa-credit-card"></i> Proses Pembayaran
                    </a>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="queue-section">
        <div class="queue-header">
            <h2>🔥 Sedang Diproses</h2>
            <span class="queue-count" data-active-count><?= count($activeOrders) ?></span>
        </div>
        <div class="grid grid-3 order-queue" data-queue-active>
            <?php if (empty($activeOrders)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-fire-flame-curved"></i>
                    <h3>Tidak ada pesanan diproses</h3>
                    <p>Pesanan yang dikonfirmasi atau sedang dimasak akan muncul di sini</p>
                </div>
            <?php else: ?>
                <?php foreach ($activeOrders as $o): ?>
                <article class="card order-ticket">
                    <div class="code"><?= e($o['order_code']) ?></div>
                    <p class="ticket-meta"><i class="fa-solid fa-user"></i> <?= e($o['customer_name']) ?></p>
                    <p class="ticket-meta"><i class="fa-solid fa-spinner fa-spin"></i> <?= get_status_label($o['status']) ?></p>
                    <p class="ticket-meta"><i class="fa-regular fa-clock"></i> <?= time_ago($o['created_at']) ?></p>
                    <div class="ticket-total"><?= format_rupiah((float) $o['total']) ?></div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/js/kasir.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
