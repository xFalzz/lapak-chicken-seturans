<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['kasir', 'admin']);
$db = db();
$id = (int) ($_GET['order_id'] ?? 0);
$stmt = $db->prepare('SELECT o.*, b.name branch_name, b.address, b.phone, p.payment_method, p.amount_paid, p.paid_at FROM orders o JOIN branches b ON b.id=o.branch_id LEFT JOIN payments p ON p.order_id=o.id WHERE o.id=?');
$stmt->execute([$id]);
$order = $stmt->fetch();
$items = $db->prepare('SELECT od.*, m.name menu_name, m.price, s.name sauce_name, COALESCE(s.price_extra,0) price_extra FROM order_details od JOIN menus m ON m.id=od.menu_id LEFT JOIN sauces s ON s.id=od.sauce_id WHERE od.order_id=?');
$items->execute([$id]);
$amountPaid = (float) ($order['amount_paid'] ?? $order['total'] ?? 0);
$change = max(0, $amountPaid - (float) ($order['total'] ?? 0));
$pageTitle = 'Receipt';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar">
    <div class="card receipt">
        <h2><?= APP_NAME ?></h2><p><?= e($order['branch_name'] ?? '') ?><br><?= e($order['address'] ?? '') ?><br><?= e($order['phone'] ?? '') ?></p><hr>
        <p><?= e($order['order_code'] ?? '') ?><br><?= e($order['created_at'] ?? '') ?><br><?= e($order['order_type'] ?? '') ?></p><hr>
        <?php foreach ($items->fetchAll() as $i): ?><p><?= e($i['menu_name']) ?> <?= $i['sauce_name'] ? '(' . e($i['sauce_name']) . ')' : '' ?><br><?= (int) $i['quantity'] ?> x <?= format_rupiah((float) $i['price'] + (float) $i['price_extra']) ?> = <?= format_rupiah((float) $i['subtotal']) ?></p><?php endforeach; ?><hr>
        <p>TOTAL: <strong><?= format_rupiah((float) ($order['total'] ?? 0)) ?></strong><br><?= e($order['payment_method'] ?? '') ?>: <?= format_rupiah($amountPaid) ?><br>Kembali: <?= format_rupiah($change) ?></p><hr><p>Terima kasih!</p>
    </div>
    <div class="print-actions" style="text-align:center"><button class="btn btn-primary" onclick="window.print()">Cetak Struk</button> <a class="btn btn-outline" href="index.php">Kembali ke Antrian</a></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
