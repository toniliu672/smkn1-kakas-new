<?php
// admin/functions/chart/getGuruPerJurusanChart.php
require_once '../../../config/koneksi.php';

header('Content-Type: application/json');

try {
    // Query yang benar menggunakan join dengan jurusan untuk mendapatkan nama jurusan
    $query = "SELECT 
                j.nama_jurusan,
                COUNT(DISTINCT gj.id_guru) as jumlah_guru,
                ROUND(COUNT(DISTINCT gj.id_guru) * 100.0 / (
                    SELECT COUNT(DISTINCT id_guru) 
                    FROM guru_jurusan 
                    WHERE is_active = TRUE
                ), 1) as persentase
              FROM jurusan j
              LEFT JOIN guru_jurusan gj ON j.id = gj.id_jurusan AND gj.is_active = TRUE
              GROUP BY j.id, j.nama_jurusan 
              ORDER BY jumlah_guru DESC";

    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
