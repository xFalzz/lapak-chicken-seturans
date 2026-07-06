<?php
require_once __DIR__ . '/../includes/functions.php';
$db   = db();
$cart = get_cart($db);
$pageTitle = 'Keranjang Anda';
require __DIR__ . '/../includes/header.php';
?>

<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container">

        <div class="page-title" style="margin-bottom:32px;">
            <h1 style="font-size:1.8rem;margin:0;">Keranjang Pesanan</h1>
        </div>

        <?php if (!$cart['items']): ?>
            <div class="empty-state" style="text-align:center;padding:60px 20px;background:var(--surface-container-low);border-radius:var(--radius-xl);">
                <div style="font-size:3rem;color:var(--outline);margin-bottom:16px;"><i class="fa-solid fa-bag-shopping"></i></div>
                <h3 style="margin-bottom:8px;">Keranjang Masih Kosong</h3>
                <p style="color:var(--secondary);margin-bottom:24px;">Wah, belum ada menu yang kamu pilih nih.</p>
                <a class="btn btn-primary" href="<?= base_url('customer/menu.php') ?>" style="border-radius:var(--radius-pill);">
                    <i class="fa-solid fa-utensils"></i> Lihat Menu
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-2" style="align-items:start;grid-template-columns:2fr 1.2fr;gap:40px;">
                
                <!-- Left: Items -->
                <div class="card" style="padding:24px;border:none;border-bottom:1px solid var(--outline-variant);border-radius:0;">
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--outline-variant);">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <i class="fa-solid fa-store" style="color:var(--secondary);"></i>
                            <span style="font-weight:700;font-size:1.05rem;">Dari <?= e($_SESSION['branch_name'] ?? '') ?></span>
                        </div>
                    </div>

                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-item-card" data-cart-row="<?= (int) $item['id'] ?>">
                            <?php if (isset($item['image_url']) && $item['image_url']): ?>
                                <img src="<?= e($item['image_url']) ?>" class="cart-item-img">
                            <?php else: ?>
                                <div class="cart-item-img" style="background:var(--surface-container);display:grid;place-items:center;font-size:2rem;">🍗</div>
                            <?php endif; ?>

                            <div class="cart-item-details">
                                <div class="cart-item-header">
                                    <h4 class="cart-item-title"><?= e($item['menu_name']) ?></h4>
                                    <div class="cart-item-price"><?= format_rupiah((float) $item['subtotal']) ?></div>
                                </div>
                                
                                <div class="cart-item-variant">
                                    <?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <div class="qty-pill">
                                        <button type="button" data-cart-qty="<?= max(0, (int)$item['quantity'] - 1) ?>"><i class="fa-solid fa-minus"></i></button>
                                        <span><?= (int) $item['quantity'] ?></span>
                                        <button type="button" data-cart-qty="<?= (int)$item['quantity'] + 1 ?>"><i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <button class="cart-btn-text" type="button">
                                        <i class="fa-regular fa-pen-to-square"></i> Tulis Catatan
                                    </button>
                                    <button class="cart-btn-text" style="color:var(--error);" type="button" data-cart-remove>
                                        <i class="fa-regular fa-trash-can"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <a href="<?= base_url('customer/menu.php') ?>" style="display:inline-block;margin-top:24px;color:#B29500;font-weight:700;"><i class="fa-solid fa-plus"></i> Tambah Menu Lainnya</a>
                </div>

                <!-- Right: Summary -->
                <div class="summary-box card" style="padding:24px;border:1px solid var(--outline-variant);border-radius:var(--radius-xl);background:var(--surface-container-low);">
                    <h2 style="font-size:1.2rem;margin-bottom:24px;border-bottom:1px dashed var(--outline);padding-bottom:16px;">Ringkasan Pesanan</h2>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.95rem;color:var(--secondary);">
                        <span>Subtotal (<?= $cart['count'] ?> Item)</span>
                        <span style="color:var(--on-surface);font-weight:600;"><?= format_rupiah((float) $cart['subtotal']) ?></span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:0.95rem;color:var(--secondary);">
                        <span>Pajak (PB1 10%)</span>
                        <span style="color:var(--on-surface);font-weight:600;"><?= format_rupiah((float) $cart['tax']) ?></span>
                    </div>

                    <div style="display:flex;justify-content:space-between;margin-top:16px;padding-top:16px;border-top:1px dashed var(--outline);font-size:1.2rem;font-weight:800;">
                        <span>Total Pembayaran</span>
                        <span><?= format_rupiah((float) $cart['total']) ?></span>
                    </div>

                    <a href="<?= base_url('customer/checkout.php') ?>" class="btn btn-primary" style="width:100%;margin-top:24px;justify-content:center;border-radius:var(--radius-pill);padding:14px;font-size:1.05rem;">
                        Lanjut ke Pembayaran
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Handling cart updates (Qty + / - and Remove)
    document.querySelectorAll('[data-cart-qty], [data-cart-remove]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const row = e.currentTarget.closest('.cart-item-card');
            if(!row) return;
            const itemId = row.dataset.cartRow;
            let qty = 0;

            if (e.currentTarget.hasAttribute('data-cart-qty')) {
                qty = parseInt(e.currentTarget.dataset.cartQty);
            }

            try {
                const url = qty > 0 ? 'api/cart.php?action=update' : 'api/cart.php?action=remove';
                const body = qty > 0 ? { cart_item_id: itemId, quantity: qty } : { cart_item_id: itemId };
                
                await apiFetch(url, {
                    method: 'POST',
                    body: JSON.stringify(body)
                });
                
                window.location.reload();
            } catch(err) {
                toast(err.message || 'Gagal mengubah keranjang', 'error');
            }
        });
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
