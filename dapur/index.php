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
$pageTitle = 'Kitchen Display';
$bodyClass = 'dapur-body';
require __DIR__ . '/../includes/header.php';
?>
<section class="kds">
    <div class="kds-header">
        <div><h1>Kitchen Display</h1><p>Cabang aktif: <?= e(array_values(array_filter($branches, fn($b) => (int) $b['id'] === $branchId))[0]['name'] ?? '') ?></p></div>
        <form method="post"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><select name="branch_id" onchange="this.form.submit()"><?php foreach ($branches as $b): ?><option value="<?= (int) $b['id'] ?>" <?= $branchId === (int) $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option><?php endforeach; ?></select></form>
    </div>
    <div class="kds-grid" data-kds data-branch-id="<?= $branchId ?>"></div>
</section>
<script src="<?= base_url('assets/js/dapur.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
