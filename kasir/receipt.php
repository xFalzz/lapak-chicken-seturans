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
$orderItems = $items->fetchAll();
$amountPaid = (float) ($order['amount_paid'] ?? $order['total'] ?? 0);
$change = max(0, $amountPaid - (float) ($order['total'] ?? 0));
$pageTitle = 'Receipt';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar">
    <!-- Receipt Card -->
    <div class="receipt">
        <!-- Header -->
        <div class="receipt-header">
            <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:12px;">
                <span style="width:36px;height:36px;background:var(--primary-container);color:var(--on-primary-container);border-radius:10px;display:grid;place-items:center;font-size:0.85rem;font-weight:800;">LC</span>
                <h2><?= APP_NAME ?></h2>
            </div>
            <p>
                <?= e($order['branch_name'] ?? '') ?><br>
                <?= e($order['address'] ?? '') ?><br>
                <?= e($order['phone'] ?? '') ?>
            </p>
        </div>

        <!-- Order Info -->
        <div class="receipt-info">
            <p><strong><?= e($order['order_code'] ?? '') ?></strong></p>
            <p><?= e($order['created_at'] ?? '') ?></p>
            <p>Tipe: <strong><?= e($order['order_type'] ?? '') ?></strong></p>
            <p>Customer: <strong><?= e($order['customer_name'] ?? '') ?></strong></p>
        </div>

        <!-- Items -->
        <?php foreach ($orderItems as $i): ?>
        <div class="receipt-item">
            <div class="receipt-item-name">
                <?= e($i['menu_name']) ?>
                <?php if ($i['sauce_name']): ?> (<?= e($i['sauce_name']) ?>)<?php endif; ?>
                <?php if (isset($i['spice_level']) && $i['spice_level'] !== '' && $i['spice_level'] !== '0'): ?>
                    [Level: <?= e($i['spice_level']) ?>]
                <?php endif; ?>
            </div>
            <?php if (!empty($i['notes'])): ?>
                <div style="font-size:0.8rem;color:var(--secondary);font-style:italic;margin-bottom:2px;">Catatan: "<?= e($i['notes']) ?>"</div>
            <?php endif; ?>
            <div class="receipt-item-calc">
                <span><?= (int) $i['quantity'] ?> x <?= format_rupiah((float) $i['price'] + (float) $i['price_extra']) ?></span>
                <span style="font-weight:700;color:var(--on-surface);"><?= format_rupiah((float) $i['subtotal']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Total -->
        <div class="receipt-total">
            <div class="receipt-total-row main">
                <span>TOTAL</span>
                <span><?= format_rupiah((float) ($order['total'] ?? 0)) ?></span>
            </div>
            <div class="receipt-total-row">
                <span style="color:var(--secondary);"><?= e($order['payment_method'] ?? '') ?></span>
                <span><?= format_rupiah($amountPaid) ?></span>
            </div>
            <div class="receipt-total-row">
                <span style="color:var(--secondary);">Kembali</span>
                <span style="color:var(--success);font-weight:700;"><?= format_rupiah($change) ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <p style="font-weight:700;margin-bottom:4px;">Terima kasih telah memesan! 🍗</p>
            <p>Semoga makanannya memuaskan</p>
        </div>
    </div>

    <!-- Print Actions -->
    <div class="print-actions" style="text-align:center;margin-top:24px;display:flex;gap:12px;justify-content:center;">
        <button class="btn btn-primary" onclick="window.print()" style="border-radius:var(--radius-md);padding:12px 32px;">
            <i class="fa-solid fa-print"></i> Cetak Struk
        </button>
        <a class="btn btn-outline" href="index.php" style="border-radius:var(--radius-md);padding:12px 32px;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Antrian
        </a>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
