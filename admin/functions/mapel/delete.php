<?php
// admin/functions/mapel/delete.php
function deleteMapel($pdo, $id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM mata_pelajaran_tingkat WHERE id_mata_pelajaran = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM guru_mata_pelajaran WHERE id_mata_pelajaran = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Mata pelajaran berhasil dihapus'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}