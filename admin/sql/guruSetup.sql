-- admin/sql/guruSetup.sql

-- Membuat tabel guru
CREATE TABLE guru (
    id VARCHAR(10) PRIMARY KEY,
    nip VARCHAR(20) DEFAULT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) DEFAULT NULL,
    kontak VARCHAR(20) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    tanggal_bergabung DATETIME NOT NULL,
    tanggal_keluar DATETIME DEFAULT NULL,
    status ENUM('PNS', 'Honorer', 'Kontrak') DEFAULT NULL,
    status_aktif ENUM('aktif', 'non-aktif') DEFAULT 'aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat tabel mata pelajaran
CREATE TABLE mata_pelajaran (
    id VARCHAR(10) PRIMARY KEY,
    nama_mata_pelajaran VARCHAR(100) NOT NULL,
    kode_mapel VARCHAR(20) DEFAULT NULL,
    kategori ENUM('Umum', 'Kejuruan', 'Muatan Lokal') DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Membuat tabel relasi guru dan mata pelajaran
CREATE TABLE guru_mata_pelajaran (
    id_guru VARCHAR(10),
    id_mata_pelajaran VARCHAR(10),
    PRIMARY KEY (id_guru, id_mata_pelajaran),
    FOREIGN KEY (id_guru) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (id_mata_pelajaran) REFERENCES mata_pelajaran(id) ON DELETE CASCADE
);

-- Membuat tabel relasi mata pelajaran dan tingkat
CREATE TABLE mata_pelajaran_tingkat (
    id_mata_pelajaran VARCHAR(10),
    tingkat ENUM('X', 'XI', 'XII') NOT NULL,
    PRIMARY KEY (id_mata_pelajaran, tingkat),
    FOREIGN KEY (id_mata_pelajaran) REFERENCES mata_pelajaran(id) ON DELETE CASCADE
);

-- Membuat trigger untuk mengupdate updated_at pada tabel guru
DELIMITER //
CREATE TRIGGER before_guru_update 
    BEFORE UPDATE ON guru
    FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END;//
DELIMITER ;

-- Membuat trigger untuk mengupdate updated_at pada tabel mata_pelajaran
DELIMITER //
CREATE TRIGGER before_mata_pelajaran_update 
    BEFORE UPDATE ON mata_pelajaran
    FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END;//
DELIMITER ;

-- Membuat index untuk optimasi query
CREATE INDEX idx_guru_status_aktif ON guru(status_aktif);
CREATE INDEX idx_mata_pelajaran_kategori ON mata_pelajaran(kategori);