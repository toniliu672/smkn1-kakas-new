<?php 
// admin/functions/angkatan/create.php
function createAngkatan($pdo, $data) {
    try {
        // Generate ID
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $id = "ANG-" . $random;

        // Validasi tahun
        if (!empty($data['tahun'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM angkatan WHERE tahun = ?");
            $stmt->execute([$data['tahun']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Tahun angkatan sudah terdaftar!");
            }
        }

        $query = "INSERT INTO angkatan (id, tahun) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $id,
            $data['tahun']
        ]);

        return ['status' => 'success', 'message' => 'Angkatan berhasil ditambahkan'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}