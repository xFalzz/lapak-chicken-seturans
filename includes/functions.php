<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sanitize(string $input): string
{
    return trim(strip_tags($input));
}

function format_rupiah(float $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function flash(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

function get_flash(string $key): ?string
{
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function json_response(bool $success, mixed $data = null, string $message = 'OK', int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message]);
    exit;
}

function request_data(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (str_contains($contentType, 'application/json')) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw ?: '{}', true);
        return is_array($json) ? $json : [];
    }

    return $_POST;
}

function get_status_label(string $status): string
{
    return [
        'pending' => 'Pending',
        'confirmed' => 'Dikonfirmasi',
        'cooking' => 'Dimasak',
        'ready' => 'Siap',
        'completed' => 'Selesai',
        'cancelled' => 'Batal',
    ][$status] ?? ucfirst($status);
}

function get_status_color(string $status): string
{
    return [
        'pending' => 'badge-gray',
        'confirmed' => 'badge-blue',
        'cooking' => 'badge-orange',
        'ready' => 'badge-green',
        'completed' => 'badge-black',
        'cancelled' => 'badge-red',
    ][$status] ?? 'badge-gray';
}

function generate_order_code(int $branch_id): string
{
    $db = db();
    $prefix = ORDER_PREFIX . $branch_id . '-' . date('Ymd') . '-';
    $stmt = $db->prepare('SELECT COUNT(*) + 1 FROM orders WHERE branch_id = ? AND DATE(created_at) = CURDATE()');
    $stmt->execute([$branch_id]);
    return $prefix . str_pad((string) $stmt->fetchColumn(), 4, '0', STR_PAD_LEFT);
}

function time_ago(string $datetime): string
{
    $seconds = max(0, time() - strtotime($datetime));
    if ($seconds < 60) {
        return $seconds . ' detik lalu';
    }
    $minutes = floor($seconds / 60);
    if ($minutes < 60) {
        return $minutes . ' menit lalu';
    }
    $hours = floor($minutes / 60);
    if ($hours < 24) {
        return $hours . ' jam lalu';
    }
    return floor($hours / 24) . ' hari lalu';
}

function get_or_create_cart(PDO $db): int
{
    $user = current_user();
    if ($user) {
        $stmt = $db->prepare('SELECT id FROM carts WHERE user_id = ? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$user['id']]);
        $cartId = $stmt->fetchColumn();
        if ($cartId) {
            return (int) $cartId;
        }
        $stmt = $db->prepare('INSERT INTO carts (user_id, session_id) VALUES (?, ?)');
        $stmt->execute([$user['id'], session_id()]);
        return (int) $db->lastInsertId();
    }

    $_SESSION[SESSION_CART_KEY] ??= session_id();
    $sessionId = $_SESSION[SESSION_CART_KEY];
    $stmt = $db->prepare('SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL ORDER BY id DESC LIMIT 1');
    $stmt->execute([$sessionId]);
    $cartId = $stmt->fetchColumn();
    if ($cartId) {
        return (int) $cartId;
    }
    $stmt = $db->prepare('INSERT INTO carts (session_id) VALUES (?)');
    $stmt->execute([$sessionId]);
    return (int) $db->lastInsertId();
}

function get_cart(PDO $db): array
{
    $cartId = get_or_create_cart($db);
    $stmt = $db->prepare(
        'SELECT ci.id, ci.menu_id, ci.sauce_id, ci.quantity, ci.notes, m.name menu_name, m.price,
                s.name sauce_name, COALESCE(s.price_extra, 0) price_extra,
                ((m.price + COALESCE(s.price_extra, 0)) * ci.quantity) subtotal
         FROM cart_items ci
         JOIN menus m ON m.id = ci.menu_id
         LEFT JOIN sauces s ON s.id = ci.sauce_id
         WHERE ci.cart_id = ?
         ORDER BY ci.id DESC'
    );
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();
    $subtotal = array_reduce($items, fn($sum, $item) => $sum + (float) $item['subtotal'], 0.0);
    $branchId = (int) ($_SESSION['branch_id'] ?? 1);
    $taxRate = (float) get_branch_setting($db, $branchId, 'tax_rate', DEFAULT_TAX_RATE);
    $tax = $subtotal * $taxRate;

    return [
        'cart_id' => $cartId,
        'items' => $items,
        'count' => array_sum(array_map(fn($item) => (int) $item['quantity'], $items)),
        'subtotal' => $subtotal,
        'tax_rate' => $taxRate,
        'tax' => $tax,
        'total' => $subtotal + $tax,
    ];
}

function calculate_cart_total(PDO $db, int $cart_id): float
{
    $stmt = $db->prepare(
        'SELECT SUM((m.price + COALESCE(s.price_extra, 0)) * ci.quantity)
         FROM cart_items ci
         JOIN menus m ON m.id = ci.menu_id
         LEFT JOIN sauces s ON s.id = ci.sauce_id
         WHERE ci.cart_id = ?'
    );
    $stmt->execute([$cart_id]);
    return (float) ($stmt->fetchColumn() ?: 0);
}

function get_branch_setting(PDO $db, int $branch_id, string $key, mixed $default = null): mixed
{
    $stmt = $db->prepare('SELECT `value` FROM settings WHERE branch_id = ? AND `key` = ? LIMIT 1');
    $stmt->execute([$branch_id, $key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : $value;
}

function is_branch_open(PDO $db, int $branch_id): bool
{
    $stmt = $db->prepare('SELECT open_time, close_time, is_closed FROM operating_hours WHERE branch_id = ? AND day_of_week = ? LIMIT 1');
    $stmt->execute([$branch_id, (int) date('w')]);
    $hours = $stmt->fetch();
    if (!$hours || (int) $hours['is_closed'] === 1 || !$hours['open_time'] || !$hours['close_time']) {
        return false;
    }
    $now = date('H:i:s');
    return $now >= $hours['open_time'] && $now <= $hours['close_time'];
}

function branch_options(PDO $db): array
{
    return $db->query('SELECT * FROM branches ORDER BY name')->fetchAll();
}
