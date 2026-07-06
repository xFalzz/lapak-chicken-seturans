<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Masuk ke Akun';
require __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
    <div class="container" style="padding: 0 var(--container-pad);">
        <div class="auth-split">
            
            <!-- Brand Panel (kiri) -->
            <div class="auth-image-panel">
                <div class="auth-image-content">
                    <div class="auth-image-title">FRESH YOUR<br>DAY!</div>
                    <div class="auth-image-subtitle">MakanGes Bareng<br>Lapak Chicken</div>
                    <p style="font-size:0.95rem;opacity:0.9;">Nikmati diskon potongan harga spesial 50% untuk semua Ayam Crispy khusus pengguna Lapak Chicken. Pesan sekarang!</p>
                </div>
            </div>

            <!-- Form Panel (kanan) -->
            <div class="auth-form-panel">
                <div style="text-align:center;margin-bottom:32px;">
                    <div style="display:flex;align-items:center;justify-content:center;gap:10px;font-weight:800;font-size:1.4rem;color:var(--on-surface);margin-bottom:16px;">
                        <span class="brand-icon" style="width:32px;height:32px;background:var(--primary-container);color:var(--on-primary-container);display:grid;place-items:center;border-radius:8px;font-size:0.9rem;">LC</span>
                        Lapak Chicken
                    </div>
                    <h1>Selamat Datang</h1>
                    <p style="color:var(--secondary);font-size:0.95rem;">Bikin pesananmu lebih mudah dan login aplikasimu sekarang juga!</p>
                </div>

                <form class="form-grid" data-login-form novalidate>
                    <div class="form-field">
                        <label for="login-identity">Email <span style="color:var(--error);">*</span></label>
                        <input
                            id="login-identity"
                            name="identity"
                            required
                            placeholder="cs@lapak-chicken.com"
                            autocomplete="username">
                    </div>

                    <div class="form-field">
                        <label for="login-password">Kata Sandi <span style="color:var(--error);">*</span></label>
                        <div style="position:relative;">
                            <input
                                id="login-password"
                                type="password"
                                name="password"
                                required
                                placeholder="Masukkan password Anda"
                                autocomplete="current-password"
                                style="width:100%;">
                            <button type="button" id="toggleLoginPwd" aria-label="Tampilkan password" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--secondary);cursor:pointer;">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;font-weight:600;color:var(--secondary);">
                            <input type="checkbox" name="remember" value="1" style="width:16px;height:16px;accent-color:var(--primary-container);">
                            Ingat akun saya
                        </label>
                        <a href="#" style="font-size:0.88rem;font-weight:600;color:var(--on-surface);">Lupa Password?</a>
                    </div>

                    <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;margin-top:16px;min-height:48px;" id="loginSubmitBtn">
                        LOGIN <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <div class="auth-divider" style="margin-top:32px;">Atau daftar menggunakan</div>

                <div class="auth-social-btns">
                    <button type="button" class="btn btn-social"><i class="fa-brands fa-google"></i> Google</button>
                    <button type="button" class="btn btn-social"><i class="fa-brands fa-facebook"></i> Facebook</button>
                </div>

                <div style="text-align:center;font-size:0.85rem;color:var(--secondary);margin-top:16px;">
                    Belum punya akun Lapak Chicken? <a href="<?= base_url('customer/register.php') ?>" style="color:var(--on-surface);font-weight:700;">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // === Toggle Password Visibility ===
    const toggleBtn = document.getElementById('toggleLoginPwd');
    const pwdInput  = document.getElementById('login-password');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = pwdInput.type === 'password';
            pwdInput.type = isHidden ? 'text' : 'password';
            toggleBtn.querySelector('i').className = isHidden ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    }

    // === Login Form Submit ===
    let loginAttempts = 0;
    let lockUntil = 0;

    qs('[data-login-form]')?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (Date.now() < lockUntil) {
            const wait = Math.ceil((lockUntil - Date.now()) / 1000);
            toast(`Terlalu banyak percobaan. Coba lagi dalam ${wait} detik.`, 'error');
            return;
        }

        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('loading');

        const data = Object.fromEntries(new FormData(form).entries());
        try {
            const result = await apiFetch('api/auth.php?action=login', { method: 'POST', body: JSON.stringify(data) });
            loginAttempts = 0;
            const routes = {
                admin: 'admin/index.php',
                kasir: 'kasir/index.php',
                dapur: 'dapur/index.php',
                customer: 'index.php'
            };
            window.location.href = `${APP.baseUrl}/${routes[result.role] || 'index.php'}`;
        } catch (error) {
            submitBtn.classList.remove('loading');
            loginAttempts++;
            if (loginAttempts >= 3) {
                lockUntil = Date.now() + 30000;
                toast('Terlalu banyak percobaan gagal. Akun dikunci 30 detik.', 'error');
            } else {
                toast(error.message || 'Login gagal. Periksa kembali data Anda.', 'error');
            }
        }
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
