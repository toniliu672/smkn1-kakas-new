<?php
// admin/functions/jurusan/read.php

function getJurusan($pdo, $page = 1, $limit = 10, $search = [])
{
    try {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($search['nama'])) {
            $where[] = "j.nama_jurusan LIKE ?";
            $params[] = "%{$search['nama']}%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Join dengan view v_jumlah_guru_per_jurusan untuk mendapatkan jumlah guru
        $query = "SELECT j.*, v.jumlah_guru_aktif 
                 FROM jurusan j
                 LEFT JOIN v_jumlah_guru_per_jurusan v ON j.id = v.id_jurusan
                 $whereClause
                 ORDER BY j.nama_jurusan 
                 LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total for pagination
        $queryCount = "SELECT COUNT(*) as total FROM jurusan j $whereClause";
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

function getGuruJurusan($pdo, $id_jurusan = null)
{
    try {
        $query = "SELECT 
                    j.id as id_jurusan,
                    j.nama_jurusan,
                    v.jumlah_guru_aktif,
                    GROUP_CONCAT(
                        DISTINCT 
                        CASE 
                            WHEN gj.is_active = 1 
                            THEN CONCAT(g.nama_lengkap, ' (', g.status, ')')
                        END
                        ORDER BY g.nama_lengkap ASC
                    ) as daftar_guru
                FROM jurusan j
                LEFT JOIN v_jumlah_guru_per_jurusan v ON j.id = v.id_jurusan
                LEFT JOIN guru_jurusan gj ON j.id = gj.id_jurusan
                LEFT JOIN guru g ON gj.id_guru = g.id AND g.status_aktif = 'aktif'";

        if ($id_jurusan) {
            $query .= " WHERE j.id = ?
                       GROUP BY j.id, j.nama_jurusan, v.jumlah_guru_aktif";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id_jurusan]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $query .= " GROUP BY j.id, j.nama_jurusan, v.jumlah_guru_aktif
                       ORDER BY j.nama_jurusan";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
