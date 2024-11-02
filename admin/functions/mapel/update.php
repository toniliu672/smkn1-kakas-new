<?php
// admin/functions/mapel/update.php
function updateMapel($pdo, $id, $data)
{
    try {
        if (!empty($data['kode_mapel'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM mata_pelajaran WHERE kode_mapel = ? AND id != ?");
            $stmt->execute([$data['kode_mapel'], $id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Kode mata pelajaran sudah terdaftar!");
            }
        }

        $pdo->beginTransaction();

        // Update data mata pelajaran
        $query = "UPDATE mata_pelajaran 
                 SET nama_mata_pelajaran = ?, 
                     kode_mapel = ?, 
                     kategori = ?
                 WHERE id = ?";

        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            $data['nama_mata_pelajaran'],
            $data['kode_mapel'] ?? null,
            $data['kategori'],
            $id
        ]);

        if (!$result) {
            throw new Exception("Gagal mengupdate data mata pelajaran");
        }

        // Update tingkat
        $stmt = $pdo->prepare("DELETE FROM mata_pelajaran_tingkat WHERE id_mata_pelajaran = ?");
        $stmt->execute([$id]);

        if (!empty($data['tingkat']) && is_array($data['tingkat'])) {
            $stmtTingkat = $pdo->prepare("INSERT INTO mata_pelajaran_tingkat (id_mata_pelajaran, tingkat) VALUES (?, ?)");
            foreach ($data['tingkat'] as $tingkat) {
                if (!$stmtTingkat->execute([$id, $tingkat])) {
                    throw new Exception("Gagal mengupdate data tingkat");
                }
            }
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Data berhasil diupdate'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
