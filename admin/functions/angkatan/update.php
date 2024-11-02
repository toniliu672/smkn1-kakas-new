<?php 
// admin/functions/angkatan/update.php
function updateAngkatan($pdo, $id, $data) {
    try {
        // Validasi tahun jika diubah
        if (!empty($data['tahun'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM angkatan WHERE tahun = ? AND id != ?");
            $stmt->execute([$data['tahun'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Tahun angkatan sudah terdaftar!");
            }
        }

        $query = "UPDATE angkatan SET tahun = ? WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $data['tahun'],
            $id
        ]);

        return ['status' => 'success', 'message' => 'Data angkatan berhasil diperbarui'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}