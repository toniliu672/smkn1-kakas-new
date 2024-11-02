<?php
// admin/functions/guru/read.php
// admin/functions/guru/read.php
function getGuru($pdo, $page = 1, $limit = 10, $search = '')
{
    try {
        $offset = ($page - 1) * $limit;
        $params = [];
        $where = ["1=1"];

        // Tambahkan kondisi pencarian jika ada
        if (!empty($search)) {
            $where[] = "(nama_lengkap LIKE ? OR nip LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = implode(" AND ", $where);

        // Query untuk data - PERBAIKAN: Hapus quotes di LIMIT OFFSET
        $query = "SELECT id, nip, nama_lengkap, kontak, status, status_aktif 
                 FROM guru 
                 WHERE {$whereClause}
                 ORDER BY created_at DESC 
                 LIMIT $limit OFFSET $offset";  // <- Perbaikan di sini

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Query untuk total (tidak perlu diubah karena tidak ada LIMIT/OFFSET)
        $countQuery = "SELECT COUNT(*) as total FROM guru WHERE {$whereClause}";
        $stmtCount = $pdo->prepare($countQuery);
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

function searchGuruGlobal($pdo, $keyword)
{
    try {
        $searchConditions = [];
        $params = [];

        // Cari di nama dan NIP
        $searchConditions[] = "nama_lengkap LIKE ?";
        $searchConditions[] = "nip LIKE ?";
        $params[] = "%{$keyword}%";
        $params[] = "%{$keyword}%";

        $whereClause = "WHERE " . implode(" OR ", $searchConditions);

        $query = "SELECT id, nip, nama_lengkap, kontak, status, status_aktif 
                 FROM guru 
                 {$whereClause} 
                 ORDER BY nama_lengkap ASC
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


function getGuruCount($pdo, $search = [])
{
    try {
        $where = [];
        $params = [];

        if (!empty($search['nama'])) {
            $where[] = "nama_lengkap LIKE ?";
            $params[] = "%{$search['nama']}%";
        }

        if (!empty($search['nip'])) {
            $where[] = "nip = ?";
            $params[] = $search['nip'];
        }

        if (!empty($search['status']) && $search['status'] !== 'Semua') {
            $where[] = "status = ?";
            $params[] = $search['status'];
        }

        if (!empty($search['status_aktif']) && $search['status_aktif'] !== 'Semua') {
            $where[] = "status_aktif = ?";
            $params[] = $search['status_aktif'];
        }

        $whereClause = empty($where) ? "" : "WHERE " . implode(" AND ", $where);

        $query = "SELECT COUNT(*) as total FROM guru $whereClause";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        return 0;
    }
}

function getDetailGuru($pdo, $id)
{
    try {
        // Ambil data guru beserta mata pelajaran dan jurusan yang diampu
        $query = "SELECT g.*, 
                 GROUP_CONCAT(DISTINCT mp.id) as mapel_ids,
                 GROUP_CONCAT(DISTINCT mp.nama_mata_pelajaran) as mapel_names,
                 GROUP_CONCAT(DISTINCT j.id) as jurusan_ids,
                 GROUP_CONCAT(DISTINCT j.nama_jurusan) as jurusan_names,
                 GROUP_CONCAT(DISTINCT gj.tanggal_mulai ORDER BY gj.tanggal_mulai DESC) as tanggal_mulai,
                 GROUP_CONCAT(DISTINCT gj.tanggal_selesai ORDER BY gj.tanggal_mulai DESC) as tanggal_selesai,
                 GROUP_CONCAT(DISTINCT gj.is_active ORDER BY gj.tanggal_mulai DESC) as is_active
                 FROM guru g
                 LEFT JOIN guru_mata_pelajaran gmp ON g.id = gmp.id_guru
                 LEFT JOIN mata_pelajaran mp ON gmp.id_mata_pelajaran = mp.id
                 LEFT JOIN guru_jurusan gj ON g.id = gj.id_guru
                 LEFT JOIN jurusan j ON gj.id_jurusan = j.id
                 WHERE g.id = ?
                 GROUP BY g.id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $guru = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($guru) {
            // Format data mata pelajaran (kode yang sudah ada)
            $guru['mata_pelajaran'] = [];
            if ($guru['mapel_ids']) {
                $mapelIds = explode(',', $guru['mapel_ids']);
                $mapelNames = explode(',', $guru['mapel_names']);
                for ($i = 0; $i < count($mapelIds); $i++) {
                    $guru['mata_pelajaran'][] = [
                        'id' => $mapelIds[$i],
                        'nama' => $mapelNames[$i]
                    ];
                }
            }

            // Format data jurusan dengan penanganan error yang lebih baik
            $guru['jurusan'] = [];
            if (!empty($guru['jurusan_ids'])) {
                $jurusanIds = explode(',', $guru['jurusan_ids']);
                $jurusanNames = explode(',', $guru['jurusan_names']);
                $tanggalMulai = explode(',', $guru['tanggal_mulai']);
                $tanggalSelesai = explode(',', $guru['tanggal_selesai']);
                $isActive = explode(',', $guru['is_active']);

                foreach ($jurusanIds as $i => $id) {
                    if (isset($jurusanNames[$i])) {
                        $guru['jurusan'][] = [
                            'id' => $id,
                            'nama' => $jurusanNames[$i],
                            'tanggal_mulai' => $tanggalMulai[$i] ?? null,
                            'tanggal_selesai' => (!empty($tanggalSelesai[$i]) && $tanggalSelesai[$i] !== 'NULL') ? $tanggalSelesai[$i] : null,
                            'is_active' => ($isActive[$i] ?? '0') == '1'
                        ];
                    }
                }
            }

            // Hapus field yang tidak perlu
            unset(
                $guru['mapel_ids'],
                $guru['mapel_names'],
                $guru['jurusan_ids'],
                $guru['jurusan_names'],
                $guru['tanggal_mulai'],
                $guru['tanggal_selesai'],
                $guru['is_active']
            );
        }

        return $guru ?: ['status' => 'error', 'message' => 'Data guru tidak ditemukan'];
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

// Fungsi keperluan print

function getAllGuruForPrint($pdo, $search = [])
{
    try {
        $where = ["1=1"];
        $params = [];

        if (!empty($search['status'])) {
            $where[] = "g.status = ?";
            $params[] = $search['status'];
        }

        if (!empty($search['status_aktif'])) {
            $where[] = "g.status_aktif = ?";
            $params[] = $search['status_aktif'];
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Query dimodifikasi untuk hanya mengambil jurusan yang aktif
        $query = "SELECT g.*, 
                 GROUP_CONCAT(DISTINCT mp.id) as mapel_ids,
                 GROUP_CONCAT(DISTINCT mp.nama_mata_pelajaran) as mapel_names,
                 GROUP_CONCAT(DISTINCT CASE WHEN gj.is_active = TRUE THEN j.id END) as jurusan_ids,
                 GROUP_CONCAT(DISTINCT CASE WHEN gj.is_active = TRUE THEN j.nama_jurusan END) as jurusan_names
                 FROM guru g
                 LEFT JOIN guru_mata_pelajaran gmp ON g.id = gmp.id_guru
                 LEFT JOIN mata_pelajaran mp ON gmp.id_mata_pelajaran = mp.id
                 LEFT JOIN guru_jurusan gj ON g.id = gj.id_guru
                 LEFT JOIN jurusan j ON gj.id_jurusan = j.id
                 $whereClause
                 GROUP BY g.id
                 ORDER BY g.nama_lengkap ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format data
        foreach ($result as &$guru) {
            // Format mata pelajaran
            $guru['mata_pelajaran'] = [];
            if (!empty($guru['mapel_ids']) && !empty($guru['mapel_names'])) {
                $mapelIds = explode(',', $guru['mapel_ids']);
                $mapelNames = explode(',', $guru['mapel_names']);
                for ($i = 0; $i < count($mapelIds); $i++) {
                    if (!empty($mapelIds[$i]) && !empty($mapelNames[$i])) {  // Tambahan pengecekan
                        $guru['mata_pelajaran'][] = [
                            'id' => $mapelIds[$i],
                            'nama' => $mapelNames[$i]
                        ];
                    }
                }
            }

            // Format jurusan (hanya yang aktif)
            $guru['jurusan'] = [];
            if (!empty($guru['jurusan_ids']) && !empty($guru['jurusan_names'])) {
                $jurusanIds = explode(',', $guru['jurusan_ids']);
                $jurusanNames = explode(',', $guru['jurusan_names']);
                for ($i = 0; $i < count($jurusanIds); $i++) {
                    if (!empty($jurusanIds[$i]) && !empty($jurusanNames[$i])) {  // Tambahan pengecekan
                        $guru['jurusan'][] = [
                            'id' => $jurusanIds[$i],
                            'nama' => $jurusanNames[$i]
                        ];
                    }
                }
            }

            // Hapus field yang tidak diperlukan
            unset(
                $guru['mapel_ids'],
                $guru['mapel_names'],
                $guru['jurusan_ids'],
                $guru['jurusan_names']
            );
        }

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
