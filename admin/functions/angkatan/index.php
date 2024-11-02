<?php
// admin/functions/angkatan/index.php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../auth/auth_check.php';

require_once __DIR__ . '/create.php';
require_once __DIR__ . '/read.php';
require_once __DIR__ . '/update.php';
require_once __DIR__ . '/delete.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => 'Invalid request'];
    
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'getAngkatan':
                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
                $search = $_POST['search'] ?? [];
                
                $result = getAngkatan($pdo, $page, $limit, $search);
                echo json_encode($result);
                exit;

            case 'tambahAngkatan':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                $response = createAngkatan($pdo, $_POST);
                break;

            case 'getDetailAngkatan':
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }
                $response = getDetailAngkatan($pdo, $id);
                break;

            case 'updateAngkatan':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }
                $response = updateAngkatan($pdo, $id, $_POST);
                break;

            case 'deleteAngkatan':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }
                $response = deleteAngkatan($pdo, $id);
                break;

            case 'getAllAngkatan':
                $response = getAllAngkatan($pdo);
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