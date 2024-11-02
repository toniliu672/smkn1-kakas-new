<?php
// admin/functions/guru/update.php 
function updateGuru($pdo, $id, $data, $foto = null) {
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
            'nip', 'nama_lengkap', 'email', 'kontak', 'alamat', 
            'tanggal_bergabung', 'status', 'status_aktif'
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