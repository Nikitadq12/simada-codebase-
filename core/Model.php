<?php
/**
 * Abstract class Model
 * Kelas induk semua model. Menyediakan koneksi PDO serta operasi
 * baca umum (findAll, findById) yang diwariskan ke model turunan.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY id")->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
