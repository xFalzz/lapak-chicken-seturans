<?php
define('APP_NAME', 'Lapak Chicken Seturan');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/lapak-chicken-seturan');
define('DB_HOST', 'localhost');
define('DB_NAME', 'lapak_chicken_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DEFAULT_TAX_RATE', 0.10);
define('KDS_REFRESH_SEC', 10);
define('ORDER_PREFIX', 'LCS');
define('SESSION_CART_KEY', 'cart_session_id');

date_default_timezone_set('Asia/Jakarta');

function base_url(string $path = ''): string
{
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, 'data:') === 0) {
        return $path;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
