<?php
// admin/functions/guru/update.php
function updateGuru($pdo, $id, $data, $foto = null)
{
    try {
        $pdo->beginTransaction();

        // Validasi NIP jika diubah
        if (!empty($data['nip'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM guru WHERE nip = ? AND id != ?");
            $stmt->execute([$data['nip'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("NIP sudah terdaftar!");
            }
        }

        // Handle foto baru
        $fotoPath = null;
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxSize = 10 * 1024 * 1024;

            if (!in_array($foto['type'], $allowedTypes)) {
                throw new Exception("Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.");
            }

            if ($foto['size'] > $maxSize) {
                throw new Exception("Ukuran file terlalu besar. Maksimal 10MB.");
            }

            // Hapus foto lama
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

        // Update data guru
        $setClause = [];
        $params = [];

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
        $stmt = $pdo->prepare("DELETE FROM guru_mata_pelajaran WHERE id_guru = ?");
        $stmt->execute([$id]);

        if (!empty($data['mata_pelajaran'])) {
            $stmtMapel = $pdo->prepare("INSERT INTO guru_mata_pelajaran (id_guru, id_mata_pelajaran) VALUES (?, ?)");
            foreach ($data['mata_pelajaran'] as $mapel) {
                $stmtMapel->execute([$id, $mapel]);
            }
        }

        // Update jurusan dengan sistem tracking baru
        if (!empty($data['jurusan'])) {
            // Get current active jurusan
            $stmt = $pdo->prepare("
                SELECT id_jurusan 
                FROM guru_jurusan 
                WHERE id_guru = ? AND is_active = TRUE
            ");
            $stmt->execute([$id]);
            $currentJurusan = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Handle removals
            foreach ($currentJurusan as $jurusanId) {
                if (!in_array($jurusanId, $data['jurusan'])) {
                    $stmt = $pdo->prepare("CALL insert_guru_jurusan(?, ?, ?, ?)");
                    $stmt->execute([
                        $id,
                        $jurusanId,
                        'TERMINATION',
                        'Guru tidak lagi mengajar di jurusan ini'
                    ]);
                }
            }

            // Handle additions
            foreach ($data['jurusan'] as $jurusanId) {
                if (!in_array($jurusanId, $currentJurusan)) {
                    $stmt = $pdo->prepare("CALL insert_guru_jurusan(?, ?, ?, ?)");
                    $stmt->execute([
                        $id,
                        $jurusanId,
                        'ADDITIONAL',
                        'Penambahan jurusan baru'
                    ]);
                }
            }
        } else {
            // If no jurusan selected, terminate all active ones
            $stmt = $pdo->prepare("
                SELECT id_jurusan 
                FROM guru_jurusan 
                WHERE id_guru = ? AND is_active = TRUE
            ");
            $stmt->execute([$id]);
            $activeJurusan = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($activeJurusan as $jurusanId) {
                $stmt = $pdo->prepare("CALL insert_guru_jurusan(?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    $jurusanId,
                    'TERMINATION',
                    'Guru tidak lagi mengajar di jurusan manapun'
                ]);
            }
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Data guru berhasil diperbarui'];
    } catch (Exception $e) {
        $pdo->rollBack();
        if (isset($fotoPath) && file_exists("../../../" . $fotoPath)) {
            unlink("../../../" . $fotoPath);
        }
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
