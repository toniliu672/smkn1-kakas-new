<?php
// admin/functions/guru/index.php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../auth/auth_check.php';

// Import semua handler
require_once __DIR__ . '/create.php';
require_once __DIR__ . '/read.php';
require_once __DIR__ . '/update.php';
require_once __DIR__ . '/delete.php';

// Handler AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set header untuk semua response
    header('Content-Type: application/json');

    $response = ['status' => 'error', 'message' => 'Invalid request'];

    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'getGuru':
                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
                $search = $_POST['search'] ?? [];
                
                $result = getGuru($pdo, $page, $limit, $search);
                if ($result['status'] === 'success') {
                    $response = [
                        'status' => 'success',
                        'data' => $result['data'],
                        'total' => $result['total']
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => $result['message']
                    ];
                }
                break;

            case 'tambahGuru':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }

                $response = createGuru($pdo, $_POST, $_FILES['foto'] ?? null);
                break;

            case 'getDetailGuru':
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }

                $detail = getDetailGuru($pdo, $id);
                // Seharusnya langsung mengembalikan detail, tidak perlu nested data
                $response = $detail;
                break;

            case 'updateGuru':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }

                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }

                $response = updateGuru($pdo, $id, $_POST, $_FILES['foto'] ?? null);
                break;

            case 'deleteGuru':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }

                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }

                $response = deleteGuru($pdo, $id);
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

    // Pastikan response selalu di-encode dengan benar
    echo json_encode($response);
    exit;
}
