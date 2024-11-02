<?php
// admin/functions/chart/getGenderPerAngkatanChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                a.tahun as angkatan,
                SUM(CASE WHEN s.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as laki_laki,
                SUM(CASE WHEN s.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan,
                COUNT(*) as total
              FROM angkatan a
              LEFT JOIN siswa s ON a.id = s.id_angkatan
              GROUP BY a.id, a.tahun
              ORDER BY a.tahun DESC";

    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tambahkan persentase untuk setiap angkatan
    foreach ($data as &$row) {
        $row['persen_laki_laki'] = $row['total'] > 0 ?
            round(($row['laki_laki'] / $row['total']) * 100, 1) : 0;
        $row['persen_perempuan'] = $row['total'] > 0 ?
            round(($row['perempuan'] / $row['total']) * 100, 1) : 0;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
