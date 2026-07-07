<?php
require_once __DIR__ . '/../includes/functions.php';
require_role(['dapur', 'admin']);
$db = db();
$branches = branch_options($db);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $_SESSION['dapur_branch_id'] = (int) $_POST['branch_id'];
    redirect('index.php');
}
$branchId = (int) ($_SESSION['dapur_branch_id'] ?? ($branches[0]['id'] ?? 1));
$branchName = '';
foreach ($branches as $b) {
    if ((int) $b['id'] === $branchId) { $branchName = $b['name']; break; }
}
$pageTitle = 'Kitchen Display';
$bodyClass = 'dapur-body';
require __DIR__ . '/../includes/header.php';
?>
<section class="kds">
    <!-- KDS Header -->
    <div class="kds-header">
        <div class="kds-header-left">
            <h1>
                <span style="width:42px;height:42px;background:var(--primary-container);color:var(--on-primary-container);border-radius:12px;display:grid;place-items:center;font-size:0.9rem;font-weight:800;">LC</span>
                Kitchen Display
            </h1>
            <p><i class="fa-solid fa-store" style="margin-right:6px;"></i> <?= e($branchName) ?></p>
        </div>
        <div class="kds-header-right">
            <span class="kds-live-badge">
                <span class="kds-live-dot"></span>
                LIVE
            </span>
            <span class="kds-clock" id="kdsClock"></span>
            <form method="post" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <select name="branch_id" onchange="this.form.submit()">
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- KDS Grid -->
    <div class="kds-grid" data-kds data-branch-id="<?= $branchId ?>"></div>
</section>

<script>
// Live Clock
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
