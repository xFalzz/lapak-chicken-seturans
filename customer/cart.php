<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$cart = get_cart($db);
$pageTitle = 'Keranjang';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="page-title"><h1>Keranjang</h1><a class="btn btn-outline" href="<?= base_url('customer/menu.php') ?>">Tambah Menu</a></div>
        <div class="grid grid-2">
            <div class="card">
                <?php if (!$cart['items']): ?>
                    <p class="muted">Keranjang masih kosong.</p>
                <?php endif; ?>
                <?php foreach ($cart['items'] as $item): ?>
                    <div class="cart-row" data-cart-row="<?= (int) $item['id'] ?>">
                        <strong><?= e($item['menu_name']) ?></strong>
                        <span class="muted"><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?><?= $item['notes'] ? ' - ' . e($item['notes']) : '' ?></span>
                        <div class="qty-controls">
                            <button type="button" data-cart-qty="<?= max(0, (int) $item['quantity'] - 1) ?>">-</button>
                            <strong><?= (int) $item['quantity'] ?></strong>
                            <button type="button" data-cart-qty="<?= (int) $item['quantity'] + 1 ?>">+</button>
                            <button class="btn btn-danger" type="button" data-cart-remove>Hapus</button>
                        </div>
                        <strong><?= format_rupiah((float) $item['subtotal']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
            <form class="card summary-box form-grid" method="get" action="<?= base_url('customer/checkout.php') ?>">
                <h2>Detail pesanan</h2>
                <div class="form-field"><label>Nama</label><input name="customer_name" required value="<?= e(current_user()['name'] ?? '') ?>"></div>
                <div class="form-field"><label>Phone</label><input name="customer_phone" required value="<?= e(current_user()['phone'] ?? '') ?>"></div>
                <div class="form-field"><label>Tipe order</label><select name="order_type" data-order-type><option value="dine_in">Dine-in</option><option value="takeaway">Takeaway</option><option value="delivery">Delivery</option></select></div>
                <div class="form-field"><label>Nomor meja / alamat</label><textarea name="notes" placeholder="Meja 4 atau alamat delivery"></textarea></div>
                <p>Subtotal <strong><?= format_rupiah((float) $cart['subtotal']) ?></strong></p>
                <p>Pajak <?= (float) $cart['tax_rate'] * 100 ?>% <strong><?= format_rupiah((float) $cart['tax']) ?></strong></p>
                <h2>Total <?= format_rupiah((float) $cart['total']) ?></h2>
                <button class="btn btn-primary" <?= !$cart['items'] ? 'disabled' : '' ?>>Lanjut ke Checkout</button>
            </form>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/js/cart.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
