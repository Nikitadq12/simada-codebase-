<?php
/**
 * Class PaketPengadaan
 * Paket pekerjaan pengadaan yang diproses lewat e-Tendering (FR-02)
 * dan dipantau progresnya (FR-06, FR-08).
 */
class PaketPengadaan extends Model
{
    protected string $table = 'paket_pengadaan';
    public const METODE = ['Tender', 'Pengadaan Langsung', 'E-Purchasing'];

    /** Daftar paket beserta jumlah tahap selesai dan tahap yang sedang aktif. */
    public function allWithProgress(): array
    {
        return $this->db->query(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM tender t WHERE t.paket_id = p.id AND t.status = 'Selesai') AS tahap_selesai,
                    (SELECT t.tahap FROM tender t WHERE t.paket_id = p.id AND t.status = 'Berjalan'
                      ORDER BY t.id LIMIT 1) AS tahap_aktif
             FROM paket_pengadaan p ORDER BY p.tanggal DESC, p.id DESC"
        )->fetchAll();
    }

    public function create(array $d, int $userId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO paket_pengadaan (nama_paket, metode, status, tanggal, created_by) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$d['nama_paket'], $d['metode'], 'Analisis', date('Y-m-d'), $userId]);
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare('UPDATE paket_pengadaan SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    public function creatorName(int $userId): string
    {
        $u = (new User())->findById($userId);
        return $u['nama'] ?? '-';
    }
}
