<?php 
function updateJurusan($pdo, $id, $data) {
    try {
        // Validasi nama jurusan jika diubah
        if (!empty($data['nama_jurusan'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM jurusan WHERE nama_jurusan = ? AND id != ?");
            $stmt->execute([$data['nama_jurusan'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Nama jurusan sudah terdaftar!");
            }
        }

        $query = "UPDATE jurusan SET nama_jurusan = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $data['nama_jurusan'],
            $id
        ]);

        return ['status' => 'success', 'message' => 'Data jurusan berhasil diperbarui'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}