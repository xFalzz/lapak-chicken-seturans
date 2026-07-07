<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$db = db();
$code = sanitize($_GET['code'] ?? '');
$stmt = $db->prepare('SELECT o.*, b.name branch_name, b.address branch_address, COALESCE(p.payment_method, "-") payment_method, COALESCE(p.payment_status, "unpaid") payment_status FROM orders o JOIN branches b ON b.id = o.branch_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.order_code = ? AND o.user_id = ?');
$stmt->execute([$code, current_user()['id']]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Pesanan tidak ditemukan');
    redirect(base_url('customer/orders.php'));
}

$items = $db->prepare('SELECT od.*, m.name menu_name, s.name sauce_name FROM order_details od JOIN menus m ON m.id = od.menu_id LEFT JOIN sauces s ON s.id = od.sauce_id WHERE od.order_id = ?');
$items->execute([$order['id']]);
$orderItems = $items->fetchAll();

$steps = [
    'pending' => ['label' => 'Pesanan Diterima', 'icon' => 'fa-clock', 'desc' => 'Pesanan Anda sedang menunggu konfirmasi dari restoran.'],
    'confirmed' => ['label' => 'Dikonfirmasi', 'icon' => 'fa-check-double', 'desc' => 'Pesanan telah dikonfirmasi dan akan segera dimasak.'],
    'cooking' => ['label' => 'Sedang Dimasak', 'icon' => 'fa-fire-burner', 'desc' => 'Koki kami sedang mempersiapkan pesanan Anda.'],
    'ready' => ['label' => 'Siap Diambil', 'icon' => 'fa-bell-concierge', 'desc' => 'Pesanan Anda sudah siap! Silakan ambil di counter.'],
    'completed' => ['label' => 'Selesai', 'icon' => 'fa-flag-checkered', 'desc' => 'Pesanan telah selesai. Terima kasih!'],
];
$statuses = array_keys($steps);
$currentIndex = array_search($order['status'], $statuses);
if ($currentIndex === false && $order['status'] !== 'cancelled') $currentIndex = 0;

