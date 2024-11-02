<?php 
function deleteJurusan($pdo, $id) {
    try {
        // Cek apakah ada siswa di jurusan ini
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE id_jurusan = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Tidak dapat menghapus jurusan yang masih memiliki siswa!");
        }

        $stmt = $pdo->prepare("DELETE FROM jurusan WHERE id = ?");
        $stmt->execute([$id]);

        return ['status' => 'success', 'message' => 'Jurusan berhasil dihapus'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}