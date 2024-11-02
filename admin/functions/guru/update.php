<?php
// admin/functions/guru/update.php 
function updateGuru($pdo, $id, $data, $foto = null)
{
    try {
        // Validasi NIP jika diubah
        if (!empty($data['nip'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM guru WHERE nip = ? AND id != ?");
            $stmt->execute([$data['nip'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("NIP sudah terdaftar!");
            }
        }

        $fotoPath = null;

        // Handle upload foto baru jika ada
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxSize = 10 * 1024 * 1024; // 10MB

            if (!in_array($foto['type'], $allowedTypes)) {
                throw new Exception("Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.");
            }

            if ($foto['size'] > $maxSize) {
                throw new Exception("Ukuran file terlalu besar. Maksimal 10MB.");
            }

            // Hapus foto lama jika ada
            $stmt = $pdo->prepare("SELECT foto FROM guru WHERE id = ?");
            $stmt->execute([$id]);
            $oldFoto = $stmt->fetchColumn();
            if ($oldFoto && file_exists("../../../" . $oldFoto)) {
                unlink("../../../" . $oldFoto);
            }

            $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $fotoPath = 'uploads/guru/' . $id . '.' . $extension;

            if (!move_uploaded_file($foto['tmp_name'], "../../../" . $fotoPath)) {
                throw new Exception("Gagal mengupload foto.");
            }
        }

        // Mulai transaksi
        $pdo->beginTransaction();

        // Update data guru
        $setClause = [];
        $params = [];

        // Daftar field yang bisa diupdate
        $allowedFields = [
            'nip',
            'nama_lengkap',
            'email',
            'kontak',
            'alamat',
            'tanggal_bergabung',
            'tanggal_keluar',
            'alasan_keluar',
            'keterangan_keluar',
            'status',
            'status_aktif'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $setClause[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if ($fotoPath) {
            $setClause[] = "foto = ?";
            $params[] = $fotoPath;
        }

        $params[] = $id; // untuk WHERE clause

        $query = "UPDATE guru SET " . implode(", ", $setClause) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // Update mata pelajaran
        if (isset($data['mata_pelajaran'])) {
            // Hapus relasi yang ada
            $stmt = $pdo->prepare("DELETE FROM guru_mata_pelajaran WHERE id_guru = ?");
            $stmt->execute([$id]);

            // Insert relasi baru
            if (!empty($data['mata_pelajaran'])) {
                $stmtMapel = $pdo->prepare("INSERT INTO guru_mata_pelajaran (id_guru, id_mata_pelajaran) VALUES (?, ?)");
                foreach ($data['mata_pelajaran'] as $mapel) {
                    $stmtMapel->execute([$id, $mapel]);
                }
            }
        }
        // Update jurusan
        if (isset($data['jurusan'])) {
            // Dapatkan jurusan yang aktif saat ini
            $stmt = $pdo->prepare("
            SELECT id_jurusan 
            FROM guru_jurusan 
            WHERE id_guru = ? AND is_active = TRUE
        ");
            $stmt->execute([$id]);
            $currentJurusan = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Jurusan yang akan dihapus (ada di current tapi tidak ada di data baru)
            $jurusanToDeactivate = array_diff($currentJurusan, $data['jurusan']);

            // Non-aktifkan jurusan yang tidak dipilih lagi
            if (!empty($jurusanToDeactivate)) {
                $stmt = $pdo->prepare("
                UPDATE guru_jurusan 
                SET is_active = FALSE, 
                    tanggal_selesai = CURRENT_DATE 
                WHERE id_guru = ? 
                AND id_jurusan IN (" . str_repeat('?,', count($jurusanToDeactivate) - 1) . "?)
                AND is_active = TRUE
            ");
                $stmt->execute(array_merge([$id], $jurusanToDeactivate));
            }

            // Tambahkan jurusan baru (yang ada di data baru tapi tidak ada di current)
            $jurusanToAdd = array_diff($data['jurusan'], $currentJurusan);

            if (!empty($jurusanToAdd)) {
                $stmt = $pdo->prepare("
                INSERT INTO guru_jurusan (id_guru, id_jurusan, tanggal_mulai, is_active) 
                VALUES (?, ?, CURRENT_DATE, TRUE)
            ");
                foreach ($jurusanToAdd as $id_jurusan) {
                    try {
                        $stmt->execute([$id, $id_jurusan]);
                    } catch (PDOException $e) {
                        // Handle jika jurusan sudah ada dan aktif
                        if ($e->getCode() == 23000) { // Duplicate entry error
                            continue;
                        }
                        throw $e;
                    }
                }
            }
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Data guru berhasil diperbarui'];
    } catch (Exception $e) {
        $pdo->rollBack();
        // Hapus foto baru jika ada error
        if (isset($fotoPath) && file_exists("../../../" . $fotoPath)) {
            unlink("../../../" . $fotoPath);
        }
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
