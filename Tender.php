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

    /** Selisih hari bertanda (b - a): negatif = b lebih awal dari a. */
    private static function selisihHari(string $a, string $b): int
    {
        return (int)(new DateTime($a))->diff(new DateTime($b))->format('%r%a');
    }

    private static function ket(int $d): array
    {
        if ($d < 0)  return ['kode' => 'cepat', 'hari' => -$d, 'label' => 'Lebih cepat ' . (-$d) . ' hari'];
        if ($d === 0) return ['kode' => 'tepat', 'hari' => 0,  'label' => 'Tepat waktu'];
        return ['kode' => 'delay', 'hari' => $d, 'label' => "Delay $d hari"];
    }

    /**
     * Ketepatan waktu satu tahap (FR-02 revisi): bandingkan tanggal selesai
     * (atau hari ini bila belum selesai) dengan tanggal target.
     * @return array|null ['kode'=>cepat|tepat|delay,'hari'=>int,'label'=>string]; null bila belum dapat dinilai
     */
    public static function ketepatanTahap(array $r, ?string $today = null): ?array
    {
        $target = $r['tanggal_target'] ?? '';
        if ($target === '' || !self::isDate($target)) return null;
        $today ??= date('Y-m-d');
        if (($r['status'] ?? '') === 'Selesai' && !empty($r['tanggal_selesai'])) {
            return self::ket(self::selisihHari($target, $r['tanggal_selesai']));
        }
        return $today > $target ? self::ket(self::selisihHari($target, $today)) : null;
    }

    /**
     * Ketepatan waktu paket. Paket selesai: dinilai dari tahap terakhir
     * (Penetapan dan Pengumuman Pemenang). Paket berjalan: delay terbesar
     * di antara tahapnya; bila tidak ada yang delay -> Tepat waktu.
     */
    public static function ketepatanPaket(array $tahapan, ?string $today = null): ?array
    {
        if (!$tahapan) return null;
        if (self::deriveStatusPaket($tahapan) === 'Selesai') {
            $akhir = array_values(array_filter($tahapan, fn($t) => $t['tahap'] === self::TAHAP[count(self::TAHAP) - 1]));
            return ($akhir ? self::ketepatanTahap($akhir[0], $today) : null) ?? self::ket(0);
        }
        $max = 0;
        foreach ($tahapan as $t) {
            $k = self::ketepatanTahap($t, $today);
            if ($k && $k['kode'] === 'delay') $max = max($max, $k['hari']);
        }
        return $max > 0 ? self::ket($max) : self::ket(0);
    }

    /** Posisi paket pada 6 tahap baku: no 1..6 (0 = belum dimulai). */
    public static function posisi(array $tahapan): array
    {
        if (!$tahapan) return ['no' => 0, 'nama' => 'Belum dimulai', 'selesai' => false];
        $by = array_column($tahapan, 'status', 'tahap');
        foreach (self::TAHAP as $i => $t) {
            if (($by[$t] ?? 'Belum dimulai') !== 'Selesai') return ['no' => $i + 1, 'nama' => $t, 'selesai' => false];
        }
        return ['no' => count(self::TAHAP), 'nama' => self::TAHAP[count(self::TAHAP) - 1], 'selesai' => true];
    }

    /** Semua tahapan dikelompokkan per paket_id (satu query). */
    public function allGrouped(): array
    {
        $g = [];
        foreach ($this->db->query('SELECT * FROM tender')->fetchAll() as $r) $g[$r['paket_id']][] = $r;
        return $g;
    }

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

            foreach (['tanggal_mulai' => 'Tanggal mulai', 'tanggal_selesai' => 'Tanggal selesai', 'tanggal_target' => 'Tanggal target'] as $f => $n) {
                if ($r[$f] !== '' && !self::isDate($r[$f])) $errors[] = "$label: $n tidak valid.";
            }
            if ($r['tanggal_target'] === '') $errors[] = "$label: Tanggal target wajib diisi.";
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

    public static function isDate(string $v): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $v);
        return $d && $d->format('Y-m-d') === $v;
    }

    /** Simpan semua baris dalam satu transaksi (all-or-nothing). */
    public function saveRows(int $paketId, array $rows): void
    {
        $this->db->beginTransaction();
        try {
            $ins = $this->db->prepare('INSERT INTO tender (paket_id, tahap, tanggal_mulai, tanggal_selesai, tanggal_target, status) VALUES (?, ?, ?, ?, ?, ?)');
            $upd = $this->db->prepare('UPDATE tender SET tahap = ?, tanggal_mulai = ?, tanggal_selesai = ?, tanggal_target = ?, status = ? WHERE id = ? AND paket_id = ?');
            foreach ($rows as $r) {
                $vals = [$r['tahap'], $r['tanggal_mulai'] ?: null, $r['tanggal_selesai'] ?: null, $r['tanggal_target'] ?: null, $r['status']];
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
