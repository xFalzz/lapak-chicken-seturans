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
$orderItems = $items->fetchAll();
$pageTitle = 'Proses Pembayaran';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
        <div>
            <a href="index.php" style="font-size:0.85rem;color:var(--secondary);display:flex;align-items:center;gap:6px;margin-bottom:8px;font-weight:600;">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Antrian
            </a>
            <h1 style="font-size:1.6rem;font-weight:800;"><?= e($order['order_code'] ?? '') ?></h1>
        </div>
        <div class="payment-total"><?= format_rupiah((float) ($order['total'] ?? 0)) ?></div>
    </div>

    <div class="grid grid-2">
        <div class="order-detail-card">
            <h2><i class="fa-solid fa-receipt" style="margin-right:8px;color:var(--secondary);"></i> Detail Pesanan</h2>
            
            <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
                <span class="badge badge-blue"><i class="fa-solid fa-store" style="margin-right:4px;"></i> <?= e($order['branch_name'] ?? '') ?></span>
                <span class="badge badge-gray"><i class="fa-solid fa-user" style="margin-right:4px;"></i> <?= e($order['customer_name'] ?? '') ?></span>
                <span class="badge badge-gray"><i class="fa-solid fa-tag" style="margin-right:4px;"></i> <?= e($order['order_type'] ?? '') ?></span>
            </div>

            <?php foreach ($orderItems as $i): ?>
            <div class="order-item">
                <div class="order-item-main">
                    <span class="order-item-name"><?= (int) $i['quantity'] ?>x <?= e($i['menu_name']) ?></span>
                    <span class="order-item-subtotal"><?= format_rupiah((float) $i['subtotal']) ?></span>
                </div>
                <div class="order-item-extras">
                    <?php if ($i['sauce_name']): ?>
                        <span class="badge badge-yellow" style="font-size:0.75rem;">🌶️ <?= e($i['sauce_name']) ?></span>
                    <?php endif; ?>
                    <?php if (isset($i['spice_level']) && $i['spice_level'] !== '' && $i['spice_level'] !== '0'): ?>
                        <span class="badge badge-red" style="font-size:0.75rem;">🔥 Level <?= e($i['spice_level']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($i['notes'])): ?>
                    <div class="order-item-note"><i class="fa-solid fa-message" style="margin-right:4px;"></i> "<?= e($i['notes']) ?>"</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="order-detail-card">
            <form data-pay-form>
                <input type="hidden" name="order_id" value="<?= $id ?>">
                
                <h2><i class="fa-solid fa-credit-card" style="margin-right:8px;color:var(--secondary);"></i> Pembayaran</h2>

                <div style="margin-bottom:20px;">
                    <label style="font-size:0.8rem;font-weight:700;color:var(--secondary);text-transform:uppercase;letter-spacing:0.05em;display:block;margin-bottom:10px;">Metode Pembayaran</label>
                    <div class="payment-methods">
                        <?php 
                        $methods = [
                            ['Cash', 'fa-money-bill-wave', true],
                            ['QRIS', 'fa-qrcode', false],
                            ['Transfer Bank', 'fa-building-columns', false],
                            ['COD', 'fa-truck', false]
                        ];
                        foreach ($methods as $m): ?>
                        <label class="payment-method-label">
                            <input type="radio" name="payment_method" value="<?= e($m[0]) ?>" <?= $m[2] ? 'checked' : '' ?>>
                            <i class="fa-solid <?= $m[1] ?>"></i>
                            <span style="font-weight:600;"><?= e($m[0]) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-field" style="margin-bottom:16px;">
                    <label>Uang Diterima</label>
                    <input type="number" name="amount_paid" data-cash-input data-total="<?= e($order['total'] ?? 0) ?>" value="<?= e($order['total'] ?? 0) ?>" style="font-size:1.1rem;font-weight:700;">
                </div>

                <div class="form-field" style="margin-bottom:16px;">
                    <label>Reference No (Opsional)</label>
                    <input name="reference_no" placeholder="Contoh: TRF-123456">
                </div>

                <div class="change-display">
                    <label>Kembalian</label>
                    <strong data-change>Rp 0</strong>
                </div>

                <button class="btn btn-primary" style="width:100%;justify-content:center;min-height:50px;font-size:1rem;border-radius:var(--radius-md);">
                    <i class="fa-solid fa-check-circle"></i> Konfirmasi Pembayaran
                </button>
            </form>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/js/kasir.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
