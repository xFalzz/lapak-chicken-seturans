<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$cart = get_cart($db);
$pageTitle = 'Keranjang Anda';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        
        <div class="stepper-wrap">
            <div class="stepper">
                <div class="stepper-progress" style="width: 25%;"></div>
                <div class="step completed">
                    <div class="step-icon"><i class="fa-solid fa-utensils"></i></div>
                    <div class="step-label">Pilih Menu</div>
                </div>
                <div class="step active">
                    <div class="step-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                    <div class="step-label">Keranjang</div>
                </div>
                <div class="step">
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
            <h1>Keranjang Anda</h1>
            <a class="btn btn-outline" href="<?= base_url('customer/menu.php') ?>"><i class="fa-solid fa-plus"></i> Tambah Menu</a>
        </div>
        
        <?php if (!$cart['items']): ?>
            <div class="empty-state">
                <i class="fa-solid fa-basket-shopping empty-state-icon"></i>
                <h3>Keranjang masih kosong</h3>
                <p>Wah, belum ada menu ayam crispy yang kamu pilih nih. Yuk lihat-lihat menu dulu!</p>
                <a class="btn btn-primary" href="<?= base_url('customer/menu.php') ?>">Mulai Pesan</a>
            </div>
        <?php else: ?>
            <div class="grid grid-2" style="align-items: start;">
                <div class="card" style="padding: 16px;">
                    <h2 style="margin-top: 0; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid rgba(0,0,0,0.05);">Daftar Pesanan</h2>
                    
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="cart-row" data-cart-row="<?= (int) $item['id'] ?>">
                            <div class="cart-info">
                                <h4><?= e($item['menu_name']) ?></h4>
                                <p><?= $item['sauce_name'] ? 'Saus ' . e($item['sauce_name']) : 'Tanpa saus' ?></p>
                                <?php if ($item['notes']): ?>
                                    <p style="font-style: italic; font-size: 0.85rem;"><i class="fa-solid fa-comment-dots" style="margin-right: 4px;"></i> <?= e($item['notes']) ?></p>
                                <?php endif; ?>
                                <p style="color: var(--primary-dark); font-weight: 600; margin-top: 4px;"><?= format_rupiah((float) $item['subtotal']) ?></p>
                            </div>
                            
                            <div class="cart-actions">
                                <div class="qty-controls">
                                    <button type="button" data-cart-qty="<?= max(0, (int) $item['quantity'] - 1) ?>"><i class="fa-solid fa-minus" style="font-size: 0.8rem;"></i></button>
                                    <span><?= (int) $item['quantity'] ?></span>
                                    <button type="button" data-cart-qty="<?= (int) $item['quantity'] + 1 ?>"><i class="fa-solid fa-plus" style="font-size: 0.8rem;"></i></button>
                                </div>
                                <button class="icon-btn" style="color: var(--danger); background: #FEF2F2;" type="button" data-cart-remove aria-label="Hapus Item">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <form class="card summary-box form-grid" method="post" action="<?= base_url('customer/checkout.php') ?>">
                    <h2 style="margin-top: 0; margin-bottom: 16px;">Detail Pemesan</h2>
                    <p style="color: var(--gray); font-size: 0.9rem; margin-top: -12px; margin-bottom: 16px;">Pastikan data diri Anda benar untuk kelancaran pesanan.</p>
                    
                    <div class="form-field">
                        <label>Nama Pemesan</label>
                        <input name="customer_name" required placeholder="Contoh: Budi Santoso" value="<?= e(current_user()['name'] ?? '') ?>">
                    </div>
                    <div class="form-field">
                        <label>Nomor WhatsApp / HP</label>
                        <input name="customer_phone" required placeholder="Contoh: 081234567890" value="<?= e(current_user()['phone'] ?? '') ?>">
                    </div>
                    <div class="form-field">
                        <label>Tipe Pesanan</label>
                        <select name="order_type" data-order-type>
                            <option value="dine_in">Dine-in (Makan di tempat)</option>
                            <option value="takeaway">Takeaway (Bawa pulang)</option>
                            <option value="delivery">Delivery (Pesan antar)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Nomor Meja / Alamat Pengiriman</label>
                        <textarea name="notes" placeholder="Meja 4 (Dine-in) atau Alamat Lengkap (Delivery)"></textarea>
                    </div>
                    
                    <div style="background: var(--gray-light); padding: 20px; border-radius: var(--radius-sm); margin-top: 16px;">
                        <h3 style="margin-top: 0; margin-bottom: 16px; font-size: 1.1rem;">Ringkasan Biaya</h3>
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong><?= format_rupiah((float) $cart['subtotal']) ?></strong>
                        </div>
                        <div class="summary-row">
                            <span>Pajak PB1 (<?= (float) $cart['tax_rate'] * 100 ?>%)</span>
                            <strong><?= format_rupiah((float) $cart['tax']) ?></strong>
                        </div>
                        <div class="summary-total">
                            <span>Total Pembayaran</span>
                            <span style="color: var(--primary-dark); font-size: 1.4rem;"><?= format_rupiah((float) $cart['total']) ?></span>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" style="margin-top: 8px;" type="submit">
                        Lanjut ke Pembayaran <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="<?= base_url('assets/js/cart.js') ?>?v=1.1"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
