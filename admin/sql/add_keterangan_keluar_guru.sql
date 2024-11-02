-- admin/sql/guruSetup.sql

ALTER TABLE guru 
MODIFY COLUMN status_aktif ENUM('aktif', 'non-aktif') DEFAULT 'aktif',
ADD COLUMN alasan_keluar ENUM('pensiun', 'pindah_tugas', 'lainnya') DEFAULT NULL AFTER tanggal_keluar,
ADD COLUMN keterangan_keluar TEXT DEFAULT NULL AFTER alasan_keluar,
ADD CONSTRAINT chk_keluar CHECK (
    (tanggal_keluar IS NULL AND alasan_keluar IS NULL AND keterangan_keluar IS NULL AND status_aktif = 'aktif')
    OR 
    (tanggal_keluar IS NOT NULL AND alasan_keluar IS NOT NULL AND status_aktif = 'non-aktif')
),
ADD CONSTRAINT chk_tanggal CHECK (tanggal_keluar IS NULL OR tanggal_keluar > tanggal_bergabung);