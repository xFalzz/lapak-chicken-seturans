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

        <form id="checkoutForm" class="grid grid-2" style="align-items:start;grid-template-columns:2fr 1.2fr;gap:40px;" method="post" action="<?= base_url('api/checkout.php') ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="customer_name" value="<?= e($customerName) ?>">
            <input type="hidden" name="customer_phone" value="<?= e($customerPhone) ?>">
            
            <!-- Left: Informasi Pesanan -->
            <div>
                <!-- Info Section -->
                <div class="checkout-card">
                    <h2 class="checkout-card-title">Informasi Pesanan</h2>
                    
                    <div style="margin-bottom:24px;">
                        <h4 style="font-size:0.95rem;color:var(--secondary);margin-bottom:12px;">Lokasi Cabang</h4>
                        <div class="address-box">
                            <div class="address-icon">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <div>
                                <strong style="display:block;font-size:1rem;margin-bottom:4px;"><?= e($branch['name']) ?></strong>
                                <span style="font-size:0.9rem;color:var(--secondary);line-height:1.5;display:block;"><?= e($branch['address'] ?? 'Cabang Lapak Chicken') ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom:24px;">
                        <h4 style="font-size:0.95rem;color:var(--secondary);margin-bottom:8px;">Tipe Pesanan</h4>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px;">
                            <label style="border:1px solid var(--outline);border-radius:var(--radius-md);padding:16px;text-align:center;cursor:pointer;position:relative;" class="order-type-label active">
                                <input type="radio" name="order_type" value="takeaway" checked style="position:absolute;opacity:0;">
                                <i class="fa-solid fa-bag-shopping" style="font-size:1.4rem;margin-bottom:8px;color:#B29500;"></i>
                                <span style="display:block;font-weight:600;font-size:0.95rem;">Takeaway</span>
                            </label>
                            <label style="border:1px solid var(--outline);border-radius:var(--radius-md);padding:16px;text-align:center;cursor:pointer;position:relative;" class="order-type-label">
                                <input type="radio" name="order_type" value="dine_in" style="position:absolute;opacity:0;">
                                <i class="fa-solid fa-utensils" style="font-size:1.4rem;margin-bottom:8px;color:#B29500;"></i>
                                <span style="display:block;font-weight:600;font-size:0.95rem;">Dine In</span>
                            </label>
                            <label style="border:1px solid var(--outline);border-radius:var(--radius-md);padding:16px;text-align:center;cursor:pointer;position:relative;" class="order-type-label">
                                <input type="radio" name="order_type" value="delivery" style="position:absolute;opacity:0;">
                                <i class="fa-solid fa-motorcycle" style="font-size:1.4rem;margin-bottom:8px;color:#B29500;"></i>
                                <span style="display:block;font-weight:600;font-size:0.95rem;">Delivery</span>
                            </label>
                        </div>
                    </div>

                    <div style="margin-bottom:8px;">
                        <h4 style="font-size:0.95rem;color:var(--secondary);margin-bottom:8px;">Catatan Tambahan (Opsional)</h4>
                        <textarea name="notes" placeholder="Contoh: Sambal dipisah, Sendok plastik, dll." style="width:100%;min-height:80px;border-radius:12px;border:1px solid var(--outline-variant);padding:12px;font-family:inherit;font-size:0.95rem;resize:vertical;"></textarea>
                    </div>
                </div>

                <!-- Detail Item Section -->
                <div class="checkout-card">
                    <h2 class="checkout-card-title">Detail Pesanan</h2>
                    <?php foreach ($cart['items'] as $item): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--surface-container);">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <span style="font-weight:700;color:var(--primary);background:var(--primary-container);padding:2px 8px;border-radius:4px;font-size:0.85rem;"><?= (int) $item['quantity'] ?>x</span>
                                <div>
                                    <span style="display:block;font-weight:600;font-size:0.95rem;"><?= e($item['menu_name']) ?></span>
                                    <span style="font-size:0.85rem;color:var(--secondary);"><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?></span>
                                </div>
                            </div>
                            <span style="font-weight:700;font-size:1rem;"><?= format_rupiah((float) $item['subtotal']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Pembayaran & Ringkasan -->
            <div>
                <!-- Metode Pembayaran -->
                <div class="checkout-card">
                    <h2 class="checkout-card-title" style="border-bottom:1px dashed var(--outline);padding-bottom:16px;">Detail Pembayaran</h2>
                    
                    <h4 style="font-size:0.95rem;color:var(--secondary);margin-bottom:12px;">E-Wallet</h4>
                    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:var(--radius-md);cursor:pointer;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="radio" name="payment_method" value="Gopay" checked style="accent-color:var(--primary-container);width:18px;height:18px;">
                                <span style="font-weight:600;font-size:1rem;">Gopay</span>
                            </div>
                            <i class="fa-solid fa-wallet" style="color:var(--secondary);"></i>
                        </label>
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:var(--radius-md);cursor:pointer;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="radio" name="payment_method" value="Ovo" style="accent-color:var(--primary-container);width:18px;height:18px;">
                                <span style="font-weight:600;font-size:1rem;">Ovo</span>
                            </div>
                            <i class="fa-solid fa-wallet" style="color:var(--secondary);"></i>
                        </label>
                    </div>

                    <h4 style="font-size:0.95rem;color:var(--secondary);margin-bottom:12px;">Transfer Bank</h4>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:var(--radius-md);cursor:pointer;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="radio" name="payment_method" value="BCA" style="accent-color:var(--primary-container);width:18px;height:18px;">
                                <span style="font-weight:600;font-size:1rem;">BCA Virtual Account</span>
                            </div>
                            <i class="fa-solid fa-building-columns" style="color:var(--secondary);"></i>
                        </label>
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--outline-variant);border-radius:var(--radius-md);cursor:pointer;" class="payment-method-label">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <input type="radio" name="payment_method" value="Mandiri" style="accent-color:var(--primary-container);width:18px;height:18px;">
                                <span style="font-weight:600;font-size:1rem;">Mandiri Virtual Account</span>
                            </div>
                            <i class="fa-solid fa-building-columns" style="color:var(--secondary);"></i>
                        </label>
                    </div>
                </div>

                <!-- Ringkasan -->
                <div class="checkout-card" style="background:var(--surface-container-low);">
                    <h2 class="checkout-card-title" style="border-bottom:1px dashed var(--outline);padding-bottom:16px;">Ringkasan Pembayaran</h2>
                    
                    <div style="display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.95rem;color:var(--secondary);">
                        <span>Subtotal</span>
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

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:24px;justify-content:center;border-radius:var(--radius-pill);padding:14px;font-size:1.05rem;" id="checkoutBtn">
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>

<style>
/* Checkout custom styles */
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

    // Handle form submit
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
