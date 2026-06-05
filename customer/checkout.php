<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$cart = get_cart($db);

// Redirect to cart if empty
if (!$cart['items']) {
    flash('error', 'Keranjang masih kosong');
    redirect(base_url('customer/menu.php'));
}

// Redirect to cart if accessed via GET without POST data (unless testing or reloading)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($_POST)) {
    // If we want to allow refresh, we can store in session. But for now, let's allow it fallback to current user or empty
    $customerName = current_user()['name'] ?? '';
    $customerPhone = current_user()['phone'] ?? '';
    $orderType = 'takeaway';
    $notes = '';
} else {
    $customerName = $_POST['customer_name'] ?? current_user()['name'] ?? '';
    $customerPhone = $_POST['customer_phone'] ?? current_user()['phone'] ?? '';
    $orderType = $_POST['order_type'] ?? 'takeaway';
    $notes = $_POST['notes'] ?? '';
}

$branchId = (int) ($_SESSION['branch_id'] ?? 1);
$branch = $db->prepare('SELECT * FROM branches WHERE id = ?');
$branch->execute([$branchId]);
$branch = $branch->fetch();

$pageTitle = 'Pembayaran';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        
        <div class="stepper-wrap">
            <div class="stepper">
                <div class="stepper-progress" style="width: 75%;"></div>
                <div class="step completed">
                    <div class="step-icon"><i class="fa-solid fa-utensils"></i></div>
                    <div class="step-label">Pilih Menu</div>
                </div>
                <div class="step completed">
                    <div class="step-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div class="step-label">Keranjang</div>
                </div>
                <div class="step active">
                    <div class="step-icon"><i class="fa-solid fa-credit-card"></i></div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
        </div>

        <div class="page-title">
            <h1>Selesaikan Pesanan</h1>
            <span class="badge badge-black" style="font-size: 0.95rem; padding: 8px 16px;"><i class="fa-solid fa-store" style="margin-right: 8px;"></i> <?= e($branch['name'] ?? '') ?></span>
        </div>
        
        <form class="grid grid-2" style="align-items: start;" data-checkout-form>
            <input type="hidden" name="branch_id" value="<?= $branchId ?>">
            <input type="hidden" name="customer_name" value="<?= e($customerName) ?>">
            <input type="hidden" name="customer_phone" value="<?= e($customerPhone) ?>">
            <input type="hidden" name="order_type" value="<?= e($orderType) ?>">
            <input type="hidden" name="notes" value="<?= e($notes) ?>">
            
            <div class="card" style="padding: 24px;">
                <h2 style="margin-top: 0; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(0,0,0,0.05);">Ringkasan Pesanan</h2>
                
                <div style="background: var(--gray-light); padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px;">
                    <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 8px;">
                        <i class="fa-solid fa-user" style="color: var(--primary-dark); margin-top: 4px;"></i>
                        <div>
                            <strong style="display: block; font-size: 1.05rem;"><?= e($customerName ?: 'Tamu') ?></strong>
                            <span class="muted" style="font-size: 0.9rem;"><?= e($customerPhone ?: '-') ?></span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <i class="fa-solid fa-clipboard-list" style="color: var(--primary-dark); margin-top: 4px;"></i>
                        <div>
                            <strong style="display: block; font-size: 0.95rem; text-transform: uppercase;"><?= str_replace('_', ' ', e($orderType)) ?></strong>
                            <?php if ($notes): ?>
                                <span class="muted" style="font-size: 0.9rem; font-style: italic;"><?= e($notes) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <h3 style="font-size: 1.1rem; margin-bottom: 16px;">Daftar Item</h3>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px dashed rgba(0,0,0,0.1);">
                            <div>
                                <strong style="display: block;"><?= (int) $item['quantity'] ?>x <?= e($item['menu_name']) ?></strong>
                                <span class="muted" style="font-size: 0.85rem;"><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?></span>
                            </div>
                            <strong style="font-weight: 600;"><?= format_rupiah((float) $item['subtotal']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span class="muted">Subtotal</span>
                    <strong><?= format_rupiah((float) $cart['subtotal']) ?></strong>
                </div>
                <div class="summary-row">
                    <span class="muted">Pajak PB1 (<?= (float) $cart['tax_rate'] * 100 ?>%)</span>
                    <strong><?= format_rupiah((float) $cart['tax']) ?></strong>
                </div>
                <div class="summary-total" style="font-size: 1.5rem; color: var(--primary-dark);">
                    <span>Total Pembayaran</span>
                    <span><?= format_rupiah((float) $cart['total']) ?></span>
                </div>
            </div>
            
            <div class="card form-grid" style="padding: 24px;">
                <h2 style="margin-top: 0; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(0,0,0,0.05);">Metode Pembayaran</h2>
                
                <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                    <?php 
                    $methods = [
                        ['id' => 'Cash', 'icon' => 'fa-money-bill-wave', 'desc' => 'Bayar tunai di kasir'],
                        ['id' => 'QRIS', 'icon' => 'fa-qrcode', 'desc' => 'Bayar via e-wallet / m-banking'],
                        ['id' => 'Transfer Bank', 'icon' => 'fa-building-columns', 'desc' => 'Transfer manual ke rekening bank'],
                        ['id' => 'COD', 'icon' => 'fa-motorcycle', 'desc' => 'Bayar saat pesanan tiba (Khusus Delivery)']
                    ];
                    foreach ($methods as $idx => $m): 
                        // Disable COD if not delivery
                        $disabled = ($m['id'] === 'COD' && $orderType !== 'delivery') ? 'disabled' : '';
                        $checked = ($m['id'] === 'Cash' && empty($disabled)) ? 'checked' : '';
                        $opacity = $disabled ? 'opacity: 0.5;' : '';
                    ?>
                        <label style="display: flex; gap: 16px; align-items: center; padding: 16px; border: 1px solid rgba(0,0,0,0.1); border-radius: var(--radius-sm); cursor: <?= $disabled ? 'not-allowed' : 'pointer' ?>; transition: var(--transition); <?= $opacity ?>">
                            <input type="radio" name="payment_method" value="<?= e($m['id']) ?>" <?= $checked ?> <?= $disabled ?> style="width: 20px; height: 20px; accent-color: var(--primary-dark);">
                            <div style="width: 40px; height: 40px; background: var(--gray-light); border-radius: 50%; display: grid; place-items: center; font-size: 1.2rem; color: var(--gray-dark);">
                                <i class="fa-solid <?= $m['icon'] ?>"></i>
                            </div>
                            <div style="flex: 1;">
                                <strong style="display: block; font-size: 1.05rem;"><?= e($m['id']) ?></strong>
                                <span class="muted" style="font-size: 0.85rem;"><?= e($m['desc']) ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <button class="btn btn-primary" type="submit" style="width: 100%; font-size: 1.1rem; padding: 16px;">
                    <i class="fa-solid fa-paper-plane" style="margin-right: 8px;"></i> Proses Pesanan Sekarang
                </button>
                <p style="text-align: center; font-size: 0.85rem; color: var(--gray); margin-top: 16px;">
                    Dengan menekan tombol di atas, Anda menyetujui pesanan ini.
                </p>
            </div>
        </form>
    </div>
</section>
<script src="<?= base_url('assets/js/order.js') ?>?v=1.1"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
