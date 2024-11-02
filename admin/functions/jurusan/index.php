<?php
// admin/functions/jurusan/index.php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../auth/auth_check.php';

require_once __DIR__ . '/create.php';
require_once __DIR__ . '/read.php';
require_once __DIR__ . '/update.php';
require_once __DIR__ . '/delete.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['status' => 'error', 'message' => 'Invalid request'];
    
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'getJurusan':
                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
                $search = $_POST['search'] ?? [];
                
                $result = getJurusan($pdo, $page, $limit, $search);
                if ($result['status'] === 'success') {
                    // Format jumlah siswa untuk tampilan
                    foreach ($result['data'] as &$jurusan) {
                        $jurusan['jumlah_siswa_formatted'] = number_format($jurusan['jumlah_siswa'], 0, ',', '.');
                    }
                }
                echo json_encode($result);
                exit;

            case 'tambahJurusan':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                
                // Validasi input
                if (empty($_POST['nama_jurusan'])) {
                    throw new Exception('Nama jurusan harus diisi');
                }
                if (strlen($_POST['nama_jurusan']) > 100) {
                    throw new Exception('Nama jurusan terlalu panjang (maksimal 100 karakter)');
                }
                
                $response = createJurusan($pdo, $_POST);
                break;

            case 'getDetailJurusan':
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }

                // Get detailed information about jurusan including student count
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
                    throw new Exception('Jurusan tidak ditemukan');
                }

                // Format data
                $jurusan['tahun_angkatan'] = $jurusan['tahun_angkatan'] 
                    ? explode(',', $jurusan['tahun_angkatan']) 
                    : [];
                sort($jurusan['tahun_angkatan']);

                $response = [
                    'status' => 'success',
                    'data' => $jurusan
                ];
                break;

            case 'updateJurusan':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }

                // Validasi input
                if (empty($_POST['nama_jurusan'])) {
                    throw new Exception('Nama jurusan harus diisi');
                }
                if (strlen($_POST['nama_jurusan']) > 100) {
                    throw new Exception('Nama jurusan terlalu panjang (maksimal 100 karakter)');
                }

                $response = updateJurusan($pdo, $id, $_POST);
                break;

            case 'deleteJurusan':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }

                // Additional check before deletion
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE id_jurusan = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Tidak dapat menghapus jurusan yang masih memiliki siswa terdaftar');
                }

                $response = deleteJurusan($pdo, $id);
                break;

            case 'getAllJurusan':
                // For dropdown lists and other simple uses
                $stmt = $pdo->query("SELECT id, nama_jurusan FROM jurusan ORDER BY nama_jurusan");
                $response = [
                    'status' => 'success',
                    'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
                ];
                break;

            case 'getJurusanStats':
                // Get statistics for dashboard or reports
                $query = "SELECT j.nama_jurusan, 
                         COUNT(s.id) as total_siswa,
                         a.tahun
                         FROM jurusan j
                         LEFT JOIN siswa s ON j.id = s.id_jurusan
                         LEFT JOIN angkatan a ON s.id_angkatan = a.id
                         GROUP BY j.id, a.tahun
                         ORDER BY j.nama_jurusan, a.tahun";

                $stmt = $pdo->query($query);
                $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $response = [
                    'status' => 'success',
                    'data' => $stats
                ];
                break;

            case 'validateJurusanName':
                // For AJAX validation during input
                $nama = $_POST['nama_jurusan'] ?? '';
                $currentId = $_POST['current_id'] ?? '';
                
                if (empty($nama)) {
                    throw new Exception('Nama jurusan harus diisi');
                }

                $query = "SELECT COUNT(*) FROM jurusan WHERE nama_jurusan = ? AND id != ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$nama, $currentId]);
                
                $exists = $stmt->fetchColumn() > 0;
                $response = [
                    'status' => 'success',
                    'valid' => !$exists,
                    'message' => $exists ? 'Nama jurusan sudah digunakan' : 'Nama jurusan tersedia'
                ];
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $response = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }

    echo json_encode($response);
    exit;
}