$pageTitle = 'Lacak Pesanan';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container">
        
        <a href="<?= base_url('customer/orders.php') ?>" style="display:inline-flex;align-items:center;gap:8px;color:var(--secondary);font-size:0.95rem;margin-bottom:16px;font-weight:600;">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat Pesanan
        </a>
        <h1 style="font-size:1.8rem;margin-bottom:32px;font-weight:800;">Lacak Pesanan <?= e($order['order_code']) ?></h1>

        <div class="grid grid-2" style="gap:32px;align-items:start;">
            
            <div>
                <div class="checkout-card" style="padding:0;overflow:hidden;margin-bottom:24px;">
                    <div style="height:300px;background:#e8e8e8;display:flex;align-items:center;justify-content:center;position:relative;">
                        <iframe 
                            src="https://www.openstreetmap.org/export/embed.html?bbox=110.39%2C-7.78%2C110.41%2C-7.76&layer=mapnik" 
                            style="width:100%;height:100%;border:0;"
                            loading="lazy"
                            title="Lokasi Cabang">
                        </iframe>
                    </div>
                    <div style="padding:20px;">
                        <div class="address-box" style="border:none;padding:0;">
                            <div class="address-icon">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                <strong style="display:block;margin-bottom:4px;"><?= e($order['branch_name']) ?></strong>
                                <span style="font-size:0.9rem;color:var(--secondary);"><?= e($order['branch_address'] ?? 'Jl. Seturan Raya No.12, Yogyakarta') ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkout-card" style="text-align:center;padding:32px;">
                    <div style="font-size:2.5rem;font-weight:800;color:var(--on-surface);margin-bottom:8px;">
                        <?php 
                        $eta = match($order['status']) {
                            'pending' => '~20 menit',
                            'confirmed' => '~15 menit',
                            'cooking' => '~10 menit',
                            'ready' => 'Siap!',
                            'completed' => 'Selesai',
                            default => '-'
                        };
                        echo $eta;
                        ?>
                    </div>
                    <p style="color:var(--secondary);">Estimasi waktu pesanan</p>
                </div>
            </div>

            <div>
                <div class="checkout-card">
                    <h2 class="checkout-card-title">Status Pesanan</h2>
                    
                    <?php if ($order['status'] === 'cancelled'): ?>
                        <div style="text-align:center;padding:32px;">
                            <div style="font-size:3rem;color:var(--error);margin-bottom:16px;"><i class="fa-solid fa-circle-xmark"></i></div>
                            <h3 style="color:var(--error);margin-bottom:8px;">Pesanan Dibatalkan</h3>
                            <p style="color:var(--secondary);">Pesanan ini telah dibatalkan.</p>
                        </div>
                    <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:0;position:relative;">
                            <?php foreach ($steps as $key => $step):
                                $stepIndex = array_search($key, $statuses);
                                $isCompleted = $order['status'] === 'completed' || $stepIndex < $currentIndex;
                                $isActive = $stepIndex === $currentIndex;
                                $isPending = !$isCompleted && !$isActive;
                            ?>
                                <div style="display:flex;gap:20px;position:relative;padding-bottom:32px;">
                                    <?php if ($stepIndex < count($steps) - 1): ?>
                                        <div style="position:absolute;left:19px;top:40px;width:2px;height:calc(100% - 20px);
                                            background:<?= $isCompleted ? '#B29500' : 'var(--outline-variant)' ?>;"></div>
                                    <?php endif; ?>
                                    
                                    <div style="width:40px;height:40px;border-radius:50%;display:grid;place-items:center;flex-shrink:0;font-size:1rem;z-index:1;
                                        <?php if ($isCompleted || $isActive): ?>
                                            background:var(--primary-container);color:var(--on-primary-container);
                                        <?php else: ?>
                                            background:var(--surface-container-low);color:var(--outline);border:2px solid var(--outline-variant);
                                        <?php endif; ?>">
                                        <i class="fa-solid <?= $isCompleted ? 'fa-check' : $step['icon'] ?>"></i>
                                    </div>

                                    <div style="padding-top:4px;">
                                        <strong style="display:block;font-size:1rem;margin-bottom:4px;
                                            color:<?= ($isCompleted || $isActive) ? 'var(--on-surface)' : 'var(--outline)' ?>;">
                                            <?= $step['label'] ?>
                                        </strong>
                                        <?php if ($isActive): ?>
                                            <p style="color:var(--secondary);font-size:0.9rem;line-height:1.5;"><?= $step['desc'] ?></p>
                                        <?php endif; ?>
                                        <?php if ($isCompleted): ?>
                                            <span style="font-size:0.8rem;color:var(--secondary);"><i class="fa-solid fa-check" style="color:#B29500;margin-right:4px;"></i> Selesai</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="checkout-card" style="margin-top:24px;">
                    <h2 class="checkout-card-title">Detail Pesanan</h2>
                    <?php foreach ($orderItems as $item): ?>
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--surface-container);">
                            <div>
                                <span style="color:var(--on-surface);font-weight:700;"><?= (int)$item['quantity'] ?>x <?= e($item['menu_name']) ?></span>
                                <div style="font-size:0.85rem;color:var(--secondary);display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-top:2px;">
                                    <?php if(!empty($item['sauce_name'])): ?>
                                        <span>Saus <?= e($item['sauce_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (isset($item['spice_level']) && $item['spice_level'] !== '' && $item['spice_level'] !== '0'): ?>
                                        <span style="background:rgba(255, 214, 0, 0.15);color:#b29500;padding:2px 8px;border-radius:6px;font-weight:700;font-size:0.75rem;">
                                            <i class="fa-solid fa-pepper-hot"></i> Level <?= e($item['spice_level']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($item['notes'])): ?>
                                    <div style="background:var(--surface-container-low);padding:4px 8px;border-radius:6px;font-size:0.8rem;color:var(--on-surface-variant);margin-top:4px;border:1px dashed var(--outline-variant);display:inline-flex;align-items:center;gap:4px;">
                                        <i class="fa-regular fa-note-sticky" style="color:var(--primary);"></i>
                                        <span style="font-style:italic;">"<?= e($item['notes']) ?>"</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span style="font-weight:600;"><?= format_rupiah((float)$item['subtotal']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div style="display:flex;justify-content:space-between;padding-top:16px;margin-top:8px;font-weight:800;font-size:1.1rem;">
                        <span>Total</span>
                        <span><?= format_rupiah((float)$order['total']) ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
