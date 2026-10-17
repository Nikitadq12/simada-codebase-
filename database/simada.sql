-- =========================================================
-- SIMADA - Sistem Informasi Manajemen Pengadaan
-- Bagian Pengadaan Barang/Jasa Setda Kabupaten Kebumen
-- RDBMS : MySQL 8 / MariaDB 10 (XAMPP)
-- =========================================================
DROP DATABASE IF EXISTS simada;
CREATE DATABASE simada CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simada;

CREATE TABLE roles (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama     VARCHAR(100) NOT NULL,
  email    VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id  INT UNSIGNED NOT NULL,
  status   ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE paket_pengadaan (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_paket VARCHAR(150) NOT NULL,
  metode     ENUM('Tender','Pengadaan Langsung','E-Purchasing') NOT NULL,
  status     ENUM('Analisis','Berjalan','Selesai') NOT NULL DEFAULT 'Analisis',
  tanggal    DATE NOT NULL,
  created_by INT UNSIGNED NULL,
  CONSTRAINT fk_paket_user FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE tender (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  paket_id        INT UNSIGNED NOT NULL,
  tahap           VARCHAR(60) NOT NULL,
  tanggal_mulai   DATE NULL,
  tanggal_selesai DATE NULL,
  status          ENUM('Belum dimulai','Berjalan','Selesai') NOT NULL DEFAULT 'Belum dimulai',
  UNIQUE KEY uq_paket_tahap (paket_id, tahap),
  CONSTRAINT fk_tender_paket FOREIGN KEY (paket_id) REFERENCES paket_pengadaan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO roles (nama_role) VALUES ('Admin'),('PA/KPA'),('PPK'),('Pokja'),('Pejabat Pengadaan');

-- Password seluruh akun awal: Simada@123 (disimpan sebagai hash bcrypt)
INSERT INTO users (nama,email,password,role_id,status) VALUES
('Administrator','admin@kebumen.go.id','$2y$10$246ef1X0vqGWBJ4C79F5e.zvV7H.fkQL7u461VnirYr0sbE4HWJja',1,'aktif'),
('Budi Santoso','budi@kebumen.go.id','$2y$10$246ef1X0vqGWBJ4C79F5e.zvV7H.fkQL7u461VnirYr0sbE4HWJja',4,'aktif'),
('Siti Aminah','siti@kebumen.go.id','$2y$10$246ef1X0vqGWBJ4C79F5e.zvV7H.fkQL7u461VnirYr0sbE4HWJja',3,'aktif'),
('Agus Pratama','agus@kebumen.go.id','$2y$10$246ef1X0vqGWBJ4C79F5e.zvV7H.fkQL7u461VnirYr0sbE4HWJja',2,'aktif'),
('Dewi Lestari','dewi@kebumen.go.id','$2y$10$246ef1X0vqGWBJ4C79F5e.zvV7H.fkQL7u461VnirYr0sbE4HWJja',5,'nonaktif');

INSERT INTO paket_pengadaan (nama_paket,metode,status,tanggal,created_by) VALUES
('Pengadaan ATK Kantor','Tender','Berjalan','2026-09-14',2),
('Renovasi Gedung B','Tender','Selesai','2026-08-03',2),
('Pengadaan Laptop Dinas','Tender','Berjalan','2026-10-01',2);

INSERT INTO tender (paket_id,tahap,tanggal_mulai,tanggal_selesai,status) VALUES
(1,'Pengumuman Pascakualifikasi','2026-09-15','2026-09-17','Selesai'),
(1,'Download Dokumen Pemilihan','2026-09-18','2026-09-22','Selesai'),
(1,'Pemberian Penjelasan','2026-09-23','2026-09-23','Selesai'),
(1,'Upload Dokumen Penawaran','2026-09-24','2026-09-30','Selesai'),
(1,'Evaluasi Penawaran','2026-10-01',NULL,'Berjalan'),
(2,'Pengumuman Pascakualifikasi','2026-08-04','2026-08-06','Selesai'),
(2,'Download Dokumen Pemilihan','2026-08-07','2026-08-11','Selesai'),
(2,'Pemberian Penjelasan','2026-08-12','2026-08-12','Selesai'),
(2,'Upload Dokumen Penawaran','2026-08-13','2026-08-19','Selesai'),
(2,'Evaluasi Penawaran','2026-08-20','2026-08-26','Selesai'),
(2,'Penetapan dan Pengumuman Pemenang','2026-08-27','2026-08-28','Selesai'),
(3,'Pengumuman Pascakualifikasi','2026-10-01','2026-10-03','Selesai'),
(3,'Download Dokumen Pemilihan','2026-10-04','2026-10-10','Selesai'),
(3,'Pemberian Penjelasan','2026-10-12',NULL,'Berjalan');
