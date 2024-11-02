<?php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                mp.nama_mata_pelajaran,
                COUNT(DISTINCT gmp.id_guru) as jumlah_guru,
                ROUND((COUNT(DISTINCT gmp.id_guru) * 100.0) / 
                    (SELECT COUNT(DISTINCT id_guru) FROM guru_mata_pelajaran), 1) as persentase
              FROM mata_pelajaran mp
              LEFT JOIN guru_mata_pelajaran gmp ON mp.id = gmp.id_mata_pelajaran
              GROUP BY mp.id, mp.nama_mata_pelajaran
              HAVING jumlah_guru > 0
              ORDER BY jumlah_guru DESC";

    $stmt = $pdo->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $result ?: []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'success',
        'data' => [],
        'error' => $e->getMessage()
    ]);
}

