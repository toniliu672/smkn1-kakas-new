<?php
// admin/functions/chart/getAngkatanChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                a.tahun as angkatan,
                SUM(CASE WHEN s.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as laki_laki,
                SUM(CASE WHEN s.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan,
                COUNT(s.id) as total
              FROM angkatan a
              LEFT JOIN siswa s ON a.id = s.id_angkatan
              GROUP BY a.id, a.tahun
              ORDER BY a.tahun DESC";

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