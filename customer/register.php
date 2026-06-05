<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'Register';
require __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <form class="card form-grid" style="max-width:560px;margin:auto" data-register-form>
            <h1>Register Customer</h1>
            <div class="form-field"><label>Nama</label><input name="name" required></div>
            <div class="form-field"><label>Email opsional</label><input type="email" name="email"></div>
            <div class="form-field"><label>Phone</label><input name="phone" required></div>
            <div class="form-field"><label>Password</label><input type="password" name="password" minlength="6" required></div>
            <button class="btn btn-primary" type="submit">Daftar</button>
        </form>
    </div>
</section>
<script>
qs('[data-register-form]')?.addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    await apiFetch('api/auth.php?action=register', { method: 'POST', body: JSON.stringify(Object.fromEntries(new FormData(event.target).entries())) });
    window.location.href = `${APP.baseUrl}/customer/menu.php`;
  } catch (error) { toast(error.message, 'error'); }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
