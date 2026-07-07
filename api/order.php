<?php
require_once __DIR__ . '/../includes/functions.php';

$db = db();
$action = $_GET['action'] ?? 'status';

function order_payload(PDO $db, string $code): array
{
    $stmt = $db->prepare('SELECT o.*, b.name branch_name, p.payment_method, COALESCE(p.payment_status, "unpaid") payment_status FROM orders o JOIN branches b ON b.id = o.branch_id LEFT JOIN payments p ON p.order_id = o.id WHERE o.order_code = ? LIMIT 1');
    $stmt->execute([$code]);
    $order = $stmt->fetch();
    if (!$order) {
        json_response(false, null, 'Pesanan tidak ditemukan', 404);
    }
    $items = $db->prepare('SELECT od.*, m.name menu_name, m.price, s.name sauce_name, COALESCE(s.price_extra,0) price_extra FROM order_details od JOIN menus m ON m.id = od.menu_id LEFT JOIN sauces s ON s.id = od.sauce_id WHERE od.order_id = ?');
    $items->execute([$order['id']]);
    $steps = ['pending', 'confirmed', 'cooking', 'ready', 'completed'];
    $order['items'] = $items->fetchAll();
    $order['status_label'] = get_status_label($order['status']);
    $order['progress_index'] = array_search($order['status'], $steps, true);
    $order['progress_index'] = $order['progress_index'] === false ? 0 : $order['progress_index'];
    return $order;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        json_response(false, null, 'Token CSRF tidak valid', 419);
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!is_logged_in()) {
            json_response(false, null, 'WAJIB LOGIN UNTUK MELANJUTKAN PEMBAYARAN', 401);
        }
        $data = request_data();
        $branchId = (int) ($data['branch_id'] ?? $_SESSION['branch_id'] ?? 0);
        $cart = get_cart($db);
        if (!$branchId || !$cart['items']) {
            json_response(false, null, 'Cabang atau keranjang belum valid', 422);
        }
        $taxRate = (float) get_branch_setting($db, $branchId, 'tax_rate', DEFAULT_TAX_RATE);
        $total = $cart['subtotal'] + ($cart['subtotal'] * $taxRate);

        $db->beginTransaction();
        try {
            foreach ($cart['items'] as $item) {
                $menuStmt = $db->prepare('SELECT name, stock, is_active FROM menus WHERE id = ? FOR UPDATE');
                $menuStmt->execute([$item['menu_id']]);
                $menuItem = $menuStmt->fetch();
                if (!$menuItem || !$menuItem['is_active']) {
                    throw new Exception('Menu ' . ($menuItem['name'] ?? 'tidak diketahui') . ' tidak tersedia');
                }
                if ($menuItem['stock'] !== null) {
                    if ($item['quantity'] > (int)$menuItem['stock']) {
                        throw new Exception('Stok ' . $menuItem['name'] . ' tidak mencukupi (Tersisa: ' . $menuItem['stock'] . ')');
                    }
                    $updateStock = $db->prepare('UPDATE menus SET stock = stock - ? WHERE id = ?');
                    $updateStock->execute([$item['quantity'], $item['menu_id']]);
                }
            }

            $code = generate_order_code($db, $branchId);

            $stmt = $db->prepare('INSERT INTO orders (branch_id, user_id, order_code, customer_name, customer_phone, order_type, status, total) VALUES (?, ?, ?, ?, ?, ?, "pending", ?)');
            $stmt->execute([
                $branchId,
                current_user()['id'] ?? null,
                $code,
                sanitize($data['customer_name'] ?? ''),
                sanitize($data['customer_phone'] ?? ''),
                sanitize($data['order_type'] ?? 'takeaway'),
                $total,
            ]);
            $orderId = (int) $db->lastInsertId();
            $detail = $db->prepare('INSERT INTO order_details (order_id, menu_id, sauce_id, spice_level, quantity, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($cart['items'] as $item) {
                $detail->execute([$orderId, $item['menu_id'], $item['sauce_id'], $item['spice_level'] ?? '0', $item['quantity'], $item['subtotal'], $item['notes'] ?? '']);
            }
            $payment = $db->prepare('INSERT INTO payments (order_id, payment_method, payment_status, amount_paid) VALUES (?, ?, "unpaid", 0)');
            $payment->execute([$orderId, sanitize($data['payment_method'] ?? 'Cash')]);
            $db->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cart['cart_id']]);
            
            $db->commit();
            json_response(true, ['order_id' => $orderId, 'order_code' => $code], 'Pesanan dibuat');
        } catch (Exception $e) {
            $db->rollBack();
            json_response(false, null, $e->getMessage(), 422);
        }
    }

    if ($action === 'status') {
        json_response(true, order_payload($db, sanitize($_GET['code'] ?? '')));
    }

    if ($action === 'list') {
        require_json_role(['admin', 'kasir']);
        $where = ['1=1'];
        $params = [];
        foreach (['branch_id', 'status', 'order_type'] as $field) {
            if (!empty($_GET[$field])) {
                $where[] = "o.$field = ?";
                $params[] = sanitize($_GET[$field]);
            }
        }
        if (!empty($_GET['date_from'])) {
            $where[] = 'DATE(o.created_at) >= ?';
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where[] = 'DATE(o.created_at) <= ?';
            $params[] = $_GET['date_to'];
        }
        $stmt = $db->prepare('SELECT o.*, b.name branch_name, COALESCE(p.payment_status, "unpaid") payment_status, COUNT(od.id) items_count FROM orders o JOIN branches b ON b.id = o.branch_id LEFT JOIN payments p ON p.order_id = o.id LEFT JOIN order_details od ON od.order_id = o.id WHERE ' . implode(' AND ', $where) . ' GROUP BY o.id ORDER BY o.created_at DESC LIMIT 200');
        $stmt->execute($params);
        json_response(true, $stmt->fetchAll());
    }

    if ($action === 'kitchen') {
        require_json_role(['admin', 'dapur']);
        $branchId = (int) ($_GET['branch_id'] ?? $_SESSION['dapur_branch_id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM orders WHERE branch_id = ? AND status IN ("confirmed", "cooking") ORDER BY created_at ASC');
        $stmt->execute([$branchId]);
        $orders = $stmt->fetchAll();
        $itemsStmt = $db->prepare('SELECT od.*, m.name menu_name, s.name sauce_name FROM order_details od JOIN menus m ON m.id = od.menu_id LEFT JOIN sauces s ON s.id = od.sauce_id WHERE od.order_id = ?');
        foreach ($orders as &$order) {
            $itemsStmt->execute([$order['id']]);
            $order['items'] = $itemsStmt->fetchAll();
            $order['elapsed_minutes'] = floor(max(0, time() - strtotime($order['created_at'])) / 60);
            $order['elapsed_label'] = time_ago($order['created_at']);
        }
        json_response(true, $orders);
    }

    if ($action === 'review' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_json_role(['customer']);
        $data = request_data();
        $stmt = $db->prepare('INSERT INTO reviews (user_id, order_id, rating, comment) VALUES (?, ?, ?, ?)');
        $stmt->execute([current_user()['id'], (int) $data['order_id'], max(1, min(5, (int) $data['rating'])), sanitize($data['comment'] ?? '')]);
        json_response(true, null, 'Review tersimpan');
    }

    json_response(false, null, 'Action tidak dikenal', 404);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log('[API order] ' . $e->getMessage());
    json_response(false, null, 'Terjadi kesalahan server', 500);
}
