<?php
// admin/functions/mapel/create.php
function createMapel($pdo, $data) {
    try {
        // Generate ID
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $id = "MP-{$random}";

        // Validasi kode mapel
        if (!empty($data['kode_mapel'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM mata_pelajaran WHERE kode_mapel = ?");
            $stmt->execute([$data['kode_mapel']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Kode mata pelajaran sudah terdaftar!");
            }
        }

        $pdo->beginTransaction();

        $query = "INSERT INTO mata_pelajaran (id, nama_mata_pelajaran, kode_mapel, kategori) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $id,
            $data['nama_mata_pelajaran'],
            $data['kode_mapel'] ?? null,
            $data['kategori']
        ]);

        // Insert tingkatan
        if (!empty($data['tingkat'])) {
            $stmtTingkat = $pdo->prepare("INSERT INTO mata_pelajaran_tingkat (id_mata_pelajaran, tingkat) VALUES (?, ?)");
            foreach ($data['tingkat'] as $tingkat) {
                $stmtTingkat->execute([$id, $tingkat]);
            }
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Mata pelajaran berhasil ditambahkan'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}