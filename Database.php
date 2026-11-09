<?php
/**
 * Class Database
 * Singleton pembungkus PDO. Seluruh model mengambil koneksi dari sini
 * sehingga hanya ada satu koneksi ke MySQL per request.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $c = require __DIR__ . '/../config/config.php';
        $dsn = $c['driver'] === 'sqlite'
            ? 'sqlite:' . $c['sqlite']
            : "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        if ($c['driver'] === 'sqlite') {
            $this->pdo->exec('PRAGMA foreign_keys = ON');
        }
    }

    public static function getInstance(): Database
    {
        return self::$instance ??= new Database();
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
