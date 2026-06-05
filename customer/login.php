<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<section class="section" style="min-height: calc(100vh - var(--header-height)); display: flex; align-items: center; background: url('https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=1600&q=80') center/cover; position: relative;">
    <div style="position: absolute; inset: 0; background: rgba(26, 28, 35, 0.85); backdrop-filter: blur(8px);"></div>
    <div class="container auth-container" style="position: relative; z-index: 10;">
        <form class="card auth-card" data-login-form>
            <div style="width: 64px; height: 64px; background: var(--primary-light); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 16px; font-size: 1.8rem; color: var(--primary-dark);">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <h1>Selamat Datang</h1>
            <p>Silakan masuk ke akun Anda untuk mulai memesan</p>
            
            <div class="form-grid">
                <div class="form-field">
                    <label>Email atau Nomor Telepon</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-user" style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--gray);"></i>
                        <input name="identity" required placeholder="email@contoh.com / 0812..." style="padding-left: 44px;">
                    </div>
                </div>
                <div class="form-field">
                    <label>Password</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--gray);"></i>
                        <input type="password" name="password" required placeholder="Masukkan password Anda" style="padding-left: 44px;">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" name="remember" value="1" style="width: 18px; height: 18px; accent-color: var(--primary-dark); min-height: auto;">
                        Ingat saya (30 hari)
                    </label>
                </div>
                
                <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 16px; font-size: 1.1rem; padding: 14px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                </button>
                
                <div style="text-align: center; margin-top: 16px; font-size: 0.95rem;">
                    Belum punya akun? <a href="<?= base_url('customer/register.php') ?>" style="color: var(--primary-dark); font-weight: 600;">Daftar di sini</a>
                </div>
            </div>
        </form>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Rate limiting state
  let loginAttempts = 0;
  let lockUntil = 0;

  qs('[data-login-form]')?.addEventListener('submit', async (event) => {
    event.preventDefault();
    
    // Rate limiting check
    if (Date.now() < lockUntil) {
        const waitSeconds = Math.ceil((lockUntil - Date.now()) / 1000);
        toast(`Terlalu banyak percobaan. Coba lagi dalam ${waitSeconds} detik.`, 'error');
        return;
    }
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.classList.add('loading');
    
    const data = Object.fromEntries(new FormData(event.target).entries());
    try {
      const result = await apiFetch('api/auth.php?action=login', { method: 'POST', body: JSON.stringify(data) });
      loginAttempts = 0; // reset
      const routes = { admin: 'admin/index.php', kasir: 'kasir/index.php', dapur: 'dapur/index.php', customer: 'customer/menu.php' };
      window.location.href = `${APP.baseUrl}/${routes[result.role] || 'customer/menu.php'}`;
    } catch (error) { 
      submitBtn.classList.remove('loading');
      loginAttempts++;
      
      // Lock for 30s after 3 failed attempts
      if (loginAttempts >= 3) {
          lockUntil = Date.now() + 30000;
          toast(`Terlalu banyak percobaan gagal. Akun dikunci selama 30 detik.`, 'error');
      } else {
          toast(error.message, 'error'); 
      }
    }
  });
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
