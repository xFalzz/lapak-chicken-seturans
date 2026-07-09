<?php
define('APP_NAME', 'Lapak Chicken Seturan');
define('APP_VERSION', '1.0.0');
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    $scriptDir = str_replace('\\', '/', dirname(__DIR__));
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? '');
    
    $subDir = '';
    if (!empty($docRoot) && strpos($scriptDir, $docRoot) === 0) {
        $subDir = substr($scriptDir, strlen($docRoot));
    } else {
        $subDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if (preg_match('#^(.*?)/(?:customer|admin|kasir|api|config)#', $subDir, $matches)) {
            $subDir = $matches[1];
        } else {
            $subDir = rtrim($subDir, '/');
        }
    }
    define('BASE_URL', $protocol . $host . rtrim($subDir, '/'));
}
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
