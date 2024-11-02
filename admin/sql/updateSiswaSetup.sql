-- Menambahkan kolom yang diperlukan ke tabel siswa
ALTER TABLE siswa
ADD COLUMN tempat_lahir VARCHAR(100) DEFAULT NULL AFTER nama_lengkap,
ADD COLUMN tanggal_lahir DATE DEFAULT NULL AFTER tempat_lahir,
ADD COLUMN jenis_kelamin ENUM('Laki-laki', 'Perempuan') DEFAULT NULL AFTER tanggal_lahir,
ADD COLUMN agama VARCHAR(20) DEFAULT NULL AFTER jenis_kelamin;

-- Menambahkan index untuk optimasi pencarian
ALTER TABLE siswa
ADD INDEX idx_nama_lengkap (nama_lengkap),
ADD INDEX idx_tanggal_lahir (tanggal_lahir);

-- Update struktur trigger yang ada jika diperlukan (dalam kasus ini tidak perlu karena tidak mempengaruhi logika trigger yang sudah ada)

-- Optional: Tambahkan constraint untuk agama
ALTER TABLE siswa
MODIFY COLUMN agama ENUM('Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu') DEFAULT NULL;