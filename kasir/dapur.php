<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['kasir', 'admin']);
$db = db();
$branches = branch_options($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $_SESSION['dapur_branch_id'] = (int) $_POST['branch_id'];
    redirect('dapur.php');
}
$branchId = (int) ($_SESSION['dapur_branch_id'] ?? ($branches[0]['id'] ?? 1));
$branchName = '';
foreach ($branches as $b) {
    if ((int) $b['id'] === $branchId) { $branchName = $b['name']; break; }
}
$pageTitle = 'Dapur (KDS)';
$bodyClass = 'kasir-layout';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar-kasir.php';
?>
<section class="content-with-sidebar">
    <div class="kds-header" style="background: #161616; color: #FFFFFF; padding: 24px 28px; border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.06); margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div class="kds-header-left">
            <h1 style="color: #FFFFFF; font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                <span style="width:42px;height:42px;background:var(--primary-container);color:var(--on-primary-container);border-radius:12px;display:grid;place-items:center;font-size:0.9rem;font-weight:800;">LC</span>
                Kitchen Display System
            </h1>
            <p style="color: rgba(255,255,255,0.45); font-size: 0.9rem;"><i class="fa-solid fa-store" style="margin-right:6px;"></i> <?= e($branchName) ?></p>
        </div>
        <div class="kds-header-right" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <span class="kds-live-badge" style="background: rgba(255, 59, 48, 0.15); color: #FF3B30; padding: 6px 14px; border-radius: 99px; font-weight: 800; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 8px;">
                <span style="width: 8px; height: 8px; background: #FF3B30; border-radius: 50%;"></span>
                LIVE
            </span>
            <span class="kds-clock" id="kdsClock" style="font-family: monospace; font-size: 1.25rem; font-weight: 800; color: #FFFFFF;"></span>
            <form method="post" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <select name="branch_id" onchange="this.form.submit()" style="background: #222; color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 8px 12px; border-radius: 8px; font-weight: 600;">
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="kds-grid" data-kds data-branch-id="<?= $branchId ?>" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;"></div>
</section>

<script>
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    const el = document.getElementById('kdsClock');
    if (el) el.textContent = `${h}:${m}:${s}`;
}
updateClock();
setInterval(updateClock, 1000);
</script>
<script src="<?= base_url('assets/js/dapur.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
