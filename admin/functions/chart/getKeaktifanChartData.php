<?php 
// admin/functions/chart/getKeaktifanChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                status_aktif,
                COUNT(*) as jumlah,
                ROUND((COUNT(*) * 100.0) / (SELECT COUNT(*) FROM guru), 1) as persentase
              FROM guru
              GROUP BY status_aktif
              ORDER BY status_aktif DESC";

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