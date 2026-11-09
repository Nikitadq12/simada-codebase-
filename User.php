<?php
/**
 * Class User  (Modul 2 - Kelola Pengguna & Role, UC-02, FR-01)
 * Operasi data pengguna. Akun tidak dihapus permanen, melainkan
 * dinonaktifkan agar jejak data pengadaan tetap utuh.
 */
class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.nama_role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ?'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function allWithRole(): array
    {
        return $this->db->query(
            'SELECT u.id, u.nama, u.email, u.status, u.role_id, r.nama_role
             FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.nama'
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $exceptId ?? 0]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create(array $d): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (nama, email, password, role_id, status) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$d['nama'], $d['email'], password_hash($d['password'], PASSWORD_DEFAULT),
                        $d['role_id'], 'aktif']);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET nama = ?, email = ?, role_id = ? WHERE id = ?');
        return $stmt->execute([$d['nama'], $d['email'], $d['role_id'], $id]);
    }

    public function setStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }
}
