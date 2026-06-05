<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$cart = get_cart($db);
if (!$cart['items']) {
    flash('error', 'Keranjang masih kosong');
    redirect(base_url('customer/menu.php'));
}
$branchId = (int) ($_SESSION['branch_id'] ?? 1);
$branch = $db->prepare('SELECT * FROM branches WHERE id = ?');
$branch->execute([$branchId]);
$branch = $branch->fetch();
$pageTitle = 'Checkout';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="page-title"><h1>Checkout</h1><span class="badge badge-black"><?= e($branch['name'] ?? '') ?></span></div>
        <form class="grid grid-2" data-checkout-form>
            <input type="hidden" name="branch_id" value="<?= $branchId ?>">
            <input type="hidden" name="customer_name" value="<?= e($_GET['customer_name'] ?? current_user()['name'] ?? '') ?>">
            <input type="hidden" name="customer_phone" value="<?= e($_GET['customer_phone'] ?? current_user()['phone'] ?? '') ?>">
            <input type="hidden" name="order_type" value="<?= e($_GET['order_type'] ?? 'takeaway') ?>">
            <div class="card">
                <h2>Ringkasan</h2>
                <p><strong><?= e($_GET['customer_name'] ?? current_user()['name'] ?? '') ?></strong> - <?= e($_GET['customer_phone'] ?? current_user()['phone'] ?? '') ?></p>
                <?php foreach ($cart['items'] as $item): ?>
                    <p><?= (int) $item['quantity'] ?>x <?= e($item['menu_name']) ?> <?= $item['sauce_name'] ? '(' . e($item['sauce_name']) . ')' : '' ?> <strong><?= format_rupiah((float) $item['subtotal']) ?></strong></p>
                <?php endforeach; ?>
                <hr>
                <h2>Total <?= format_rupiah((float) $cart['total']) ?></h2>
            </div>
            <div class="card form-grid">
                <h2>Metode pembayaran</h2>
                <?php foreach (['Cash', 'QRIS', 'Transfer Bank', 'COD'] as $method): ?>
                    <label><input type="radio" name="payment_method" value="<?= e($method) ?>" <?= $method === 'Cash' ? 'checked' : '' ?>> <?= e($method) ?></label>
                <?php endforeach; ?>
                <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i>Buat Pesanan</button>
            </div>
        </form>
    </div>
</section>
<script src="<?= base_url('assets/js/order.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
