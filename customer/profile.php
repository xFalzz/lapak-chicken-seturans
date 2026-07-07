<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$db = db();
$user = current_user();
$orderCount = $db->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$orderCount->execute([$user['id']]);
$orderCount = (int) $orderCount->fetchColumn();

$totalSpent = $db->prepare('SELECT COALESCE(SUM(total), 0) FROM orders WHERE user_id = ? AND status != "cancelled"');
$totalSpent->execute([$user['id']]);
$totalSpent = (float) $totalSpent->fetchColumn();
$points = (int) ($totalSpent / 1000);
$recentOrders = $db->prepare('SELECT o.*, b.name branch_name FROM orders o JOIN branches b ON b.id = o.branch_id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT 3');
$recentOrders->execute([$user['id']]);
$recentOrders = $recentOrders->fetchAll();
$favorites = $db->prepare('
    SELECT m.id, m.name, m.price, m.image_url, COUNT(*) as order_count
    FROM order_details od
    JOIN orders o ON o.id = od.order_id
    JOIN menus m ON m.id = od.menu_id
    WHERE o.user_id = ?
    GROUP BY m.id
    ORDER BY order_count DESC
    LIMIT 4
');
$favorites->execute([$user['id']]);
$favorites = $favorites->fetchAll();

$pageTitle = 'Profil Saya';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container">

        <div class="checkout-card" style="margin-bottom:32px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:24px;flex-wrap:wrap;">
                <div style="display:flex;align-items:center;gap:24px;">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FFFD00&color=111&size=120&bold=true" 
                         alt="Avatar" 
                         style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-container);">
                    <div>
                        <h1 style="font-size:1.6rem;margin-bottom:4px;font-weight:800;"><?= e($user['name']) ?></h1>
                        <p style="color:var(--secondary);font-size:0.95rem;margin-bottom:8px;"><?= e($user['email']) ?></p>
                        <span class="badge badge-yellow" style="font-size:0.8rem;padding:4px 12px;">
                            <i class="fa-solid fa-crown" style="margin-right:4px;"></i> Member Aktif
                        </span>
                    </div>
                </div>
                <a href="#" class="btn btn-outline" style="border-radius:99px;padding:8px 24px;" onclick="toast('Fitur edit profil akan segera hadir!','info')">
                    <i class="fa-solid fa-pen"></i> Edit Profil
                </a>
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:32px;padding-top:24px;border-top:1px solid var(--outline-variant);">
                <div style="text-align:center;">
                    <div style="font-size:2rem;font-weight:800;color:var(--on-surface);"><?= $orderCount ?></div>
                    <div style="color:var(--secondary);font-size:0.9rem;margin-top:4px;">Total Pesanan</div>
                </div>
                <div style="text-align:center;border-left:1px solid var(--outline-variant);border-right:1px solid var(--outline-variant);">
                    <div style="font-size:2rem;font-weight:800;color:var(--on-surface);"><?= number_format($points, 0, ',', '.') ?></div>
                    <div style="color:var(--secondary);font-size:0.9rem;margin-top:4px;">Poin Terkumpul</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:2rem;font-weight:800;color:var(--on-surface);"><?= format_rupiah($totalSpent) ?></div>
                    <div style="color:var(--secondary);font-size:0.9rem;margin-top:4px;">Total Belanja</div>
                </div>
            </div>
        </div>

        <div class="checkout-card" style="margin-bottom:24px;">
            <h2 class="checkout-card-title">Informasi Pribadi</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <div>
                    <span style="display:block;font-size:0.85rem;color:var(--secondary);margin-bottom:4px;">Nama Lengkap</span>
                    <strong style="font-size:1rem;"><?= e($user['name']) ?></strong>
                </div>
                <div>
                    <span style="display:block;font-size:0.85rem;color:var(--secondary);margin-bottom:4px;">Email</span>
                    <strong style="font-size:1rem;"><?= e($user['email']) ?></strong>
                </div>
                <div>
                    <span style="display:block;font-size:0.85rem;color:var(--secondary);margin-bottom:4px;">Nomor HP</span>
                    <strong style="font-size:1rem;"><?= e($user['phone'] ?? '+62 821-xxxx-xxxx') ?></strong>
                </div>
                <div>
                    <span style="display:block;font-size:0.85rem;color:var(--secondary);margin-bottom:4px;">Alamat</span>
                    <strong style="font-size:1rem;">Jl. Seturan Raya, Yogyakarta</strong>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:40px;">
            
            <div class="checkout-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h2 class="checkout-card-title" style="margin-bottom:0;">Pesanan Terakhir</h2>
                    <a href="<?= base_url('customer/orders.php') ?>" style="color:#B29500;font-weight:700;font-size:0.9rem;">Lihat Semua</a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <p style="color:var(--secondary);text-align:center;padding:32px 0;">Belum ada pesanan.</p>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 0;border-bottom:1px solid var(--outline-variant);">
                            <div>
                                <strong style="display:block;font-size:0.95rem;margin-bottom:4px;"><?= e($order['order_code']) ?></strong>
                                <span style="font-size:0.85rem;color:var(--secondary);"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
                            </div>
                            <div style="text-align:right;">
                                <strong style="display:block;font-size:0.95rem;margin-bottom:4px;"><?= format_rupiah((float)$order['total']) ?></strong>
                                <span class="badge <?= get_status_color($order['status']) ?>" style="font-size:0.75rem;padding:2px 10px;"><?= get_status_label($order['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="checkout-card">
                <h2 class="checkout-card-title">Menu Favorit</h2>
                <?php if (empty($favorites)): ?>
                    <p style="color:var(--secondary);text-align:center;padding:32px 0;">Belum ada menu favorit.</p>
                <?php else: ?>
                    <?php foreach ($favorites as $fav): ?>
                        <div style="display:flex;align-items:center;gap:16px;padding:12px 0;border-bottom:1px solid var(--outline-variant);">
                            <div style="width:56px;height:56px;border-radius:12px;background:var(--surface-container);display:grid;place-items:center;overflow:hidden;flex-shrink:0;">
                                <?php if ($fav['image_url']): ?>
                                    <img src="<?= e(base_url($fav['image_url'])) ?>" style="width:100%;height:100%;object-fit:cover;">
                                <?php else: ?>
                                    <span style="font-size:1.5rem;">🍗</span>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1;">
                                <strong style="display:block;font-size:0.95rem;"><?= e($fav['name']) ?></strong>
                                <span style="font-size:0.85rem;color:var(--secondary);">Dipesan <?= (int)$fav['order_count'] ?>x</span>
                            </div>
                            <strong style="color:var(--on-surface);"><?= format_rupiah((float)$fav['price']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
