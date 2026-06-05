<?php
require_once __DIR__ . '/../includes/functions.php';
$db = db();
$code = sanitize($_GET['code'] ?? '');
$stmt = $db->prepare('SELECT o.*, b.name branch_name, COALESCE(p.payment_status, "unpaid") payment_status FROM orders o JOIN branches b ON b.id = o.branch_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.order_code = ?');
$stmt->execute([$code]);
$order = $stmt->fetch();
if (!$order) {
    flash('error', 'Pesanan tidak ditemukan');
    redirect(base_url('customer/menu.php'));
}
$items = $db->prepare('SELECT od.*, m.name menu_name, s.name sauce_name FROM order_details od JOIN menus m ON m.id = od.menu_id LEFT JOIN sauces s ON s.id = od.sauce_id WHERE od.order_id = ?');
$items->execute([$order['id']]);
$pageTitle = 'Status Pesanan';
require __DIR__ . '/../includes/header.php';
?>
<section class="section" data-order-code="<?= e($order['order_code']) ?>">
    <div class="container">
        <div class="page-title">
            <h1><?= e($order['order_code']) ?></h1>
            <span class="badge <?= get_status_color($order['status']) ?>" data-live-status><?= get_status_label($order['status']) ?></span>
        </div>
        <div class="card">
            <div class="stepper">
                <?php foreach (['Pending', 'Confirmed', 'Cooking', 'Ready', 'Completed'] as $i => $step): ?>
                    <div class="step" data-step data-step-index="<?= $i ?>"><?= e($step) ?></div>
                <?php endforeach; ?>
            </div>
            <p><?= e($order['branch_name']) ?> - <?= e($order['order_type']) ?> - Payment <?= e($order['payment_status']) ?></p>
            <?php foreach ($items->fetchAll() as $item): ?>
                <p><?= (int) $item['quantity'] ?>x <?= e($item['menu_name']) ?> <?= $item['sauce_name'] ? '(' . e($item['sauce_name']) . ')' : '' ?> <strong><?= format_rupiah((float) $item['subtotal']) ?></strong></p>
            <?php endforeach; ?>
            <h2>Total <?= format_rupiah((float) $order['total']) ?></h2>
            <?php if ($order['status'] === 'completed' && user_role() === 'customer'): ?>
                <form class="form-grid" method="post" action="<?= base_url('api/order.php?action=review') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                    <div class="form-field"><label>Rating</label><input name="rating" type="number" min="1" max="5" value="5"></div>
                    <div class="form-field"><label>Komentar</label><textarea name="comment"></textarea></div>
                    <button class="btn btn-primary">Kirim Review</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="<?= base_url('assets/js/order.js') ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
