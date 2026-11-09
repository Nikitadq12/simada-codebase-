-- Migrasi revisi e-Tendering (untuk database simada yang sudah terlanjur di-import)
-- Jalankan di phpMyAdmin > tab SQL. Data lama tidak hilang.
USE simada;

ALTER TABLE paket_pengadaan
  ADD COLUMN jenis_paket ENUM('Barang','Pekerjaan Konstruksi','Jasa Konsultansi','Jasa Lainnya') NOT NULL DEFAULT 'Barang' AFTER metode;

ALTER TABLE tender
  ADD COLUMN tanggal_target DATE NULL AFTER tanggal_selesai;

-- Data lama: target diisi sama dengan tanggal selesai/mulai (=> Tepat waktu). Ubah manual lewat halaman e-Tendering.
UPDATE tender SET tanggal_target = COALESCE(tanggal_selesai, tanggal_mulai) WHERE tanggal_target IS NULL;

-- Sesuaikan jenis paket data contoh
UPDATE paket_pengadaan SET jenis_paket = 'Pekerjaan Konstruksi' WHERE nama_paket LIKE 'Renovasi%';
