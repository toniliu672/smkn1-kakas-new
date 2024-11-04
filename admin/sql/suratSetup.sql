-- Hapus tabel jika sudah ada (dalam urutan yang benar karena foreign key constraints)
DROP TABLE IF EXISTS surat_keluar;
DROP TABLE IF EXISTS surat_masuk;
DROP TABLE IF EXISTS surat_disposisi;

-- Hapus stored procedure jika sudah ada
DROP PROCEDURE IF EXISTS generate_surat_id;

-- Buat ulang tabel-tabel
CREATE TABLE surat_disposisi (
    id VARCHAR(20) NOT NULL,
    nomor_surat VARCHAR(100) NOT NULL,
    tanggal_surat DATE NOT NULL,
    tanggal_diterima DATETIME NOT NULL,
    tujuan_surat TEXT NOT NULL,
    file_surat VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    keterangan TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_tanggal_surat (tanggal_surat),
    INDEX idx_tanggal_diterima (tanggal_diterima)
);

CREATE TABLE surat_masuk (
    id VARCHAR(20) NOT NULL,
    id_surat_disposisi VARCHAR(20) NOT NULL,
    tanggal_persetujuan DATETIME NOT NULL,
    disetujui_oleh INT NOT NULL,
    status ENUM('active', 'exported') DEFAULT 'active',
    keterangan_persetujuan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (id_surat_disposisi) REFERENCES surat_disposisi(id),
    FOREIGN KEY (disetujui_oleh) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_tanggal_persetujuan (tanggal_persetujuan)
);

CREATE TABLE surat_keluar (
    id VARCHAR(20) NOT NULL,
    id_surat_masuk VARCHAR(20) NOT NULL,
    tanggal_keluar DATETIME NOT NULL,
    nomor_surat_keluar VARCHAR(100) NOT NULL,
    file_surat_keluar VARCHAR(255) NOT NULL,
    dikeluarkan_oleh INT NOT NULL,
    status ENUM('active', 'cancelled') DEFAULT 'active',
    keterangan_keluar TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (id_surat_masuk) REFERENCES surat_masuk(id),
    FOREIGN KEY (dikeluarkan_oleh) REFERENCES users(id),
    INDEX idx_status (status),
    INDEX idx_tanggal_keluar (tanggal_keluar)
);

-- Buat stored procedure yang sudah diperbaiki
DELIMITER //

CREATE PROCEDURE generate_surat_id(
    IN prefix VARCHAR(3),
    OUT generated_id VARCHAR(20)
)
BEGIN
    DECLARE curr_date VARCHAR(8);
    DECLARE rand_num VARCHAR(6);
    
    -- Format: PREFIX-YYYYMMDD-RANDOM
    SET curr_date = DATE_FORMAT(NOW(), '%Y%m%d');
    SET rand_num = LPAD(FLOOR(RAND() * 1000000), 6, '0');
    SET generated_id = CONCAT(prefix, '-', curr_date, '-', rand_num);
END //

DELIMITER ;

-- Buat triggers
DELIMITER //

CREATE TRIGGER after_surat_keluar_insert
AFTER INSERT ON surat_keluar
FOR EACH ROW
BEGIN
    UPDATE surat_masuk 
    SET status = 'exported',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.id_surat_masuk;
END //

CREATE TRIGGER after_surat_keluar_cancel
AFTER UPDATE ON surat_keluar
FOR EACH ROW
BEGIN
    IF NEW.status = 'cancelled' AND OLD.status = 'active' THEN
        UPDATE surat_masuk 
        SET status = 'active',
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.id_surat_masuk;
    END IF;
END //

DELIMITER ;