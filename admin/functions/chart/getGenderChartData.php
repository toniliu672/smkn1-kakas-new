<?php
// admin/functions/chart/getGenderChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    // Query untuk mendapatkan data per jenis kelamin dan jurusan
    $query = "SELECT 
                j.nama_jurusan,
                SUM(CASE WHEN s.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as laki_laki,
                SUM(CASE WHEN s.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan,
                COUNT(*) as total
              FROM jurusan j
              LEFT JOIN siswa s ON j.id = s.id_jurusan
              GROUP BY j.id, j.nama_jurusan
              ORDER BY j.nama_jurusan";

    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tambahkan total keseluruhan
    $queryTotal = "SELECT 
                    SUM(CASE WHEN jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as total_laki_laki,
                    SUM(CASE WHEN jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as total_perempuan,
                    COUNT(*) as grand_total
                   FROM siswa";
    
    $stmtTotal = $pdo->query($queryTotal);
    $totals = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    // Tambahkan persentase untuk setiap jenis kelamin per jurusan
    foreach ($data as &$row) {
        $row['persen_laki_laki'] = $row['total'] > 0 ? 
            round(($row['laki_laki'] / $row['total']) * 100, 1) : 0;
        $row['persen_perempuan'] = $row['total'] > 0 ? 
            round(($row['perempuan'] / $row['total']) * 100, 1) : 0;
    }

    // Hitung persentase total
    $totals['persen_laki_laki'] = $totals['grand_total'] > 0 ? 
        round(($totals['total_laki_laki'] / $totals['grand_total']) * 100, 1) : 0;
    $totals['persen_perempuan'] = $totals['grand_total'] > 0 ? 
        round(($totals['total_perempuan'] / $totals['grand_total']) * 100, 1) : 0;

    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'totals' => $totals
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}