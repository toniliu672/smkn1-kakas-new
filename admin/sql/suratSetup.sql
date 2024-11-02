-- admin/sql/suratSetup.sql

-- Membuat tabel untuk tracking nomor urut
CREATE TABLE nomor_urut_surat (
    tahun_bulan VARCHAR(6) NOT NULL,
    tipe_surat ENUM('SM', 'SK') NOT NULL,
    nomor_terakhir INT NOT NULL DEFAULT 0,
    PRIMARY KEY (tahun_bulan, tipe_surat)
);

-- Membuat tabel surat_masuk dengan constrains dan indexes yang optimal
CREATE TABLE surat_masuk (
    id VARCHAR(20) NOT NULL,
    nomor_surat VARCHAR(100) NOT NULL,
    tanggal_surat DATE NOT NULL,
    tanggal_terima DATE NOT NULL,
    tujuan_surat VARCHAR(255) NOT NULL,
    pengirim VARCHAR(255) NOT NULL,
    perihal TEXT NOT NULL,
    file_surat VARCHAR(255) NOT NULL, -- Path file di uploads/surat
    tipe_file VARCHAR(10) NOT NULL, -- Ekstensi file (jpg/png/pdf)
    ukuran_file INT UNSIGNED NOT NULL, -- Ukuran dalam bytes
    dipindahkan_ke_surat_keluar TINYINT(1) DEFAULT 0,
    status ENUM('aktif', 'arsip') DEFAULT 'aktif',
    status_baca ENUM('belum_dibaca', 'sudah_dibaca') DEFAULT 'belum_dibaca',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX idx_nomor_surat (nomor_surat),
    INDEX idx_tanggal_surat (tanggal_surat),
    INDEX idx_status (status),
    INDEX idx_status_baca (status_baca),
    CONSTRAINT check_tipe_file_sm CHECK (tipe_file IN ('jpg','jpeg','png','pdf')),
    CONSTRAINT check_ukuran_file_sm CHECK (ukuran_file > 0),
    CONSTRAINT check_tanggal_sm CHECK (tanggal_terima >= tanggal_surat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuat tabel surat_keluar dengan constrains dan indexes yang optimal
CREATE TABLE surat_keluar (
    id VARCHAR(20) NOT NULL,
    nomor_surat VARCHAR(100) NOT NULL,
    tanggal_surat DATE NOT NULL,
    tanggal_kirim DATE NOT NULL,
    tujuan_surat VARCHAR(255) NOT NULL,
    kepada VARCHAR(255) NOT NULL,
    perihal TEXT NOT NULL,
    file_surat VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(10) NOT NULL,
    ukuran_file INT UNSIGNED NOT NULL,
    asal_surat_masuk_id VARCHAR(20) DEFAULT NULL,
    status ENUM('draft', 'terkirim', 'arsip') DEFAULT 'draft',
    status_baca ENUM('belum_dibaca', 'sudah_dibaca') DEFAULT 'belum_dibaca',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE INDEX idx_nomor_surat (nomor_surat),
    INDEX idx_tanggal_surat (tanggal_surat),
    INDEX idx_status (status),
    INDEX idx_status_baca (status_baca),
    FOREIGN KEY (asal_surat_masuk_id) 
        REFERENCES surat_masuk(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    CONSTRAINT check_tipe_file_sk CHECK (tipe_file IN ('jpg','jpeg','png','pdf')),
    CONSTRAINT check_ukuran_file_sk CHECK (ukuran_file > 0),
    CONSTRAINT check_tanggal_sk CHECK (tanggal_kirim >= tanggal_surat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuat tabel notifikasi kepala sekolah
CREATE TABLE notifikasi_email (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_tujuan VARCHAR(255) NOT NULL,
    subjek VARCHAR(255) NOT NULL,
    konten TEXT NOT NULL,
    id_surat VARCHAR(20) NOT NULL,
    tipe_surat ENUM('SM', 'SK') NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stored procedure untuk generate ID surat masuk
DELIMITER //
CREATE PROCEDURE generate_id_surat_masuk()
BEGIN
    DECLARE new_id VARCHAR(20);
    DECLARE tahun_bulan VARCHAR(6);
    DECLARE nomor INT;
    
    SET tahun_bulan = DATE_FORMAT(NOW(), '%Y%m');
    
    START TRANSACTION;
    
    INSERT INTO nomor_urut_surat (tahun_bulan, tipe_surat, nomor_terakhir)
    VALUES (tahun_bulan, 'SM', 0)
    ON DUPLICATE KEY UPDATE nomor_terakhir = nomor_terakhir + 1;
    
    SELECT nomor_terakhir INTO nomor
    FROM nomor_urut_surat
    WHERE tahun_bulan = tahun_bulan AND tipe_surat = 'SM';
    
    SET new_id = CONCAT('SM-', tahun_bulan, '-', LPAD(nomor, 4, '0'));
    
    COMMIT;
    
    SELECT new_id AS id;
END //

-- Stored procedure untuk generate ID surat keluar
CREATE PROCEDURE generate_id_surat_keluar()
BEGIN
    DECLARE new_id VARCHAR(20);
    DECLARE tahun_bulan VARCHAR(6);
    DECLARE nomor INT;
    
    SET tahun_bulan = DATE_FORMAT(NOW(), '%Y%m');
    
    START TRANSACTION;
    
    INSERT INTO nomor_urut_surat (tahun_bulan, tipe_surat, nomor_terakhir)
    VALUES (tahun_bulan, 'SK', 0)
    ON DUPLICATE KEY UPDATE nomor_terakhir = nomor_terakhir + 1;
    
    SELECT nomor_terakhir INTO nomor
    FROM nomor_urut_surat
    WHERE tahun_bulan = tahun_bulan AND tipe_surat = 'SK';
    
    SET new_id = CONCAT('SK-', tahun_bulan, '-', LPAD(nomor, 4, '0'));
    
    COMMIT;
    
    SELECT new_id AS id;
END //

-- Trigger untuk validasi tanggal
CREATE TRIGGER before_surat_masuk_insert
BEFORE INSERT ON surat_masuk
FOR EACH ROW
BEGIN
    IF NEW.tanggal_terima < NEW.tanggal_surat THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tanggal terima tidak boleh lebih awal dari tanggal surat';
    END IF;
END //

CREATE TRIGGER before_surat_keluar_insert
BEFORE INSERT ON surat_keluar
FOR EACH ROW
BEGIN
    IF NEW.tanggal_kirim < NEW.tanggal_surat THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Tanggal kirim tidak boleh lebih awal dari tanggal surat';
    END IF;
END //

-- Trigger untuk membuat notifikasi email kepala sekolah
CREATE TRIGGER after_surat_masuk_insert
AFTER INSERT ON surat_masuk
FOR EACH ROW
BEGIN
    INSERT INTO notifikasi_email (
        email_tujuan,
        subjek,
        konten,
        id_surat,
        tipe_surat
    )
    VALUES (
        (SELECT value FROM settings WHERE name = 'email_kepala_sekolah'),
        CONCAT('Surat Masuk Baru: ', NEW.perihal),
        CONCAT(
            'Detail Surat:\n',
            'Nomor: ', NEW.nomor_surat, '\n',
            'Dari: ', NEW.pengirim, '\n',
            'Perihal: ', NEW.perihal, '\n',
            'Tujuan: ', NEW.tujuan_surat
        ),
        NEW.id,
        'SM'
    );
END //

DELIMITER ;

-- Prosedur untuk maintenance tabel nomor_urut_surat (dijalankan oleh cron job)
CREATE EVENT cleanup_nomor_urut_surat
ON SCHEDULE EVERY 1 MONTH
DO
  DELETE FROM nomor_urut_surat 
  WHERE tahun_bulan < DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 6 MONTH), '%Y%m');