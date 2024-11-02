<?php
// admin/functions/jurusan/read.php

function getJurusan($pdo, $page = 1, $limit = 10, $search = [])
{
    try {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($search['nama'])) {
            $where[] = "nama_jurusan LIKE ?";
            $params[] = "%{$search['nama']}%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Perbaikan query dengan LIMIT dan OFFSET langsung di string
        $query = "SELECT * FROM jurusan 
                 $whereClause 
                 ORDER BY nama_jurusan 
                 LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total for pagination
        $queryCount = "SELECT COUNT(*) as total FROM jurusan $whereClause";

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

function getAllJurusan($pdo)
{
    try {
        $stmt = $pdo->query("SELECT id, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function getDetailJurusan($pdo, $id)
{
    try {
        $query = "SELECT j.*,
                 COUNT(s.id) as total_siswa,
                 GROUP_CONCAT(DISTINCT a.tahun) as tahun_angkatan
                 FROM jurusan j
                 LEFT JOIN siswa s ON j.id = s.id_jurusan
                 LEFT JOIN angkatan a ON s.id_angkatan = a.id
                 WHERE j.id = ?
                 GROUP BY j.id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $jurusan = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$jurusan) {
            return ['status' => 'error', 'message' => 'Data tidak ditemukan'];
        }

        return [
            'status' => 'success',
            'data' => $jurusan
        ];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
