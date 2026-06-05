<?php
require_once __DIR__ . '/../includes/functions.php';

$db = db();
$action = $_GET['action'] ?? 'get';

function assert_cart_item(PDO $db, int $itemId): array
{
    $cartId = get_or_create_cart($db);
    $stmt = $db->prepare('SELECT * FROM cart_items WHERE id = ? AND cart_id = ?');
    $stmt->execute([$itemId, $cartId]);
    $item = $stmt->fetch();
    if (!$item) {
        json_response(false, null, 'Item keranjang tidak ditemukan', 404);
    }
    return $item;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        json_response(false, null, 'Token CSRF tidak valid', 419);
    }

    if ($action === 'get') {
        json_response(true, get_cart($db));
    }

    $data = request_data();
    $cartId = get_or_create_cart($db);

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $menuId = (int) ($data['menu_id'] ?? 0);
        $sauceId = !empty($data['sauce_id']) ? (int) $data['sauce_id'] : null;
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $notes = sanitize($data['notes'] ?? '');

        $menuStmt = $db->prepare('SELECT name, stock, is_active FROM menus WHERE id = ?');
        $menuStmt->execute([$menuId]);
        $menuItem = $menuStmt->fetch();
        if (!$menuItem || !$menuItem['is_active']) {
            json_response(false, null, 'Menu tidak tersedia', 422);
        }
        if ($menuItem['stock'] !== null) {
            $existingQtyStmt = $db->prepare('SELECT SUM(quantity) FROM cart_items WHERE cart_id = ? AND menu_id = ?');
            $existingQtyStmt->execute([$cartId, $menuId]);
            $existingQty = (int) $existingQtyStmt->fetchColumn();
            if ($existingQty + $quantity > (int)$menuItem['stock']) {
                json_response(false, null, 'Stok tidak mencukupi untuk ' . $menuItem['name'] . ' (Sisa stok: ' . $menuItem['stock'] . ')', 422);
            }
        }

        $stmt = $db->prepare('SELECT id FROM cart_items WHERE cart_id = ? AND menu_id = ? AND (sauce_id <=> ?) AND COALESCE(notes, "") = ?');
        $stmt->execute([$cartId, $menuId, $sauceId, $notes]);
        $existing = $stmt->fetchColumn();
        if ($existing) {
            $update = $db->prepare('UPDATE cart_items SET quantity = quantity + ? WHERE id = ?');
            $update->execute([$quantity, $existing]);
        } else {
            $insert = $db->prepare('INSERT INTO cart_items (cart_id, menu_id, sauce_id, quantity, notes) VALUES (?, ?, ?, ?, ?)');
            $insert->execute([$cartId, $menuId, $sauceId, $quantity, $notes]);
        }
        json_response(true, get_cart($db), 'Item ditambahkan');
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $itemId = (int) ($data['cart_item_id'] ?? 0);
        $item = assert_cart_item($db, $itemId);
        $quantity = (int) ($data['quantity'] ?? 1);
        if ($quantity <= 0) {
            $db->prepare('DELETE FROM cart_items WHERE id = ?')->execute([$itemId]);
        } else {
            $menuStmt = $db->prepare('SELECT name, stock FROM menus WHERE id = ?');
            $menuStmt->execute([$item['menu_id']]);
            $menuItem = $menuStmt->fetch();
            if ($menuItem && $menuItem['stock'] !== null && $quantity > (int)$menuItem['stock']) {
                json_response(false, null, 'Stok tidak mencukupi untuk ' . $menuItem['name'] . ' (Sisa stok: ' . $menuItem['stock'] . ')', 422);
            }
            $db->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$quantity, $itemId]);
        }
        json_response(true, get_cart($db), 'Keranjang diperbarui');
    }

    if ($action === 'remove' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $itemId = (int) ($data['cart_item_id'] ?? 0);
        assert_cart_item($db, $itemId);
        $db->prepare('DELETE FROM cart_items WHERE id = ?')->execute([$itemId]);
        json_response(true, get_cart($db), 'Item dihapus');
    }

    if ($action === 'clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $db->prepare('DELETE FROM cart_items WHERE cart_id = ?')->execute([$cartId]);
        json_response(true, get_cart($db), 'Keranjang dikosongkan');
    }

    json_response(false, null, 'Action tidak dikenal', 404);
} catch (PDOException $e) {
    error_log('[API cart] ' . $e->getMessage());
    json_response(false, null, 'Terjadi kesalahan server', 500);
}
