<?php 
// admin/functions/angkatan/delete.php
function deleteAngkatan($pdo, $id) {
    try {
        // Cek apakah ada siswa di angkatan ini
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE id_angkatan = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Tidak dapat menghapus angkatan yang masih memiliki siswa!");
        }

        $stmt = $pdo->prepare("DELETE FROM angkatan WHERE id = ?");
        $stmt->execute([$id]);

        return ['status' => 'success', 'message' => 'Angkatan berhasil dihapus'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}