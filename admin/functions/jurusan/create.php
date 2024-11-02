<?php 
// admin/functions/jurusan/create.php
function createJurusan($pdo, $data) {
    try {
        // Generate ID
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $id = "JUR-" . $random;

        // Validasi nama jurusan
        if (!empty($data['nama_jurusan'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jurusan WHERE nama_jurusan = ?");
            $stmt->execute([$data['nama_jurusan']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Nama jurusan sudah terdaftar!");
            }
        }

        $query = "INSERT INTO jurusan (id, nama_jurusan) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $id,
            $data['nama_jurusan']
        ]);

        return ['status' => 'success', 'message' => 'Jurusan berhasil ditambahkan'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}