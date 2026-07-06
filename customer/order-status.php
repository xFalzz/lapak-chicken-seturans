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

<section class="section" style="background:var(--surface);padding-top:60px;min-height:70vh;">
    <div class="container" style="max-width:1000px;">
        <div style="display:flex;gap:48px;align-items:center;">
            
            <!-- Left: Success Message -->
            <div style="flex:1;">
                <div style="width:120px;height:120px;background:var(--primary-container);color:var(--on-primary-container);border-radius:50%;display:grid;place-items:center;font-size:3.5rem;margin-bottom:32px;box-shadow:0 12px 32px rgba(255,253,0,0.3);">
                    <i class="fa-solid fa-check"></i>
                </div>

                <h1 style="font-size:3rem;margin-bottom:16px;font-weight:900;line-height:1.1;">Yey, Pesanan<br>Berhasil Dibuat!</h1>
                <p style="color:var(--secondary);font-size:1.1rem;margin-bottom:40px;line-height:1.6;max-width:400px;">
                    Pesananmu sedang kami siapkan dengan penuh cinta. Duduk manis dan tunggu ya!
                </p>

                <div style="display:flex;gap:16px;">
                    <a href="<?= base_url('customer/track-order.php?code=' . urlencode($order['order_code'])) ?>" class="btn btn-primary" style="border-radius:16px;padding:16px 32px;font-size:1.1rem;font-weight:800;box-shadow:0 8px 24px rgba(255,253,0,0.2);">
                        Lacak Pesanan
                    </a>
                    <a href="<?= base_url('index.php') ?>" class="btn btn-outline" style="border-radius:16px;padding:16px 32px;font-size:1.1rem;font-weight:800;border:2px solid var(--outline-variant);">
                        Ke Beranda
                    </a>
                </div>
            </div>

            <!-- Right: Order Card -->
            <div style="flex:1;">
                <div class="success-card" style="border-radius:32px;padding:40px;box-shadow:0 24px 64px rgba(0,0,0,0.06);border:1px solid var(--outline-variant);background:white;">
                    <div style="text-align:center;margin-bottom:32px;padding-bottom:24px;border-bottom:1px dashed var(--outline);">
                        <span style="display:block;color:var(--secondary);font-size:0.95rem;margin-bottom:8px;">ID Pesanan</span>
                        <strong style="font-size:1.8rem;color:var(--on-surface);font-family:monospace;letter-spacing:2px;"><?= e($order['order_code']) ?></strong>
                        
                        <div style="margin-top:16px;">
                            <span class="badge <?= get_status_color($order['status']) ?>" style="padding:6px 16px;font-size:0.95rem;border-radius:99px;">
                                <?= get_status_label($order['status']) ?>
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom:32px;">
                        <h4 style="font-size:1rem;font-weight:800;margin-bottom:16px;">Rincian Pesanan</h4>
                        <div style="display:flex;flex-direction:column;gap:12px;">
                            <?php foreach($items->fetchAll() as $item): ?>
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                <div style="display:flex;gap:12px;">
                                    <span style="font-weight:700;color:var(--primary);"><?= (int)$item['quantity'] ?>x</span>
                                    <div>
                                        <span style="display:block;font-weight:600;font-size:0.95rem;"><?= e($item['menu_name']) ?></span>
                                        <div style="font-size:0.85rem;color:var(--secondary);display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                                            <?php if($item['sauce_name']): ?>
                                                <span>Saus <?= e($item['sauce_name']) ?></span>
                                            <?php endif; ?>
                                            <?php if (isset($item['spice_level']) && $item['spice_level'] !== '' && $item['spice_level'] !== '0'): ?>
                                                <span style="background:rgba(255, 214, 0, 0.15);color:#b29500;padding:2px 8px;border-radius:6px;font-weight:700;font-size:0.75rem;">
                                                    <i class="fa-solid fa-pepper-hot"></i> Level <?= e($item['spice_level']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['notes'])): ?>
                                            <div style="background:var(--surface-container-low);padding:4px 8px;border-radius:6px;font-size:0.8rem;color:var(--on-surface-variant);margin-top:4px;border:1px dashed var(--outline-variant);">
                                                <i class="fa-regular fa-note-sticky" style="color:var(--primary);"></i>
                                                <span style="font-style:italic;">"<?= e($item['notes']) ?>"</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span style="font-weight:700;font-size:0.95rem;"><?= format_rupiah((float)$item['subtotal']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:24px;border-top:1px dashed var(--outline);">
                        <span style="color:var(--secondary);font-size:1.1rem;font-weight:600;">Total Dibayar</span>
                        <strong style="color:var(--primary);font-size:1.6rem;font-weight:900;"><?= format_rupiah((float) $order['total']) ?></strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
