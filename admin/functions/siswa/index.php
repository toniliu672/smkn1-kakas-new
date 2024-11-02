<?php
// admin/functions/siswa/index.php

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
            case 'getSiswa':
                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
                $search = $_POST['search'] ?? [];
                
                $result = getSiswa($pdo, $page, $limit, $search);
                echo json_encode($result);
                exit;

            case 'tambahSiswa':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                $response = createSiswa($pdo, $_POST, $_FILES['foto'] ?? null);
                break;

            case 'getDetailSiswa':
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }
                $response = getDetailSiswa($pdo, $id);
                break;

            case 'updateSiswa':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }
                $response = updateSiswa($pdo, $id, $_POST, $_FILES['foto'] ?? null);
                break;

            case 'deleteSiswa':
                if ($_SESSION['user_role'] !== 'admin') {
                    throw new Exception('Unauthorized');
                }
                $id = $_POST['id'] ?? '';
                if (!$id) {
                    throw new Exception('ID tidak valid');
                }
                $response = deleteSiswa($pdo, $id);
                break;

            case 'getAllSiswa':
                $filters = $_POST['filters'] ?? [];
                $response = getAllSiswaForPrint($pdo, $filters);
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