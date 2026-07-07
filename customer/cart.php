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

                <div class="card" style="padding:24px;border:none;border-radius:24px;background:transparent;box-shadow:none;">
                    
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
                        <h2 style="font-size:1.4rem;font-weight:800;color:var(--on-surface);">Keranjang Saya</h2>
                    </div>

                    <div style="background:white;border-radius:24px;padding:24px;box-shadow:0 4px 24px rgba(0,0,0,0.02);margin-bottom:24px;border:1px solid var(--outline-variant);">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-item-card" data-cart-row="<?= (int) $item['id'] ?>" style="display:flex;gap:20px;padding:20px 0;border-bottom:1px solid var(--surface-container);align-items:center;">
                            <?php if (isset($item['image_url']) && $item['image_url']): ?>
                                <img src="<?= e($item['image_url']) ?>" class="cart-item-img" style="width:100px;height:100px;border-radius:16px;object-fit:cover;">
                            <?php else: ?>
                                <div class="cart-item-img" style="width:100px;height:100px;border-radius:16px;background:var(--surface-container);display:grid;place-items:center;font-size:2rem;">🍗</div>
                            <?php endif; ?>

                            <div class="cart-item-details" style="flex:1;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                                    <div>
                                        <h4 class="cart-item-title" style="font-weight:800;font-size:1.1rem;margin-bottom:4px;"><?= e($item['menu_name']) ?></h4>
                                        <div class="cart-item-variant" style="font-size:0.85rem;color:var(--secondary);margin-bottom:6px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                            <span><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?></span>
                                            <?php if (isset($item['spice_level']) && $item['spice_level'] !== '' && $item['spice_level'] !== '0'): ?>
                                                <span style="background:rgba(255, 214, 0, 0.15);color:#b29500;padding:2px 8px;border-radius:6px;font-weight:700;font-size:0.75rem;display:inline-flex;align-items:center;gap:4px;">
                                                    <i class="fa-solid fa-pepper-hot"></i> Level <?= e($item['spice_level']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['notes'])): ?>
                                            <div style="background:var(--surface-container-low);padding:6px 12px;border-radius:8px;font-size:0.85rem;color:var(--on-surface-variant);display:inline-flex;align-items:center;gap:6px;border:1px dashed var(--outline-variant);margin-bottom:6px;">
                                                <i class="fa-regular fa-note-sticky" style="color:var(--on-surface-variant);"></i>
                                                <span style="font-style:italic;">"<?= e($item['notes']) ?>"</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="cart-item-price" style="font-weight:800;font-size:1.1rem;color:var(--on-surface);"><?= format_rupiah((float) $item['subtotal']) ?></div>
                                </div>
                                
                                <div class="cart-item-actions" style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
                                    <div class="qty-pill" style="display:flex;align-items:center;gap:16px;background:var(--surface-container-low);padding:6px 12px;border-radius:99px;">
                                        <button type="button" data-cart-qty="<?= max(0, (int)$item['quantity'] - 1) ?>" style="border:none;background:transparent;cursor:pointer;color:var(--secondary);"><i class="fa-solid fa-minus"></i></button>
                                        <span style="font-weight:800;font-size:1rem;"><?= (int) $item['quantity'] ?></span>
                                        <button type="button" data-cart-qty="<?= (int)$item['quantity'] + 1 ?>" style="border:none;background:transparent;cursor:pointer;color:#000000;font-weight:800;"><i class="fa-solid fa-plus"></i></button>
                                    </div>
                                    <button class="cart-btn-text" style="color:var(--error);background:transparent;border:none;cursor:pointer;font-size:1.2rem;" type="button" data-cart-remove>
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <div style="background:white;border-radius:24px;padding:24px;box-shadow:0 4px 24px rgba(0,0,0,0.02);border:1px solid var(--outline-variant);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h3 style="font-size:1.1rem;font-weight:800;">Tambah Pelengkap?</h3>
                        </div>
                        <div style="display:flex;gap:16px;overflow-x:auto;" class="hide-scrollbar">
                            <div style="min-width:240px;border:1px solid var(--outline-variant);border-radius:16px;padding:16px;display:flex;align-items:center;gap:12px;">
                                <img src="https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=100&q=80" style="width:60px;height:60px;border-radius:12px;object-fit:cover;">
                                <div style="flex:1;">
                                    <h5 style="font-weight:700;font-size:0.9rem;margin-bottom:4px;">Kulit Ayam Crispy</h5>
                                    <div style="font-weight:800;color:var(--on-surface);font-size:0.9rem;margin-bottom:8px;">+Rp 12.000</div>
                                    <button type="button" class="btn btn-outline" style="width:100%;padding:4px;font-size:0.8rem;border-radius:8px;">Tambah</button>
                                </div>
                            </div>
                            <div style="min-width:240px;border:1px solid var(--outline-variant);border-radius:16px;padding:16px;display:flex;align-items:center;gap:12px;">
                                <img src="https://images.unsplash.com/photo-1596662951482-0c4ba74a6df6?auto=format&fit=crop&w=100&q=80" style="width:60px;height:60px;border-radius:12px;object-fit:cover;">
                                <div style="flex:1;">
                                    <h5 style="font-weight:700;font-size:0.9rem;margin-bottom:4px;">Sambal Korek Extra</h5>
                                    <div style="font-weight:800;color:var(--on-surface);font-size:0.9rem;margin-bottom:8px;">+Rp 3.000</div>
                                    <button type="button" class="btn btn-outline" style="width:100%;padding:4px;font-size:0.8rem;border-radius:8px;">Tambah</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-box card" style="padding:32px;border:1px solid var(--outline-variant);border-radius:32px;background:white;box-shadow:0 8px 32px rgba(0,0,0,0.04);">
                    <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:24px;border-bottom:1px dashed var(--outline);padding-bottom:16px;">Ringkasan Pesanan</h2>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:1rem;color:var(--secondary);">
                        <span>Subtotal (<?= $cart['count'] ?> Produk)</span>
                        <span style="color:var(--on-surface);font-weight:700;"><?= format_rupiah((float) $cart['subtotal']) ?></span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:1rem;color:var(--secondary);">
                        <span>Pajak (PB1 10%)</span>
                        <span style="color:var(--on-surface);font-weight:700;"><?= format_rupiah((float) $cart['tax']) ?></span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:1rem;color:var(--secondary);">
                        <span>Biaya Layanan</span>
                        <span style="color:var(--on-surface);font-weight:700;">Rp 2.000</span>
                    </div>

                    <div style="margin-top:24px;margin-bottom:24px;">
                        <div style="display:flex;align-items:center;background:var(--surface-container-low);border-radius:12px;padding:8px 8px 8px 16px;">
                            <i class="fa-solid fa-ticket" style="color:var(--on-surface-variant);font-size:1.2rem;margin-right:12px;"></i>
                            <input type="text" placeholder="Masukkan Kode Promo" style="border:none;background:transparent;flex:1;font-weight:600;font-size:0.95rem;outline:none;">
                            <button class="btn btn-primary" style="padding:8px 16px;border-radius:8px;font-weight:700;">Pakai</button>
                        </div>
                    </div>

                    <?php $totalSim = $cart['total'] + 2000; ?>

                    <div style="display:flex;justify-content:space-between;margin-top:16px;padding-top:24px;border-top:1px dashed var(--outline);font-size:1.4rem;font-weight:900;">
                        <span>Total Bayar</span>
                        <span style="color:var(--on-surface);"><?= format_rupiah((float) $totalSim) ?></span>
                    </div>

                    <a href="<?= base_url('customer/checkout.php') ?>" class="btn btn-primary" style="width:100%;margin-top:32px;justify-content:center;border-radius:16px;padding:16px;font-size:1.1rem;font-weight:800;box-shadow:0 8px 24px rgba(255,253,0,0.2);">
                        Checkout Now <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <div style="margin-top:32px;background:var(--surface-container-low);border-radius:16px;padding:20px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <h4 style="font-size:0.95rem;font-weight:700;display:flex;align-items:center;gap:8px;"><i class="fa-solid fa-location-dot" style="color:#000000;"></i> Alamat Pengiriman</h4>
                            <a href="#" style="font-size:0.85rem;font-weight:700;color:var(--on-surface);text-decoration:underline;">Ubah</a>
                        </div>
                        <p style="font-size:0.9rem;color:var(--secondary);line-height:1.5;margin:0;">
                            <strong style="color:var(--on-surface);">Kantor Utama</strong><br>
                            Gedung Cyber 2 Tower Lantai 17, Jl. H. R. Rasuna Said...
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
