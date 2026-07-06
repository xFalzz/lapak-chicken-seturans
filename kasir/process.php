<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['kasir', 'admin']);
$db = db();
$id = (int) ($_GET['order_id'] ?? 0);
$stmt = $db->prepare('SELECT o.*, b.name branch_name FROM orders o JOIN branches b ON b.id=o.branch_id WHERE o.id=?');
$stmt->execute([$id]);
$order = $stmt->fetch();
$items = $db->prepare('SELECT od.*, m.name menu_name, s.name sauce_name FROM order_details od JOIN menus m ON m.id=od.menu_id LEFT JOIN sauces s ON s.id=od.sauce_id WHERE od.order_id=?');
$items->execute([$id]);
$pageTitle = 'Proses Pembayaran';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar">
    <div class="page-title"><h1><?= e($order['order_code'] ?? '') ?></h1><span class="payment-total"><?= format_rupiah((float) ($order['total'] ?? 0)) ?></span></div>
    <div class="grid grid-2">
        <div class="card">
            <h2>Detail</h2>
            <p><?= e($order['branch_name'] ?? '') ?> - <?= e($order['customer_name'] ?? '') ?></p>
            <?php foreach ($items->fetchAll() as $i): ?>
                <p style="margin-bottom:8px;">
                    <?= (int) $i['quantity'] ?>x <strong><?= e($i['menu_name']) ?></strong> <?= $i['sauce_name'] ? '(' . e($i['sauce_name']) . ')' : '' ?>
                    <?= (isset($i['spice_level']) && $i['spice_level'] !== '' && $i['spice_level'] !== '0') ? '<span style="color:#b29500;font-weight:700;">[Lvl ' . e($i['spice_level']) . ']</span>' : '' ?>
                    <?= !empty($i['notes']) ? '<br><em style="color:var(--secondary);font-size:0.85rem;">Catatan: "' . e($i['notes']) . '"</em>' : '' ?>
                    <br><strong><?= format_rupiah((float) $i['subtotal']) ?></strong>
                </p>
            <?php endforeach; ?>
        </div>
        <form class="card form-grid" data-pay-form><input type="hidden" name="order_id" value="<?= $id ?>"><h2>Pembayaran</h2><?php foreach (['Cash','QRIS','Transfer Bank','COD'] as $m): ?><label><input type="radio" name="payment_method" value="<?= e($m) ?>" <?= $m === 'Cash' ? 'checked' : '' ?>> <i class="fa-solid fa-money-bill-wave"></i> <?= e($m) ?></label><?php endforeach; ?><div class="form-field"><label>Uang diterima</label><input type="number" name="amount_paid" data-cash-input data-total="<?= e($order['total'] ?? 0) ?>" value="<?= e($order['total'] ?? 0) ?>"></div><div class="form-field"><label>Reference no</label><input name="reference_no"></div><p>Kembalian: <strong data-change>Rp 0</strong></p><button class="btn btn-primary">Konfirmasi Pembayaran</button></form>
    </div>
</section>
<script src="<?= base_url('assets/js/kasir.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
