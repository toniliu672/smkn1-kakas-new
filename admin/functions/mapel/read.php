<?php
// admin/functions/mapel/read.php
function getMapel($pdo, $page = 1, $limit = 10, $search = []) {
    try {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($search['nama'])) {
            $where[] = "mp.nama_mata_pelajaran LIKE ?";
            $params[] = "%{$search['nama']}%";
        }

        if (!empty($search['kode'])) {
            $where[] = "mp.kode_mapel = ?";
            $params[] = $search['kode'];
        }

        if (!empty($search['kategori']) && $search['kategori'] !== 'Semua') {
            $where[] = "mp.kategori = ?";
            $params[] = $search['kategori'];
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $query = "SELECT DISTINCT mp.id, mp.kode_mapel, mp.nama_mata_pelajaran, mp.kategori,
                 GROUP_CONCAT(mpt.tingkat) as tingkat
                 FROM mata_pelajaran mp
                 LEFT JOIN mata_pelajaran_tingkat mpt ON mp.id = mpt.id_mata_pelajaran
                 $whereClause
                 GROUP BY mp.id
                 ORDER BY mp.created_at DESC
                 LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count total
        $queryCount = "SELECT COUNT(DISTINCT mp.id) as total 
                      FROM mata_pelajaran mp 
                      LEFT JOIN mata_pelajaran_tingkat mpt ON mp.id = mpt.id_mata_pelajaran 
                      $whereClause";
        
        $stmtCount = $pdo->prepare($queryCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'status' => 'success',
            'data' => $data,
            'total' => $total
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

function getMapelCount($pdo, $search = []) {
    try {
        $where = [];
        $params = [];
        
        if (!empty($search['nama'])) {
            $where[] = "mp.nama_mata_pelajaran LIKE ?";
            $params[] = "%{$search['nama']}%";
        }
        
        $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);
        
        $query = "SELECT COUNT(DISTINCT mp.id) as total 
                 FROM mata_pelajaran mp 
                 LEFT JOIN mata_pelajaran_tingkat mpt ON mp.id = mpt.id_mata_pelajaran 
                 $whereClause";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        return 0;
    }
}

function getDetailMapel($pdo, $id) {
    try {
        $query = "SELECT mp.*, GROUP_CONCAT(mpt.tingkat) as tingkat_list
                 FROM mata_pelajaran mp
                 LEFT JOIN mata_pelajaran_tingkat mpt ON mp.id = mpt.id_mata_pelajaran
                 WHERE mp.id = ?
                 GROUP BY mp.id";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return [
                'status' => 'success',
                'data' => $result
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Data tidak ditemukan'  
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error', 
            'message' => $e->getMessage()
        ];
    }
 }

function getAllMapel($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nama_mata_pelajaran FROM mata_pelajaran ORDER BY nama_mata_pelajaran");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}