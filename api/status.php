<?php
require_once __DIR__ . '/../includes/functions.php';

$db = db();
$action = $_GET['action'] ?? 'count';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        json_response(false, null, 'Token CSRF tidak valid', 419);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_json_role(['admin', 'kasir', 'dapur']);
        $data = request_data();
        $status = sanitize($data['status'] ?? '');
        if (!in_array($status, ['pending', 'confirmed', 'cooking', 'ready', 'completed', 'cancelled'], true)) {
            json_response(false, null, 'Status tidak valid', 422);
        }
        $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, (int) $data['order_id']]);
        json_response(true, null, 'Status diperbarui');
    }

    if ($action === 'pay' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_json_role(['admin', 'kasir']);
        $data = request_data();
        $orderId = (int) ($data['order_id'] ?? 0);
        $method = sanitize($data['payment_method'] ?? 'Cash');
        $amount = (float) ($data['amount_paid'] ?? 0);
        $db->beginTransaction();
        $stmt = $db->prepare('SELECT total FROM orders WHERE id = ? FOR UPDATE');
        $stmt->execute([$orderId]);
        $total = (float) $stmt->fetchColumn();
        if (!$total) {
            json_response(false, null, 'Pesanan tidak ditemukan', 404);
        }
        if ($method === 'Cash' && $amount < $total) {
            json_response(false, null, 'Uang diterima kurang dari total', 422);
        }
        $pay = $db->prepare('INSERT INTO payments (order_id, payment_method, payment_status, amount_paid, paid_at) VALUES (?, ?, "paid", ?, NOW()) ON DUPLICATE KEY UPDATE payment_method = VALUES(payment_method), payment_status = "paid", amount_paid = VALUES(amount_paid), paid_at = NOW()');
        $pay->execute([$orderId, $method, $amount ?: $total]);
        $db->prepare('UPDATE orders SET status = "completed" WHERE id = ?')->execute([$orderId]);
        $db->commit();
        json_response(true, ['order_id' => $orderId], 'Pembayaran berhasil');
    }

    if ($action === 'count') {
        require_json_role(['admin', 'kasir', 'dapur']);
        $branchId = (int) ($_GET['branch_id'] ?? 0);
        $stmt = $db->prepare('SELECT status, COUNT(*) total FROM orders WHERE branch_id = ? AND status IN ("pending","confirmed","cooking","ready") GROUP BY status');
        $stmt->execute([$branchId]);
        json_response(true, $stmt->fetchAll());
    }

    json_response(false, null, 'Action tidak dikenal', 404);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('[API status] ' . $e->getMessage());
    json_response(false, null, 'Terjadi kesalahan server', 500);
}
