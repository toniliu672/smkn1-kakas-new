<?php
// admin/functions/chart/getJurusanChartData.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    $query = "SELECT 
                j.nama_jurusan,
                COUNT(s.id) as jumlah
              FROM jurusan j 
              LEFT JOIN siswa s ON j.id = s.id_jurusan 
              GROUP BY j.id, j.nama_jurusan
              ORDER BY j.nama_jurusan";
    
    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data ?: [] // Pastikan selalu mengembalikan array, meskipun kosong
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'success', // Tetap kembalikan success
        'data' => [], // Kembalikan array kosong
        'error' => $e->getMessage()
    ]);
}

