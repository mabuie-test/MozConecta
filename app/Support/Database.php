<?php
declare(strict_types=1);

namespace App\Support;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(Config $config): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config->get('database.driver', 'mysql'),
            $config->get('database.host', '127.0.0.1'),
            $config->get('database.port', '3306'),
            $config->get('database.database', '')
        );
        self::$pdo = new PDO($dsn, $config->get('database.username', ''), $config->get('database.password', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return self::$pdo;
    }
}
