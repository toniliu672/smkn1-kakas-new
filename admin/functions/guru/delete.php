<?php
// admin/functions/guru/delete.php
function deleteGuru($pdo, $id) {
    try {
        // Mulai transaksi
        $pdo->beginTransaction();

        // Hapus foto jika ada
        $stmt = $pdo->prepare("SELECT foto FROM guru WHERE id = ?");
        $stmt->execute([$id]);
        $foto = $stmt->fetchColumn();
        
        if ($foto && file_exists("../../../" . $foto)) {
            unlink("../../../" . $foto);
        }

        // Hapus relasi dengan mata pelajaran
        $stmt = $pdo->prepare("DELETE FROM guru_mata_pelajaran WHERE id_guru = ?");
        $stmt->execute([$id]);

        // Hapus data guru
        $stmt = $pdo->prepare("DELETE FROM guru WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Data guru berhasil dihapus'];

    } catch (Exception $e) {
        $pdo->rollBack();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}