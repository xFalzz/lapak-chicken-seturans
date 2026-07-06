<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$db = db();
$user = current_user();

// Tab filter
$statusFilter = sanitize($_GET['status'] ?? 'all');
$validStatuses = ['all', 'pending', 'confirmed', 'cooking', 'ready', 'completed', 'cancelled'];
if (!in_array($statusFilter, $validStatuses)) $statusFilter = 'all';

// Get orders
$sql = 'SELECT o.*, b.name branch_name, COALESCE(p.payment_method, "-") payment_method FROM orders o JOIN branches b ON b.id = o.branch_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.user_id = ?';
$params = [$user['id']];
if ($statusFilter !== 'all') {
    $sql .= ' AND o.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY o.created_at DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Riwayat Pesanan';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container" style="max-width:900px;">

        <div style="margin-bottom:32px;">
            <h1 style="font-size:1.8rem;margin-bottom:8px;font-weight:800;">Riwayat Pesanan</h1>
            <p style="color:var(--secondary);font-size:1rem;">Lihat semua riwayat pesanan Anda di Lapak Chicken.</p>
        </div>

        <!-- Status Tabs -->
        <div style="display:flex;gap:12px;overflow-x:auto;padding-bottom:12px;margin-bottom:32px;border-bottom:1px solid var(--outline-variant);">
            <?php
            $tabs = [
                'all' => 'Semua Pesanan',
                'pending' => 'Menunggu',
                'confirmed' => 'Dikonfirmasi',
                'cooking' => 'Dimasak',
                'ready' => 'Siap Diambil',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
            ];
            foreach ($tabs as $key => $label):
            ?>
                <a href="?status=<?= $key ?>" 
                   style="padding:10px 20px;border-radius:99px;font-weight:600;font-size:0.9rem;white-space:nowrap;
                          <?= $statusFilter === $key 
                            ? 'background:var(--on-surface);color:var(--surface);' 
                            : 'background:var(--surface-container-low);color:var(--secondary);' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:64px 20px;">
                <div style="font-size:3rem;color:var(--outline);margin-bottom:16px;"><i class="fa-solid fa-receipt"></i></div>
                <h3 style="margin-bottom:8px;">Belum Ada Pesanan</h3>
                <p style="color:var(--secondary);margin-bottom:24px;">Yuk mulai pesan ayam crispy favoritmu!</p>
                <a href="<?= base_url('customer/menu.php') ?>" class="btn btn-primary" style="border-radius:99px;">Pesan Sekarang</a>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <?php foreach ($orders as $order): 
                    // Get items for this order
                    $itemStmt = $db->prepare('SELECT od.*, m.name menu_name FROM order_details od JOIN menus m ON m.id = od.menu_id WHERE od.order_id = ? LIMIT 3');
                    $itemStmt->execute([$order['id']]);
                    $orderItems = $itemStmt->fetchAll();
                ?>
                    <div class="checkout-card" style="padding:20px 24px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <div style="display:flex;align-items:center;gap:16px;">
                                <div style="width:44px;height:44px;background:var(--primary-container);border-radius:12px;display:grid;place-items:center;color:var(--on-primary-container);font-size:1.1rem;">
                                    <i class="fa-solid fa-receipt"></i>
                                </div>
                                <div>
                                    <strong style="display:block;font-size:1rem;"><?= e($order['order_code']) ?></strong>
                                    <span style="font-size:0.85rem;color:var(--secondary);"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                            </div>
                            <div style="display:flex;align-items:center;gap:16px;">
                                <span class="badge <?= get_status_color($order['status']) ?>" style="padding:6px 14px;font-size:0.8rem;"><?= get_status_label($order['status']) ?></span>
                            </div>
                        </div>

                        <!-- Items preview -->
                        <?php foreach ($orderItems as $item): ?>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;font-size:0.95rem;">
                                <span style="color:var(--secondary);"><?= (int)$item['quantity'] ?>x <?= e($item['menu_name']) ?></span>
                                <span style="font-weight:600;"><?= format_rupiah((float)$item['subtotal']) ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div style="display:flex;justify-content:space-between;align-items:center;padding-top:16px;margin-top:12px;border-top:1px dashed var(--outline-variant);">
                            <div>
                                <span style="color:var(--secondary);font-size:0.85rem;">Total Pembayaran</span>
                                <strong style="display:block;font-size:1.1rem;"><?= format_rupiah((float)$order['total']) ?></strong>
                            </div>
                            <div style="display:flex;gap:12px;">
                                <?php if (in_array($order['status'], ['pending', 'confirmed', 'cooking', 'ready'])): ?>
                                    <a href="<?= base_url('customer/track-order.php?code=' . urlencode($order['order_code'])) ?>" class="btn btn-primary" style="border-radius:99px;padding:8px 20px;font-size:0.9rem;">
                                        Lacak Pesanan
                                    </a>
                                <?php endif; ?>
                                <a href="<?= base_url('customer/order-status.php?code=' . urlencode($order['order_code'])) ?>" class="btn btn-outline" style="border-radius:99px;padding:8px 20px;font-size:0.9rem;">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
