<?php
require_once __DIR__ . '/../includes/functions.php';

$db = db();
$action = $_GET['action'] ?? 'check';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
        json_response(false, null, 'Token CSRF tidak valid', 419);
    }

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = request_data();
        $identity = sanitize($data['identity'] ?? '');
        $password = (string) ($data['password'] ?? '');
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1');
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            json_response(false, null, 'Email/phone atau password salah', 422);
        }

        $oldSession = $_SESSION[SESSION_CART_KEY] ?? session_id();
        login_user($user);

        $guest = $db->prepare('SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL LIMIT 1');
        $guest->execute([$oldSession]);
        $guestCart = $guest->fetchColumn();
        if ($guestCart) {
            $userCart = get_or_create_cart($db);
            $move = $db->prepare('UPDATE cart_items SET cart_id = ? WHERE cart_id = ?');
            $move->execute([$userCart, $guestCart]);
            $db->prepare('DELETE FROM carts WHERE id = ?')->execute([$guestCart]);
        }

        json_response(true, ['role' => $user['role'], 'user' => current_user()], 'Login berhasil');
    }

    if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = request_data();
        $name = sanitize($data['name'] ?? '');
        $email = filter_var($data['email'] ?? null, FILTER_VALIDATE_EMAIL) ?: null;
        $phone = sanitize($data['phone'] ?? '');
        $password = (string) ($data['password'] ?? '');
        if ($name === '' || $phone === '' || strlen($password) < 6) {
            json_response(false, null, 'Nama, phone, dan password minimal 6 karakter wajib diisi', 422);
        }
        $stmt = $db->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, "customer")');
        $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_BCRYPT)]);
        $user = ['id' => $db->lastInsertId(), 'name' => $name, 'email' => $email, 'phone' => $phone, 'role' => 'customer'];
        login_user($user);
        json_response(true, ['user' => current_user()], 'Registrasi berhasil');
    }

    if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        logout_user();
        json_response(true, null, 'Logout berhasil');
    }

    json_response(true, ['user' => current_user()], 'OK');
} catch (PDOException $e) {
    error_log('[API auth] ' . $e->getMessage());
    json_response(false, null, 'Terjadi kesalahan server', 500);
}
