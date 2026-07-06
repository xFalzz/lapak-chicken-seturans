<?php
require_once __DIR__ . '/../../includes/functions.php';
require_role(['admin']);
$db = db();
$id = (int) ($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT o.*, b.name branch_name, b.address branch_address, p.payment_method, COALESCE(p.payment_status,"unpaid") payment_status, p.amount_paid, p.paid_at FROM orders o JOIN branches b ON b.id=o.branch_id LEFT JOIN payments p ON p.order_id=o.id WHERE o.id=?');
$stmt->execute([$id]);
$order = $stmt->fetch();
$items = $db->prepare('SELECT od.*, m.name menu_name, s.name sauce_name FROM order_details od JOIN menus m ON m.id=od.menu_id LEFT JOIN sauces s ON s.id=od.sauce_id WHERE od.order_id=?');
$items->execute([$id]);
$pageTitle = 'Detail Pesanan';
$bodyClass = 'admin-layout';
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../includes/sidebar-admin.php';
?>
<section class="content-with-sidebar">
    <div class="page-title"><h1><?= e($order['order_code'] ?? 'Pesanan') ?></h1><span class="badge <?= get_status_color($order['status'] ?? '') ?>"><?= get_status_label($order['status'] ?? '') ?></span></div>
    <div class="grid grid-2">
        <div class="card"><h2>Customer</h2><p><?= e($order['customer_name'] ?? '') ?></p><p><?= e($order['customer_phone'] ?? '') ?></p><p><?= e($order['order_type'] ?? '') ?></p></div>
        <div class="card"><h2>Payment</h2><p><?= e($order['payment_method'] ?? '-') ?> - <?= e($order['payment_status'] ?? 'unpaid') ?></p><p>Paid: <?= format_rupiah((float) ($order['amount_paid'] ?? 0)) ?></p><p><?= e($order['paid_at'] ?? '-') ?></p></div>
    </div>
    <div class="table-wrap section"><table><thead><tr><th>Item</th><th>Saus & Level</th><th>Catatan</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody><?php foreach ($items->fetchAll() as $i): ?><tr><td><?= e($i['menu_name']) ?></td><td><?= e($i['sauce_name'] ?? '-') ?> <?= (isset($i['spice_level']) && $i['spice_level'] !== '' && $i['spice_level'] !== '0') ? '<span style="color:#b29500;font-weight:700;">(Lvl ' . e($i['spice_level']) . ')</span>' : '' ?></td><td style="font-style:italic;color:var(--secondary);"><?= !empty($i['notes']) ? '"' . e($i['notes']) . '"' : '-' ?></td><td><?= (int) $i['quantity'] ?></td><td><?= format_rupiah((float) $i['subtotal']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <h2>Total <?= format_rupiah((float) ($order['total'] ?? 0)) ?></h2>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
