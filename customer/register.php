<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Register';
require __DIR__ . '/../includes/header.php';
?>
<section class="section" style="min-height: calc(100vh - var(--header-height)); display: flex; align-items: center; background: url('https://images.unsplash.com/photo-1562967914-608f82629710?auto=format&fit=crop&w=1600&q=80') center/cover; position: relative;">
    <div style="position: absolute; inset: 0; background: rgba(26, 28, 35, 0.85); backdrop-filter: blur(8px);"></div>
    <div class="container auth-container" style="position: relative; z-index: 10;">
        <form class="card auth-card" data-register-form>
            <div style="width: 64px; height: 64px; background: var(--primary-light); border-radius: 50%; display: grid; place-items: center; margin: 0 auto 16px; font-size: 1.8rem; color: var(--primary-dark);">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h1>Daftar Akun Baru</h1>
            <p>Buat akun untuk mempermudah pesanan Anda berikutnya</p>
            
            <div class="form-grid">
                <div class="form-field">
                    <label>Nama Lengkap</label>
                    <div style="position: relative;">
                        <i class="fa-regular fa-id-card" style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--gray);"></i>
                        <input name="name" required placeholder="Budi Santoso" style="padding-left: 44px;">
                    </div>
                </div>
                
                <div class="grid grid-2" style="gap: 16px;">
                    <div class="form-field">
                        <label>Nomor HP / WhatsApp</label>
                        <div style="position: relative;">
                            <i class="fa-solid fa-phone" style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--gray);"></i>
                            <input name="phone" required placeholder="0812..." style="padding-left: 44px;">
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Email (Opsional)</label>
                        <div style="position: relative;">
                            <i class="fa-regular fa-envelope" style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--gray);"></i>
                            <input type="email" name="email" placeholder="budi@email.com" style="padding-left: 44px;">
                        </div>
                    </div>
                </div>
                
                <div class="form-field">
                    <label>Password</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; top: 50%; left: 16px; transform: translateY(-50%); color: var(--gray);"></i>
                        <input type="password" name="password" minlength="6" required placeholder="Minimal 6 karakter" style="padding-left: 44px;">
                    </div>
                </div>
                
                <button class="btn btn-primary" type="submit" style="width: 100%; margin-top: 16px; font-size: 1.1rem; padding: 14px;">
                    <i class="fa-solid fa-user-check"></i> Daftar Sekarang
                </button>
                
                <div style="text-align: center; margin-top: 16px; font-size: 0.95rem;">
                    Sudah punya akun? <a href="<?= base_url('customer/login.php') ?>" style="color: var(--primary-dark); font-weight: 600;">Login di sini</a>
                </div>
            </div>
        </form>
    </div>
</section>
<script>
// Rate limiting state
let registerAttempts = 0;
let lockUntil = 0;

qs('[data-register-form]')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  
  // Rate limiting check
  if (Date.now() < lockUntil) {
      const waitSeconds = Math.ceil((lockUntil - Date.now()) / 1000);
      toast(`Terlalu banyak percobaan. Coba lagi dalam ${waitSeconds} detik.`, 'error');
      return;
  }
  
  const submitBtn = event.target.querySelector('button[type="submit"]');
  submitBtn.classList.add('loading');
  
  try {
    await apiFetch('api/auth.php?action=register', { method: 'POST', body: JSON.stringify(Object.fromEntries(new FormData(event.target).entries())) });
    registerAttempts = 0;
    window.location.href = `${APP.baseUrl}/customer/menu.php`;
  } catch (error) { 
    submitBtn.classList.remove('loading');
    registerAttempts++;
    
    // Lock for 60s after 3 failed attempts
    if (registerAttempts >= 3) {
        lockUntil = Date.now() + 60000;
        toast(`Terlalu banyak percobaan gagal. Pendaftaran dikunci selama 60 detik.`, 'error');
    } else {
        toast(error.message, 'error'); 
    }
  }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
