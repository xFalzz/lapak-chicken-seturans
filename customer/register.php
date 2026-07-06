<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Daftar Akun';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container" style="padding: 0 var(--container-pad);">
        <div class="auth-split">
            
            <!-- Brand Panel (kiri) -->
            <div class="auth-text-panel">
                <div style="font-weight:800;font-size:1.8rem;color:var(--on-surface);margin-bottom:48px;">
                    <span style="color:var(--primary-container);">Lapak</span> Chicken
                </div>
                <h2>Gabung dengan <span>MakanGes</span> &<br>Nikmati Ayam Krispi Terbaik!</h2>
                <p>Nikmati diskon langsung sebesar Rp 20.000 untuk pesanan pertamamu, dapatkan penawaran eksklusif setiap harinya hanya di MakanGes Lapak Chicken.</p>
                
                <div class="auth-feature-cards">
                    <div class="auth-feature-card">
                        <i class="fa-solid fa-bolt"></i>
                        <h4>Proses Cepat</h4>
                        <p>Pesan dan bayar dalam hitungan detik.</p>
                    </div>
                    <div class="auth-feature-card">
                        <i class="fa-solid fa-gift"></i>
                        <h4>Voucher Gratis</h4>
                        <p>Dapatkan voucher untuk pengguna baru.</p>
                    </div>
                </div>
            </div>

            <!-- Form Panel (kanan) -->
            <div class="auth-form-panel">
                <div style="text-align:center;margin-bottom:24px;">
                    <h1>Buat Akun Baru</h1>
                    <p style="color:var(--secondary);font-size:0.95rem;">Sudah punya akun? <a href="<?= base_url('customer/login.php') ?>" style="color:#B29500;font-weight:700;">Masuk di sini</a></p>
                </div>

                <div class="auth-social-btns" style="margin-bottom:24px;">
                    <button type="button" class="btn btn-social"><i class="fa-brands fa-google"></i> Google</button>
                    <button type="button" class="btn btn-social"><i class="fa-brands fa-facebook"></i> Facebook</button>
                </div>

                <div class="auth-divider" style="margin-bottom:24px;">Atau gunakan email Anda</div>

                <form class="form-grid" data-register-form novalidate>
                    <div class="form-field">
                        <label for="reg-name">Nama Lengkap</label>
                        <input
                            id="reg-name"
                            name="name"
                            required
                            placeholder="Masukkan nama lengkap Anda"
                            autocomplete="name">
                    </div>

                    <div class="form-field">
                        <label for="reg-email">Email</label>
                        <input
                            id="reg-email"
                            type="email"
                            name="email"
                            placeholder="user@lapak-chicken.com"
                            autocomplete="email">
                    </div>

                    <div class="form-field">
                        <label for="reg-phone">No Telepon (WhatsApp) <span style="color:var(--error);">*</span></label>
                        <input
                            id="reg-phone"
                            name="phone"
                            type="tel"
                            required
                            placeholder="0812 3456 7890"
                            autocomplete="tel">
                    </div>

                    <div class="form-field">
                        <label for="reg-password">Kata Sandi <span style="color:var(--error);">*</span></label>
                        <div style="position:relative;">
                            <input
                                id="reg-password"
                                type="password"
                                name="password"
                                minlength="6"
                                required
                                placeholder="Minimal 6 karakter"
                                autocomplete="new-password"
                                style="width:100%;">
                            <button type="button" id="toggleRegPwd" aria-label="Tampilkan password" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--secondary);cursor:pointer;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:12px;margin-top:8px;">
                        <input type="checkbox" id="reg-terms" required style="width:16px;height:16px;accent-color:var(--primary-container);margin-top:4px;">
                        <label for="reg-terms" style="font-size:0.85rem;color:var(--secondary);font-weight:400;line-height:1.5;">
                            Saya menyetujui seluruh <a href="#" style="color:var(--on-surface);font-weight:700;">Syarat & Ketentuan Lapak Chicken</a> serta mengetahui bagaimana <a href="#" style="color:var(--on-surface);font-weight:700;">Kebijakan Privasinya</a>
                        </label>
                    </div>

                    <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;margin-top:16px;min-height:48px;" id="registerSubmitBtn">
                        Daftar Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // === Toggle Password Visibility ===
    const toggleBtn = document.getElementById('toggleRegPwd');
    const pwdInput  = document.getElementById('reg-password');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            toggleBtn.querySelector('i').className = isHidden ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    }

    // === Register Form Submit ===
    let registerAttempts = 0;
    let lockUntil = 0;

    qs('[data-register-form]')?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!qs('#reg-terms').checked) {
            toast('Silakan setujui Syarat & Ketentuan', 'error');
            return;
        }

        if (Date.now() < lockUntil) {
            const wait = Math.ceil((lockUntil - Date.now()) / 1000);
            toast(`Terlalu banyak percobaan. Coba lagi dalam ${wait} detik.`, 'error');
            return;
        }

        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('loading');

        try {
            await apiFetch('api/auth.php?action=register', {
                method: 'POST',
                body: JSON.stringify(Object.fromEntries(new FormData(form).entries()))
            });
            registerAttempts = 0;
            toast('Pendaftaran berhasil! Selamat datang 🎉', 'success');
            setTimeout(() => {
                window.location.href = `${APP.baseUrl}/customer/menu.php`;
            }, 1000);
        } catch (error) {
            submitBtn.classList.remove('loading');
            registerAttempts++;
            if (registerAttempts >= 3) {
                lockUntil = Date.now() + 60000;
                toast('Terlalu banyak percobaan. Pendaftaran dikunci 60 detik.', 'error');
            } else {
                toast(error.message || 'Pendaftaran gagal. Periksa kembali data Anda.', 'error');
            }
        }
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
