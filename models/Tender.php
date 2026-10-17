<?php
/**
 * Class Tender  (Modul 3 - Kelola e-Tendering, UC-03, FR-02)
 * Menyimpan tahapan tender per paket, memvalidasi isian tahapan,
 * dan menurunkan status paket dari status tahapan-tahapannya.
 */
class Tender extends Model
{
    protected string $table = 'tender';

    /** Enam tahapan baku e-Tendering pascakualifikasi (urutan pelaksanaan). */
    public const TAHAP = [
        'Pengumuman Pascakualifikasi',
        'Download Dokumen Pemilihan',
        'Pemberian Penjelasan',
        'Upload Dokumen Penawaran',
        'Evaluasi Penawaran',
        'Penetapan dan Pengumuman Pemenang',
    ];
    public const STATUS = ['Belum dimulai', 'Berjalan', 'Selesai'];

    public function byPaket(int $paketId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM tender WHERE paket_id = ?');
        $stmt->execute([$paketId]);
        $rows = $stmt->fetchAll();
        usort($rows, fn($a, $b) => array_search($a['tahap'], self::TAHAP) <=> array_search($b['tahap'], self::TAHAP));
        return $rows;
    }

    /**
     * Validasi seluruh baris tahapan yang dikirim form.
     * @return string[] daftar pesan error (kosong = valid)
     */
    public static function validateRows(array $rows): array
    {
        $errors = [];
        $seen = [];
        foreach ($rows as $i => $r) {
            $label = 'Baris ' . ($i + 1) . ($r['tahap'] ? " ({$r['tahap']})" : '');
            if ($r['tahap'] === '') { $errors[] = "$label: Tahap wajib dipilih."; continue; }
            if (!in_array($r['tahap'], self::TAHAP, true)) { $errors[] = "$label: Tahap tidak valid."; continue; }
            if (isset($seen[$r['tahap']])) { $errors[] = "$label: Tahap sudah ada pada paket ini."; continue; }
            $seen[$r['tahap']] = true;
            if (!in_array($r['status'], self::STATUS, true)) { $errors[] = "$label: Status tidak valid."; continue; }

            foreach (['tanggal_mulai' => 'Tanggal mulai', 'tanggal_selesai' => 'Tanggal selesai'] as $f => $n) {
                if ($r[$f] !== '' && !self::isDate($r[$f])) $errors[] = "$label: $n tidak valid.";
            }
            if ($r['status'] !== 'Belum dimulai' && $r['tanggal_mulai'] === '') {
                $errors[] = "$label: Tanggal mulai wajib diisi untuk status {$r['status']}.";
            }
            if ($r['status'] === 'Selesai' && $r['tanggal_selesai'] === '') {
                $errors[] = "$label: Tanggal selesai wajib diisi untuk status Selesai.";
            }
            if ($r['tanggal_mulai'] !== '' && $r['tanggal_selesai'] !== '' && $r['tanggal_selesai'] < $r['tanggal_mulai']) {
                $errors[] = "$label: Tanggal selesai tidak boleh lebih awal dari tanggal mulai.";
            }
        }
        return $errors;
    }

    private static function isDate(string $v): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $v);
        return $d && $d->format('Y-m-d') === $v;
    }

    /** Simpan semua baris dalam satu transaksi (all-or-nothing). */
    public function saveRows(int $paketId, array $rows): void
    {
        $this->db->beginTransaction();
        try {
            $ins = $this->db->prepare('INSERT INTO tender (paket_id, tahap, tanggal_mulai, tanggal_selesai, status) VALUES (?, ?, ?, ?, ?)');
            $upd = $this->db->prepare('UPDATE tender SET tahap = ?, tanggal_mulai = ?, tanggal_selesai = ?, status = ? WHERE id = ? AND paket_id = ?');
            foreach ($rows as $r) {
                $vals = [$r['tahap'], $r['tanggal_mulai'] ?: null, $r['tanggal_selesai'] ?: null, $r['status']];
                $r['id'] ? $upd->execute([...$vals, $r['id'], $paketId]) : $ins->execute([$paketId, ...$vals]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Analisis -> Berjalan -> Selesai (Selesai bila keenam tahap baku selesai). */
    public static function deriveStatusPaket(array $tahapan): string
    {
        $status = array_column($tahapan, 'status');
        $selesai = count(array_keys($status, 'Selesai', true));
        if ($selesai === count(self::TAHAP)) return 'Selesai';
        if ($selesai > 0 || in_array('Berjalan', $status, true)) return 'Berjalan';
        return 'Analisis';
    }
}
