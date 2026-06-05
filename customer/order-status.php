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
$pageTitle = 'Status Pesanan ' . $order['order_code'];
require __DIR__ . '/../includes/header.php';

$steps = [
    'pending' => ['label' => 'Menunggu', 'icon' => 'fa-clock'],
    'confirmed' => ['label' => 'Dikonfirmasi', 'icon' => 'fa-check-double'],
    'cooking' => ['label' => 'Dimasak', 'icon' => 'fa-fire-burner'],
    'ready' => ['label' => 'Siap', 'icon' => 'fa-bell-concierge'],
    'completed' => ['label' => 'Selesai', 'icon' => 'fa-flag-checkered'],
];
$statuses = array_keys($steps);
$currentIndex = array_search($order['status'], $statuses);
if ($currentIndex === false && $order['status'] !== 'cancelled') $currentIndex = 0;
$progressPercent = $order['status'] === 'cancelled' ? 0 : ($currentIndex / (count($steps) - 1)) * 100;
?>
<section class="section" data-order-code="<?= e($order['order_code']) ?>" data-live-status-container>
    <div class="container">
        <div class="page-title">
            <div>
                <h1 style="margin-bottom: 8px;">Pesanan <?= e($order['order_code']) ?></h1>
                <p class="muted" style="margin: 0; font-size: 0.95rem;">
                    <i class="fa-solid fa-store" style="margin-right: 4px;"></i> <?= e($order['branch_name']) ?>
                </p>
            </div>
            <span class="badge <?= get_status_color($order['status']) ?>" style="font-size: 1rem; padding: 8px 16px;" data-live-status><?= get_status_label($order['status']) ?></span>
        </div>
        
        <div class="card" style="padding: 32px; margin-bottom: 24px;">
            <div class="stepper-wrap" style="margin-top: 16px; margin-bottom: 48px;">
                <div class="stepper" id="orderStepper">
                    <div class="stepper-progress" style="width: <?= $progressPercent ?>%; background: <?= $order['status'] === 'cancelled' ? 'var(--danger)' : 'var(--primary)' ?>;" data-stepper-progress></div>
                    <?php foreach ($steps as $key => $step): 
                        $stepIndex = array_search($key, $statuses);
                        $isCompleted = $order['status'] === 'completed' || $stepIndex < $currentIndex;
                        $isActive = $stepIndex === $currentIndex;
                        
                        $stepClass = '';
                        if ($order['status'] === 'cancelled') {
                            $stepClass = 'cancelled';
                        } else if ($isCompleted) {
                            $stepClass = 'completed';
                        } else if ($isActive) {
                            $stepClass = 'active';
                        }
                    ?>
                        <div class="step <?= $stepClass ?>" data-step data-step-index="<?= $stepIndex ?>">
                            <div class="step-icon"><i class="fa-solid <?= $step['icon'] ?>"></i></div>
                            <div class="step-label"><?= e($step['label']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($order['status'] === 'cancelled'): ?>
                    <div style="text-align: center; margin-top: 24px;">
                        <span class="badge badge-red" style="font-size: 1.1rem; padding: 12px 24px;"><i class="fa-solid fa-ban" style="margin-right: 8px;"></i> Pesanan Dibatalkan</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-2" style="gap: 32px;">
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">Informasi Pesanan</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; gap: 16px; align-items: flex-start;">
                            <div style="width: 40px; height: 40px; background: var(--gray-light); border-radius: 50%; display: grid; place-items: center; color: var(--gray-dark);">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                            <div>
                                <span class="muted" style="display: block; font-size: 0.85rem;">Tipe Pesanan</span>
                                <strong style="text-transform: uppercase;"><?= str_replace('_', ' ', e($order['order_type'])) ?></strong>
                            </div>
                        </div>
                        <div style="display: flex; gap: 16px; align-items: flex-start;">
                            <div style="width: 40px; height: 40px; background: var(--gray-light); border-radius: 50%; display: grid; place-items: center; color: var(--gray-dark);">
                                <i class="fa-solid <?= $order['payment_status'] === 'paid' ? 'fa-check-circle' : 'fa-wallet' ?>"></i>
                            </div>
                            <div>
                                <span class="muted" style="display: block; font-size: 0.85rem;">Status Pembayaran</span>
                                <span class="badge <?= $order['payment_status'] === 'paid' ? 'badge-green' : 'badge-orange' ?>">
                                    <?= strtoupper(e($order['payment_status'])) ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($order['notes']): ?>
                            <div style="display: flex; gap: 16px; align-items: flex-start;">
                                <div style="width: 40px; height: 40px; background: var(--gray-light); border-radius: 50%; display: grid; place-items: center; color: var(--gray-dark);">
                                    <i class="fa-solid fa-note-sticky"></i>
                                </div>
                                <div>
                                    <span class="muted" style="display: block; font-size: 0.85rem;">Catatan</span>
                                    <span><?= e($order['notes']) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid rgba(0,0,0,0.05);">Daftar Item</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                        <?php foreach ($items->fetchAll() as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px dashed rgba(0,0,0,0.1);">
                                <div>
                                    <strong style="display: block;"><?= (int) $item['quantity'] ?>x <?= e($item['menu_name']) ?></strong>
                                    <span class="muted" style="font-size: 0.85rem;"><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?></span>
                                    <?php if ($item['notes']): ?>
                                        <span class="muted" style="display: block; font-size: 0.85rem; font-style: italic;">"<?= e($item['notes']) ?>"</span>
                                    <?php endif; ?>
                                </div>
                                <strong style="font-weight: 600;"><?= format_rupiah((float) $item['subtotal']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-total" style="display: flex; justify-content: space-between; font-size: 1.3rem; color: var(--primary-dark); font-weight: 700; padding-top: 16px; border-top: 2px dashed rgba(0,0,0,0.1);">
                        <span>Total Pesanan</span>
                        <span><?= format_rupiah((float) $order['total']) ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($order['status'] === 'completed' && user_role() === 'customer'): ?>
                <hr style="margin: 32px 0; border: 0; border-top: 1px solid rgba(0,0,0,0.05);">
                <div style="background: var(--bg-color); padding: 24px; border-radius: var(--radius-md); text-align: center;">
                    <h3 style="margin-top: 0; margin-bottom: 8px;"><i class="fa-solid fa-star" style="color: var(--primary);"></i> Berikan Review Anda</h3>
                    <p class="muted" style="margin-bottom: 24px;">Bagaimana kualitas pesanan Anda? Review Anda sangat berarti bagi kami.</p>
                    
                    <form class="form-grid" method="post" action="<?= base_url('api/order.php?action=review') ?>" style="max-width: 400px; margin: 0 auto; text-align: left;">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <div class="form-field">
                            <label>Rating (1-5)</label>
                            <div style="display: flex; gap: 8px; justify-content: space-between;">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <label style="flex: 1; text-align: center; background: var(--white); padding: 12px 0; border-radius: var(--radius-sm); border: 1px solid rgba(0,0,0,0.1); cursor: pointer; transition: var(--transition);">
                                        <input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> style="display: none;">
                                        <div style="font-weight: 700; font-size: 1.2rem; color: <?= $i >= 4 ? 'var(--success)' : ($i === 3 ? 'var(--warning)' : 'var(--danger)') ?>;"><?= $i ?> <i class="fa-solid fa-star"></i></div>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Komentar</label>
                            <textarea name="comment" placeholder="Beritahu kami apa yang Anda sukai atau yang perlu kami tingkatkan..." style="min-height: 80px;"></textarea>
                        </div>
                        <button class="btn btn-primary" style="width: 100%;"><i class="fa-solid fa-paper-plane"></i> Kirim Review</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/js/order.js') ?>?v=1.1"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
