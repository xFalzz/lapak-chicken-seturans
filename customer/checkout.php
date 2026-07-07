<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$cart = get_cart($db);

if (!$cart['items']) {
    flash('error', 'Keranjang masih kosong');
    redirect(base_url('customer/menu.php'));
}

$customerName = current_user()['name'] ?? '';
$customerPhone = current_user()['phone'] ?? '';

$branchId = (int) ($_SESSION['branch_id'] ?? 1);
$branch = $db->prepare('SELECT * FROM branches WHERE id = ?');
$branch->execute([$branchId]);
$branch = $branch->fetch();

$pageTitle = 'Selesaikan Pesanan';
require __DIR__ . '/../includes/header.php';
?>
<section class="section" style="background:var(--surface);padding-top:40px;">
    <div class="container">

        <div class="page-title" style="margin-bottom:32px;">
            <a href="<?= base_url('customer/cart.php') ?>" style="display:inline-flex;align-items:center;gap:8px;color:var(--secondary);font-size:0.95rem;margin-bottom:12px;font-weight:600;"><i class="fa-solid fa-arrow-left"></i> Kembali ke Keranjang</a>
            <h1 style="font-size:1.8rem;margin:0;">Selesaikan Pesanan</h1>
        </div>

        <form id="checkoutForm" class="grid grid-2" style="align-items:start;grid-template-columns:2fr 1.2fr;gap:40px;" method="post" action="<?= base_url('api/order.php?action=create') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="customer_name" value="<?= e($customerName) ?>">
            <input type="hidden" name="customer_phone" value="<?= e($customerPhone) ?>">

            <div style="display:flex;flex-direction:column;gap:24px;">
                
                <div class="checkout-card" style="margin-bottom:0;border-radius:24px;border:none;box-shadow:0 4px 24px rgba(0,0,0,0.02);border:1px solid var(--outline-variant);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                        <h2 class="checkout-card-title" style="margin-bottom:0;font-size:1.2rem;font-weight:800;">Alamat Pengiriman</h2>
                        <a href="#" style="font-size:0.9rem;font-weight:700;color:var(--on-surface);text-decoration:underline;">Ubah Alamat</a>
                    </div>
                    
                    <div class="address-box" style="background:transparent;border:1px solid var(--outline-variant);border-radius:16px;">
                        <div class="address-icon" style="background:var(--surface-container-low);box-shadow:none;">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div>
                            <strong style="display:block;font-size:1.05rem;margin-bottom:4px;"><?= e($branch['name']) ?> (Mock Delivery)</strong>
                            <span style="font-size:0.95rem;color:var(--secondary);line-height:1.5;display:block;">Gedung Cyber 2 Tower Lantai 17, Jl. H. R. Rasuna Said Blok X-5 Kav. 13</span>
                            <span style="display:block;font-size:0.9rem;color:var(--secondary);margin-top:8px;"><i class="fa-solid fa-circle-info" style="color:var(--on-surface-variant);margin-right:4px;"></i> Tinggalkan di lobi, hubungi di bawah...</span>
                        </div>
                    </div>
                </div>

                <div class="checkout-card" style="margin-bottom:0;border-radius:24px;border:none;box-shadow:0 4px 24px rgba(0,0,0,0.02);border:1px solid var(--outline-variant);">
                    <h2 class="checkout-card-title" style="font-size:1.2rem;font-weight:800;margin-bottom:20px;">Rincian Pesanan</h2>
                    <?php foreach ($cart['items'] as $item): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--surface-container);">
                            <div style="display:flex;align-items:flex-start;gap:16px;flex:1;">
                                <div style="width:60px;height:60px;border-radius:12px;background:var(--surface-container);display:grid;place-items:center;">
                                    <?php if (isset($item['image_url']) && $item['image_url']): ?>
                                        <img src="<?= e(base_url($item['image_url'])) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                                    <?php else: ?>
                                        <span style="font-size:1.5rem;">🍗</span>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1;">
                                    <span style="display:block;font-weight:700;font-size:1.05rem;margin-bottom:4px;"><?= e($item['menu_name']) ?></span>
                                    <div style="font-size:0.85rem;color:var(--secondary);margin-bottom:6px;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                                        <span><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?></span>
                                        <?php if (isset($item['spice_level']) && $item['spice_level'] !== '' && $item['spice_level'] !== '0'): ?>
                                            <span style="background:rgba(255, 214, 0, 0.15);color:#b29500;padding:2px 8px;border-radius:6px;font-weight:700;font-size:0.75rem;display:inline-flex;align-items:center;gap:4px;">
                                                <i class="fa-solid fa-pepper-hot"></i> Level <?= e($item['spice_level']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['notes'])): ?>
                                        <div style="background:var(--surface-container-low);padding:6px 12px;border-radius:8px;font-size:0.85rem;color:var(--on-surface-variant);display:inline-flex;align-items:center;gap:6px;border:1px dashed var(--outline-variant);margin-bottom:8px;">
                                            <i class="fa-regular fa-note-sticky" style="color:var(--on-surface-variant);"></i>
                                            <span style="font-style:italic;">"<?= e($item['notes']) ?>"</span>
                                        </div>
                                    <?php endif; ?>

                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <div class="qty-pill" style="display:inline-flex;align-items:center;gap:12px;background:var(--surface-container-low);padding:4px 8px;border-radius:99px;border:none;">
                                            <button type="button" data-cart-qty="<?= max(0, (int)$item['quantity'] - 1) ?>" style="border:none;background:transparent;cursor:pointer;color:var(--secondary);"><i class="fa-solid fa-minus"></i></button>
                                            <span style="font-weight:800;font-size:0.95rem;width:20px;text-align:center;"><?= (int) $item['quantity'] ?></span>
                                            <button type="button" data-cart-qty="<?= (int)$item['quantity'] + 1 ?>" style="border:none;background:transparent;cursor:pointer;color:#000000;font-weight:800;"><i class="fa-solid fa-plus"></i></button>
                                        </div>
                                        <button class="cart-btn-text" style="color:var(--error);background:transparent;border:none;cursor:pointer;font-size:1rem;" type="button" data-cart-remove>
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <span style="font-weight:800;font-size:1.1rem;color:var(--on-surface);"><?= format_rupiah((float) $item['subtotal']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top:24px;">
                        <textarea name="notes" placeholder="Catatan untuk Restoran (Opsional)" style="width:100%;min-height:60px;border-radius:12px;border:1px solid var(--outline-variant);padding:16px;font-family:inherit;font-size:0.95rem;resize:vertical;background:var(--surface-container-low);"></textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:24px;">
                
                <div class="checkout-card" style="margin-bottom:0;border-radius:32px;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.04);background:white;padding:32px;">
                    <h2 class="checkout-card-title" style="border-bottom:1px dashed var(--outline);padding-bottom:16px;font-size:1.3rem;font-weight:800;">Metode Pembayaran</h2>
                    
                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:16px;cursor:pointer;transition:all 0.2s;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:16px;">
                                <input type="radio" name="payment_method" value="Gopay" checked style="accent-color:var(--primary-container);width:20px;height:20px;">
                                <div>
                                    <span style="font-weight:700;font-size:1.05rem;display:block;">GoPay</span>
                                    <span style="font-size:0.85rem;color:var(--secondary);">Saldo Rp145.200</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-wallet" style="color:var(--secondary);font-size:1.2rem;"></i>
                        </label>
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:16px;cursor:pointer;transition:all 0.2s;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:16px;">
                                <input type="radio" name="payment_method" value="ShopeePay" style="accent-color:var(--primary-container);width:20px;height:20px;">
                                <div>
                                    <span style="font-weight:700;font-size:1.05rem;display:block;">ShopeePay</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-wallet" style="color:var(--secondary);font-size:1.2rem;"></i>
                        </label>
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:16px;cursor:pointer;transition:all 0.2s;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:16px;">
                                <input type="radio" name="payment_method" value="Ovo" style="accent-color:var(--primary-container);width:20px;height:20px;">
                                <div>
                                    <span style="font-weight:700;font-size:1.05rem;display:block;">OVO</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-wallet" style="color:var(--secondary);font-size:1.2rem;"></i>
                        </label>
                        <a href="#" style="text-align:center;font-weight:700;color:var(--on-surface);text-decoration:underline;font-size:0.95rem;margin-top:8px;">Lihat metode lainnya</a>
                    </div>
                </div>

                <div class="checkout-card" style="margin-bottom:0;border-radius:32px;border:none;box-shadow:0 8px 32px rgba(0,0,0,0.04);background:var(--surface-container-low);padding:32px;">
                    <h2 class="checkout-card-title" style="border-bottom:1px dashed var(--outline);padding-bottom:16px;font-size:1.3rem;font-weight:800;">Ringkasan Pembayaran</h2>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:1rem;color:var(--secondary);">
                        <span>Subtotal</span>
                        <span style="color:var(--on-surface);font-weight:700;"><?= format_rupiah((float) $cart['subtotal']) ?></span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:1rem;color:var(--secondary);">
                        <span>Ongkos Kirim</span>
                        <span style="color:var(--on-surface);font-weight:700;">Rp 15.000</span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:1rem;color:var(--secondary);">
                        <span>Biaya Layanan</span>
                        <span style="color:var(--on-surface);font-weight:700;">Rp 2.000</span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:1rem;color:var(--secondary);">
                        <span>Diskon Ongkir</span>
                        <span style="color:var(--success);font-weight:700;">-Rp 10.000</span>
                    </div>

                    <?php $totalSimCheckout = $cart['total'] + 15000 + 2000 - 10000; ?>

                    <div style="display:flex;justify-content:space-between;margin-top:16px;padding-top:24px;border-top:1px dashed var(--outline);font-size:1.4rem;font-weight:900;">
                        <span>Total Tagihan</span>
                        <span style="color:var(--on-surface);"><?= format_rupiah((float) $totalSimCheckout) ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:32px;justify-content:center;border-radius:16px;padding:16px;font-size:1.1rem;font-weight:800;box-shadow:0 8px 24px rgba(255,253,0,0.2);" id="checkoutBtn">
                        Bayar Sekarang <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<style>

.order-type-label.active {
    border-color: #B29500 !important;
    background: rgba(255, 214, 0, 0.05);
}
.payment-method-label:has(input:checked) {
    border-color: #B29500 !important;
    background: rgba(255, 214, 0, 0.05);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const orderTypeLabels = document.querySelectorAll('.order-type-label');
    const orderTypeInputs = document.querySelectorAll('input[name="order_type"]');
    
    orderTypeInputs.forEach(input => {
        input.addEventListener('change', () => {
            orderTypeLabels.forEach(label => label.classList.remove('active'));
            if(input.checked) {
                input.closest('.order-type-label').classList.add('active');
            }
        });
    });
    const form = document.getElementById('checkoutForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('checkoutBtn');
        btn.classList.add('loading');
        btn.disabled = true;

        try {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            const result = await apiFetch('api/order.php?action=create', {
                method: 'POST',
                body: JSON.stringify(data)
            });
            
            toast('Pesanan berhasil dibuat!', 'success');
            setTimeout(() => {
                window.location.href = `<?= base_url('customer/order-status.php?code=') ?>` + encodeURIComponent(result.order_code);
            }, 1000);
            
        } catch (err) {
            toast(err.message || 'Gagal memproses pesanan', 'error');
            btn.classList.remove('loading');
            btn.disabled = false;
        }
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
