<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Login';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <form class="card form-grid" style="max-width:520px;margin:auto" data-login-form>
            <h1>Login</h1>
            <div class="form-field"><label>Email atau phone</label><input name="identity" required></div>
            <div class="form-field"><label>Password</label><input type="password" name="password" required></div>
            <label><input type="checkbox" name="remember" value="1"> Remember me 30 hari</label>
            <button class="btn btn-primary" type="submit">Masuk</button>
            <a href="<?= base_url('customer/register.php') ?>">Belum punya akun? Register</a>
        </form>
    </div>
</section>
<script>
qs('[data-login-form]')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const data = Object.fromEntries(new FormData(event.target).entries());
  try {
    const result = await apiFetch('api/auth.php?action=login', { method: 'POST', body: JSON.stringify(data) });
    const routes = { admin: 'admin/index.php', kasir: 'kasir/index.php', dapur: 'dapur/index.php', customer: 'customer/menu.php' };
    window.location.href = `${APP.baseUrl}/${routes[result.role] || 'customer/menu.php'}`;
  } catch (error) { toast(error.message, 'error'); }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
