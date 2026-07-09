<?php
require_once __DIR__ . '/config.php';

final class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
                try { self::$instance->exec("ALTER TABLE cart_items ADD COLUMN spice_level VARCHAR(20) DEFAULT '0'"); } catch (Exception $e) {}
                try { self::$instance->exec("ALTER TABLE order_details ADD COLUMN spice_level VARCHAR(20) DEFAULT '0'"); } catch (Exception $e) {}
                try { self::$instance->exec("ALTER TABLE order_details ADD COLUMN notes TEXT"); } catch (Exception $e) {}
                try { self::$instance->exec("ALTER TABLE branches ADD COLUMN is_active TINYINT(1) DEFAULT 1"); } catch (Exception $e) {}
                try {
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/French Fries.png' WHERE slug = 'french-fries' AND (image_url IS NULL OR image_url LIKE '%unsplash%')");
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/Es Teh Manis.png' WHERE slug = 'es-teh-manis' AND (image_url IS NULL OR image_url LIKE '%unsplash%')");
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/Lemon Tea.png' WHERE slug = 'lemon-tea' AND (image_url IS NULL OR image_url LIKE '%unsplash%')");
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/Air Mineral.png' WHERE slug = 'air-mineral' AND (image_url IS NULL OR image_url LIKE '%unsplash%')");
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/Katsu.jpeg' WHERE slug = '1-slice-chicken-katsu' AND (image_url IS NULL OR image_url LIKE '%unsplash%')");
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/Egg.png' WHERE slug = 'egg-telur' AND (image_url IS NULL OR image_url LIKE '%unsplash%')");
                } catch (Exception $e) {}
            } catch (PDOException $e) {
                error_log('[DB] ' . $e->getMessage());
                http_response_code(500);
                exit('Database connection failed.');
            }
        }

        return self::$instance;
    }
}

function db(): PDO
{
    return Database::getInstance();
}
