<?php
require_once __DIR__ . '/../includes/functions.php';

$db = db();
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'sauces') {
        $rows = $db->query('SELECT * FROM sauces WHERE is_active = 1 ORDER BY price_extra, name')->fetchAll();
        json_response(true, $rows);
    }

    $params = [];
    $where = ['m.is_active = 1', 'c.is_active = 1'];
    if (!empty($_GET['category_id'])) {
        $where[] = 'm.category_id = ?';
        $params[] = (int) $_GET['category_id'];
    }
    $stmt = $db->prepare(
        'SELECT m.*, c.name category_name, c.icon category_icon
         FROM menus m
         JOIN categories c ON c.id = m.category_id
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY c.name, m.name'
    );
    $stmt->execute($params);
    json_response(true, $stmt->fetchAll());
} catch (PDOException $e) {
    error_log('[API menu] ' . $e->getMessage());
    json_response(false, null, 'Terjadi kesalahan server', 500);
}
