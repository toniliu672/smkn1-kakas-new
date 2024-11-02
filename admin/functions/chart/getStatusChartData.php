<?php
// admin/functions/chart/getStatusChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                status,
                COUNT(*) as jumlah,
                ROUND((COUNT(*) * 100.0) / (SELECT COUNT(*) FROM guru), 1) as persentase
              FROM guru
              WHERE status IS NOT NULL
              GROUP BY status
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