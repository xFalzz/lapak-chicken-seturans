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
                try {
                    self::$instance->exec("UPDATE menus SET image_url = 'https://images.unsplash.com/photo-1576107232684-1279f390859f?auto=format&fit=crop&w=600&q=80' WHERE slug = 'french-fries'");
                    self::$instance->exec("UPDATE menus SET image_url = 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?auto=format&fit=crop&w=600&q=80' WHERE slug = 'es-teh-manis'");
                    self::$instance->exec("UPDATE menus SET image_url = 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80' WHERE slug = 'lemon-tea'");
                    self::$instance->exec("UPDATE menus SET image_url = 'https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=600&q=80' WHERE slug = 'air-mineral'");
                    self::$instance->exec("UPDATE menus SET image_url = 'img/Menu/Katsu.jpeg' WHERE slug = '1-slice-chicken-katsu'");
                    self::$instance->exec("UPDATE menus SET image_url = 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80' WHERE slug = 'egg-telur'");
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
