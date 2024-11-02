<?php
// admin/functions/siswa/read.php

function getSiswa($pdo, $page = 1, $limit = 10, $search = [])
{
    try {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        // Build search conditions
        if (!empty($search['nama'])) {
            $where[] = "s.nama_lengkap LIKE ?";
            $params[] = "%{$search['nama']}%";
        }

        if (!empty($search['nis'])) {
            $where[] = "s.nis = ?";
            $params[] = $search['nis'];
        }

        if (!empty($search['nisn'])) {
            $where[] = "s.nisn = ?";
            $params[] = $search['nisn'];
        }

        if (!empty($search['angkatan'])) {
            $where[] = "s.id_angkatan = ?";
            $params[] = $search['angkatan'];
        }

        if (!empty($search['jurusan'])) {
            $where[] = "s.id_jurusan = ?";
            $params[] = $search['jurusan'];
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Main query dengan LIMIT dan OFFSET di string query
        $query = "SELECT s.*, a.tahun as angkatan, j.nama_jurusan
                 FROM siswa s
                 JOIN angkatan a ON s.id_angkatan = a.id
                 JOIN jurusan j ON s.id_jurusan = j.id
                 {$whereClause}
                 ORDER BY s.created_at DESC
                 LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get total count tanpa LIMIT/OFFSET
        $queryCount = "SELECT COUNT(*) as total 
                      FROM siswa s 
                      JOIN angkatan a ON s.id_angkatan = a.id
                      JOIN jurusan j ON s.id_jurusan = j.id
                      {$whereClause}";

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

function searchSiswaGlobal($pdo, $keyword) {
    try {
        $searchConditions = [];
        $params = [];

        // Cari di nama, NIS, dan NISN
        $searchConditions[] = "s.nama_lengkap LIKE ?";
        $searchConditions[] = "s.nis LIKE ?";
        $searchConditions[] = "s.nisn LIKE ?";
        $params[] = "%{$keyword}%";
        $params[] = "%{$keyword}%";
        $params[] = "%{$keyword}%";

        $whereClause = "WHERE " . implode(" OR ", $searchConditions);

        $query = "SELECT s.*, a.tahun as angkatan, j.nama_jurusan
                 FROM siswa s
                 JOIN angkatan a ON s.id_angkatan = a.id
                 JOIN jurusan j ON s.id_jurusan = j.id
                 {$whereClause}
                 ORDER BY s.nama_lengkap ASC
                 LIMIT 10";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'data' => $result
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
function getDetailSiswa($pdo, $id)
{
    try {
        $query = "SELECT s.*, a.tahun as angkatan, j.nama_jurusan
                 FROM siswa s
                 JOIN angkatan a ON s.id_angkatan = a.id
                 JOIN jurusan j ON s.id_jurusan = j.id
                 WHERE s.id = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$siswa) {
            return ['status' => 'error', 'message' => 'Data siswa tidak ditemukan'];
        }

        return [
            'status' => 'success',
            'data' => $siswa
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}

// Function untuk keperluan print/export
function getAllSiswaForPrint($pdo, $filters = []) {
    try {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['angkatan'])) {
            $where[] = "s.id_angkatan = ?"; // Ubah ke id_angkatan
            $params[] = $filters['angkatan'];
        }

        if (!empty($filters['jurusan'])) {
            $where[] = "s.id_jurusan = ?"; // Ubah ke id_jurusan
            $params[] = $filters['jurusan'];
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $query = "SELECT s.*, 
                        a.tahun as angkatan, 
                        j.nama_jurusan,
                        COALESCE(s.nis, '-') as nis,
                        COALESCE(s.nisn, '-') as nisn,
                        COALESCE(s.no_hp, '-') as no_hp,
                        COALESCE(s.alamat, '-') as alamat
                 FROM siswa s
                 JOIN angkatan a ON s.id_angkatan = a.id
                 JOIN jurusan j ON s.id_jurusan = j.id
                 {$whereClause}
                 ORDER BY s.nama_lengkap ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'message' => 'Data berhasil diambil',
            'data' => $data,
            'filter_info' => [
                'angkatan' => !empty($filters['angkatan']) ? $filters['angkatan'] : null,
                'jurusan' => !empty($filters['jurusan']) ? $filters['jurusan'] : null
            ]
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
