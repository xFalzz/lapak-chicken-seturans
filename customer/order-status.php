<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$code = sanitize($_GET['code'] ?? '');
$stmt = $db->prepare('SELECT o.*, b.name branch_name, COALESCE(p.payment_status, "unpaid") payment_status FROM orders o JOIN branches b ON b.id = o.branch_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.order_code = ?');
$stmt->execute([$code]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Pesanan tidak ditemukan');
    redirect(base_url('customer/menu.php'));
}
$items = $db->prepare('SELECT od.*, m.name menu_name, s.name sauce_name FROM order_details od JOIN menus m ON m.id = od.menu_id LEFT JOIN sauces s ON s.id = od.sauce_id WHERE od.order_id = ?');
$items->execute([$order['id']]);
$pageTitle = 'Pesanan Berhasil';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding-top:60px;min-height:70vh;display:flex;align-items:center;justify-content:center;">
    <div class="container" style="max-width:600px;text-align:center;">
        
        <!-- Success Icon -->
        <div class="success-page-icon">
            <i class="fa-solid fa-check"></i>
        </div>

        <h1 style="font-size:2rem;margin-bottom:12px;font-weight:800;">Hore! Pesanan Berhasil Dibuat</h1>
        <p style="color:var(--secondary);font-size:1.05rem;margin-bottom:40px;">
            Terima kasih! Pesanan Anda telah kami terima dan sedang diproses.
        </p>

        <!-- Order Card -->
        <div class="success-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:16px;border-bottom:1px dashed var(--outline);">
                <div>
                    <span style="display:block;color:var(--secondary);font-size:0.9rem;margin-bottom:4px;">ID Pesanan</span>
                    <strong style="font-size:1.1rem;color:var(--on-surface);"><?= e($order['order_code']) ?></strong>
                </div>
                <div style="text-align:right;">
                    <span style="display:block;color:var(--secondary);font-size:0.9rem;margin-bottom:4px;">Status</span>
                    <span class="badge <?= get_status_color($order['status']) ?>" style="padding:4px 12px;font-size:0.85rem;">
                        <?= get_status_label($order['status']) ?>
                    </span>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <span style="color:var(--secondary);font-size:0.95rem;">Tipe Pesanan</span>
                <strong style="color:var(--on-surface);"><?= ucfirst(str_replace('_', ' ', $order['order_type'])) ?></strong>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="color:var(--secondary);font-size:0.95rem;">Total Pembayaran</span>
                <strong style="color:var(--on-surface);font-size:1.2rem;"><?= format_rupiah((float) $order['total']) ?></strong>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display:flex;flex-direction:column;gap:16px;">
            <a href="<?= base_url('customer/track-order.php?code=' . urlencode($order['order_code'])) ?>" class="btn btn-primary" style="width:100%;justify-content:center;border-radius:var(--radius-pill);padding:14px;font-size:1.05rem;">
                Lacak Pesanan
            </a>
            <a href="<?= base_url('index.php') ?>" class="btn btn-outline" style="width:100%;justify-content:center;border-radius:var(--radius-pill);padding:14px;font-size:1.05rem;background:transparent;">
                Kembali ke Beranda
            </a>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
