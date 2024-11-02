-- Drop existing guru_jurusan table if exists (backup dulu jika ada data penting)
DROP TABLE IF EXISTS guru_jurusan;

-- Create guru_jurusan table with proper structure
CREATE TABLE guru_jurusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_guru VARCHAR(10) NOT NULL,
    id_jurusan VARCHAR(10) NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (id_guru) REFERENCES guru(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_jurusan) REFERENCES jurusan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Constraints
    CONSTRAINT uq_guru_jurusan_active UNIQUE (id_guru, id_jurusan, is_active),
    CONSTRAINT chk_tanggal CHECK (tanggal_selesai IS NULL OR tanggal_selesai >= tanggal_mulai),
    CONSTRAINT chk_active_tanggal CHECK (is_active = FALSE OR tanggal_selesai IS NULL)
);

-- Create indexes for better query performance
CREATE INDEX idx_guru_jurusan_active ON guru_jurusan(is_active);
CREATE INDEX idx_guru_jurusan_guru ON guru_jurusan(id_guru);
CREATE INDEX idx_guru_jurusan_tanggal ON guru_jurusan(tanggal_mulai, tanggal_selesai);