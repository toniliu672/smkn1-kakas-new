<?php
// admin/functions/angkatan/read.php

function getAngkatan($pdo, $page = 1, $limit = 10, $search = [])
{
    try {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($search['tahun'])) {
            $where[] = "tahun = ?";
            $params[] = $search['tahun'];
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Perbaikan query dengan LIMIT dan OFFSET langsung di string
        $query = "SELECT * FROM angkatan 
                 $whereClause 
                 ORDER BY tahun DESC 
                 LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total for pagination
        $queryCount = "SELECT COUNT(*) as total FROM angkatan $whereClause";

        $stmtCount = $pdo->prepare($queryCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'status' => 'success',
            'data' => $result,
            'total' => $total
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function getAllAngkatan($pdo)
{
    try {
        $stmt = $pdo->query("SELECT id, tahun FROM angkatan ORDER BY tahun DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function getDetailAngkatan($pdo, $id)
{
    try {
        $query = "SELECT a.*,
                 COUNT(s.id) as total_siswa,
                 GROUP_CONCAT(DISTINCT j.nama_jurusan) as jurusan_list
                 FROM angkatan a
                 LEFT JOIN siswa s ON a.id = s.id_angkatan
                 LEFT JOIN jurusan j ON s.id_jurusan = j.id
                 WHERE a.id = ?
                 GROUP BY a.id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $angkatan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$angkatan) {
            return ['status' => 'error', 'message' => 'Data tidak ditemukan'];
        }

        // Jika ada data jurusan, format untuk tampilan
        if ($angkatan['jurusan_list']) {
            // Ambil distribusi siswa per jurusan
            $queryDist = "SELECT j.nama_jurusan, COUNT(s.id) as jumlah
                         FROM siswa s
                         JOIN jurusan j ON s.id_jurusan = j.id
                         WHERE s.id_angkatan = ?
                         GROUP BY j.id, j.nama_jurusan";

            $stmtDist = $pdo->prepare($queryDist);
            $stmtDist->execute([$id]);
            $angkatan['jurusan'] = $stmtDist->fetchAll(PDO::FETCH_ASSOC);
        }

        return [
            'status' => 'success',
            'data' => $angkatan
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
