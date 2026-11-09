<?php
declare(strict_types=1);

final class Database
{
    public static function connect(string $path): PDO
    {
        if (!is_file($path)) {
            throw new RuntimeException('Database not found. Run ./install.sh first.');
        }
        $db = new PDO('sqlite:' . $path);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA foreign_keys = ON');
        return $db;
    }
}
