<?php
// admin/functions/siswa/delete.php

function deleteSiswa($pdo, $id) {
    try {
        $pdo->beginTransaction();

        // Hapus foto jika ada
        $stmt = $pdo->prepare("SELECT foto_siswa FROM siswa WHERE id = ?");
        $stmt->execute([$id]);
        $foto = $stmt->fetchColumn();
        
        if ($foto && file_exists("../../../" . $foto)) {
            unlink("../../../" . $foto);
        }

        // Hapus data siswa
        $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        return [
            'status' => 'success', 
            'message' => 'Data siswa berhasil dihapus'
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'status' => 'error', 
            'message' => $e->getMessage()
        ];
    }
}