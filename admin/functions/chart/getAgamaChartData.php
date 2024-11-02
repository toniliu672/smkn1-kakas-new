<?php
// admin/functions/chart/getAgamaChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                agama,
                COUNT(*) as jumlah,
                ROUND((COUNT(*) * 100.0) / (SELECT COUNT(*) FROM siswa), 1) as persentase
              FROM siswa
              WHERE agama IS NOT NULL
              GROUP BY agama
              ORDER BY jumlah DESC";

    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data ?: []
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'success',
        'data' => [],
        'error' => $e->getMessage()
    ]);
}