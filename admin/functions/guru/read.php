<?php
// admin/functions/guru/read.php
function getGuru($pdo, $page = 1, $limit = 10, $filters = [])
{
    try {
        $offset = ($page - 1) * $limit;
        $params = [];
        $where = ["1=1"];

        // Handle search term
        if (!empty($filters['search'])) {
            $where[] = "(nama_lengkap LIKE ? OR nip LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        // Handle status filter
        if (!empty($filters['status']) && $filters['status'] !== 'Semua') {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }

        // Handle status_aktif filter
        if (!empty($filters['status_aktif']) && $filters['status_aktif'] !== 'Semua') {
            $where[] = "status_aktif = ?";
            $params[] = $filters['status_aktif'];
        }

        $whereClause = implode(" AND ", $where);

        // Query untuk data
        $query = "SELECT id, nip, nama_lengkap, kontak, status, status_aktif, alasan_keluar 
                 FROM guru 
                 WHERE {$whereClause}
                 ORDER BY created_at DESC 
                 LIMIT $limit OFFSET $offset";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Query untuk total
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
        // Get guru data
        $stmt = $pdo->prepare("SELECT * FROM guru WHERE id = ?");
        $stmt->execute([$id]);
        $guru = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$guru) {
            return [
                'status' => 'error',
                'message' => 'Data guru tidak ditemukan'
            ];
        }

        // Get mata pelajaran yang diampu
        $stmt = $pdo->prepare("
            SELECT mp.* 
            FROM mata_pelajaran mp
            JOIN guru_mata_pelajaran gmp ON mp.id = gmp.id_mata_pelajaran
            WHERE gmp.id_guru = ?
        ");
        $stmt->execute([$id]);
        $mapel = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get jurusan yang diampu
        $stmt = $pdo->prepare("
            SELECT j.* 
            FROM jurusan j
            JOIN guru_jurusan gj ON j.id = gj.id_jurusan
            WHERE gj.id_guru = ?
        ");
        $stmt->execute([$id]);
        $jurusan = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'status' => 'success',
            'data' => [
                'guru' => $guru,
                'mata_pelajaran' => $mapel,
                'jurusan' => $jurusan
            ]
        ];
    } catch (PDOException $e) {
        return [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

// Fungsi alternatif dengan struktur data yang lebih sederhana
function getGuruDetail($pdo, $id)
{
    try {
        // Get guru data
        $stmt = $pdo->prepare("SELECT * FROM guru WHERE id = ?");
        $stmt->execute([$id]);
        $guru = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$guru) {
            return null;
        }

        // Set default values untuk fields yang mungkin NULL
        $guru = array_merge([
            'nip' => '-',
            'nama_lengkap' => '',
            'email' => '-',
            'kontak' => '-',
            'foto' => '',
            'alamat' => '-',
            'tanggal_bergabung' => date('Y-m-d'),
            'tanggal_keluar' => null,
            'alasan_keluar' => '',
            'keterangan_keluar' => '',
            'status' => '',
            'status_aktif' => 'aktif'
        ], $guru);

        // Get mata pelajaran yang diampu
        $stmt = $pdo->prepare("
            SELECT 
                mp.id,
                mp.nama_mata_pelajaran as nama
            FROM mata_pelajaran mp
            JOIN guru_mata_pelajaran gmp ON mp.id = gmp.id_mata_pelajaran
            WHERE gmp.id_guru = ?
        ");
        $stmt->execute([$id]);
        $guru['mata_pelajaran'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all jurusan data (both active and inactive)
        $stmt = $pdo->prepare("
            SELECT 
                j.id,
                j.nama_jurusan as nama,
                gj.is_active,
                gj.tanggal_mulai,
                gj.tanggal_selesai
            FROM jurusan j
            JOIN guru_jurusan gj ON j.id = gj.id_jurusan
            WHERE gj.id_guru = ?
            ORDER BY gj.tanggal_mulai DESC
        ");
        $stmt->execute([$id]);
        $jurusanData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pisahkan jurusan aktif dan tidak aktif
        $guru['jurusan'] = array_filter($jurusanData, function ($j) {
            return $j['is_active'];
        });
        $guru['jurusan_history'] = array_filter($jurusanData, function ($j) {
            return !$j['is_active'];
        });

        // Reset array keys
        $guru['jurusan'] = array_values($guru['jurusan']);
        $guru['jurusan_history'] = array_values($guru['jurusan_history']);

        return $guru;
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

// Fungsi baru khusus untuk halaman detail dengan tracking lengkap
function getGuruDetailWithTracking($pdo, $id)
{
    try {
        // Get basic guru detail first
        $guru = getGuruDetail($pdo, $id);

        if (!$guru) {
            return null;
        }

        // Get ONLY ACTIVE jurusan
        $stmt = $pdo->prepare("
            SELECT 
                j.id,
                j.nama_jurusan as nama,
                gj.tanggal_mulai,
                gj.is_active
            FROM guru_jurusan gj
            JOIN jurusan j ON gj.id_jurusan = j.id
            WHERE gj.id_guru = ? 
            AND gj.is_active = TRUE
            ORDER BY gj.tanggal_mulai DESC
        ");
        $stmt->execute([$id]);
        $guru['jurusan'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get tracking history
        $stmt = $pdo->prepare("
            SELECT 
                j.id,
                j.nama_jurusan as nama,
                gj.tracking_id,
                gj.tanggal_mulai,
                gj.tanggal_selesai,
                gj.change_type,
                gj.change_reason,
                gj.created_at as change_date
            FROM guru_jurusan gj
            JOIN jurusan j ON gj.id_jurusan = j.id
            WHERE gj.id_guru = ?
            ORDER BY gj.created_at DESC
        ");
        $stmt->execute([$id]);
        $allHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group history by tracking_id
        $trackingHistory = [];
        foreach ($allHistory as $record) {
            $trackingId = $record['tracking_id'];
            if (!isset($trackingHistory[$trackingId])) {
                $trackingHistory[$trackingId] = [];
            }
            $trackingHistory[$trackingId][] = $record;
        }

        $guru['jurusan_history'] = $trackingHistory;

        // Get statistics
        $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT tracking_id) as total_perubahan,
            COUNT(DISTINCT id_jurusan) as jurusan_berbeda,
            COUNT(CASE WHEN is_active = TRUE THEN 1 END) as jurusan_aktif,
            MIN(created_at) as awal_mengajar
        FROM guru_jurusan
        WHERE id_guru = ?
    ");
        $stmt->execute([$id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $guru['tracking_stats'] = [
            'total_perubahan' => $stats['total_perubahan'] ?? 0,
            'jurusan_berbeda' => $stats['jurusan_berbeda'] ?? 0,
            'jurusan_aktif' => $stats['jurusan_aktif'] ?? 0,
            'awal_mengajar' => $stats['awal_mengajar']
        ];

        return $guru;
    } catch (PDOException $e) {
        error_log('Database Error: ' . $e->getMessage());
        return null;
    }
}

// Modifikasi fungsi update untuk menangani perubahan jurusan dengan benar
function handleJurusanChanges($pdo, $id_guru, $new_jurusan, $change_reason = '')
{
    try {
        // Get current active jurusan
        $stmt = $pdo->prepare("
            SELECT id_jurusan 
            FROM guru_jurusan 
            WHERE id_guru = ? AND is_active = TRUE
        ");
        $stmt->execute([$id_guru]);
        $current_jurusan = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Terminate removed jurusan
        foreach ($current_jurusan as $jurusan_id) {
            if (!in_array($jurusan_id, $new_jurusan)) {
                // Set existing record to inactive
                $stmt = $pdo->prepare("
                    UPDATE guru_jurusan 
                    SET is_active = FALSE,
                        tanggal_selesai = CURRENT_DATE
                    WHERE id_guru = ? 
                    AND id_jurusan = ? 
                    AND is_active = TRUE
                ");
                $stmt->execute([$id_guru, $jurusan_id]);

                // Insert termination record
                $stmt = $pdo->prepare("CALL insert_guru_jurusan(?, ?, ?, ?)");
                $stmt->execute([
                    $id_guru,
                    $jurusan_id,
                    'TERMINATION',
                    $change_reason ?: 'Guru tidak lagi mengajar di jurusan ini'
                ]);
            }
        }

        // Add new jurusan
        foreach ($new_jurusan as $jurusan_id) {
            if (!in_array($jurusan_id, $current_jurusan)) {
                $stmt = $pdo->prepare("CALL insert_guru_jurusan(?, ?, ?, ?)");
                $stmt->execute([
                    $id_guru,
                    $jurusan_id,
                    'ADDITIONAL',
                    $change_reason ?: 'Penambahan jurusan baru'
                ]);
            }
        }

        return true;
    } catch (Exception $e) {
        error_log('Error in handleJurusanChanges: ' . $e->getMessage());
        throw $e;
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
