-- admin/sql/siswaSetup.sql

-- Membuat tabel angkatan
CREATE TABLE angkatan (
    id VARCHAR(10) PRIMARY KEY,
    tahun YEAR NOT NULL,
    jumlah_siswa INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tahun (tahun)
);

-- Membuat tabel jurusan
CREATE TABLE jurusan (
    id VARCHAR(10) PRIMARY KEY,
    nama_jurusan VARCHAR(100) NOT NULL,
    jumlah_siswa INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_nama_jurusan (nama_jurusan)
);

-- Membuat tabel siswa
CREATE TABLE siswa (
    id VARCHAR(20) PRIMARY KEY,
    id_angkatan VARCHAR(10) NOT NULL,
    id_jurusan VARCHAR(10) NOT NULL,
    nis VARCHAR(20) DEFAULT NULL,
    nisn VARCHAR(20) DEFAULT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    foto_siswa VARCHAR(255) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    nama_ayah VARCHAR(100) DEFAULT NULL,
    nama_ibu VARCHAR(100) DEFAULT NULL,
    pekerjaan_ayah VARCHAR(100) DEFAULT NULL,
    pekerjaan_ibu VARCHAR(100) DEFAULT NULL,
    no_hp_orang_tua VARCHAR(20) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_angkatan) REFERENCES angkatan(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_jurusan) REFERENCES jurusan(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_angkatan (id_angkatan),
    INDEX idx_jurusan (id_jurusan)
);

-- Trigger untuk menambah jumlah siswa di angkatan saat insert siswa baru
DELIMITER //
CREATE TRIGGER after_siswa_insert_angkatan
AFTER INSERT ON siswa
FOR EACH ROW
BEGIN
    UPDATE angkatan 
    SET jumlah_siswa = jumlah_siswa + 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.id_angkatan;
END;//

-- Trigger untuk mengurangi jumlah siswa di angkatan saat delete siswa
CREATE TRIGGER after_siswa_delete_angkatan
AFTER DELETE ON siswa
FOR EACH ROW
BEGIN
    UPDATE angkatan 
    SET jumlah_siswa = jumlah_siswa - 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = OLD.id_angkatan;
END;//

-- Trigger untuk menambah jumlah siswa di jurusan saat insert siswa baru
CREATE TRIGGER after_siswa_insert_jurusan
AFTER INSERT ON siswa
FOR EACH ROW
BEGIN
    UPDATE jurusan 
    SET jumlah_siswa = jumlah_siswa + 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.id_jurusan;
END;//

-- Trigger untuk mengurangi jumlah siswa di jurusan saat delete siswa
CREATE TRIGGER after_siswa_delete_jurusan
AFTER DELETE ON siswa
FOR EACH ROW
BEGIN
    UPDATE jurusan 
    SET jumlah_siswa = jumlah_siswa - 1,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = OLD.id_jurusan;
END;//

-- Trigger untuk mengupdate jumlah siswa saat pindah jurusan
CREATE TRIGGER after_siswa_update_jurusan
AFTER UPDATE ON siswa
FOR EACH ROW
BEGIN
    IF OLD.id_jurusan != NEW.id_jurusan THEN
        UPDATE jurusan 
        SET jumlah_siswa = jumlah_siswa - 1,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = OLD.id_jurusan;
        
        UPDATE jurusan 
        SET jumlah_siswa = jumlah_siswa + 1,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.id_jurusan;
    END IF;
END;//

-- Trigger untuk mengupdate jumlah siswa saat pindah angkatan
CREATE TRIGGER after_siswa_update_angkatan
AFTER UPDATE ON siswa
FOR EACH ROW
BEGIN
    IF OLD.id_angkatan != NEW.id_angkatan THEN
        UPDATE angkatan 
        SET jumlah_siswa = jumlah_siswa - 1,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = OLD.id_angkatan;
        
        UPDATE angkatan 
        SET jumlah_siswa = jumlah_siswa + 1,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.id_angkatan;
    END IF;
END;//

DELIMITER ;

-- Membuat stored procedure untuk validasi format foto
DELIMITER //
CREATE PROCEDURE validate_foto_siswa(IN foto_path VARCHAR(255))
BEGIN
    DECLARE valid BOOLEAN;
    SET valid = FALSE;
    
    -- Validasi ekstensi file
    IF foto_path REGEXP '.*\.(jpg|jpeg|png)$' THEN
        SET valid = TRUE;
    END IF;
    
    IF NOT valid THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Format foto tidak valid. Hanya file JPG, JPEG, dan PNG yang diperbolehkan.';
    END IF;
END//
DELIMITER ;