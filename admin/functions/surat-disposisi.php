<?php
// admin/functions/surat-disposisi.php

require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';

// Handler untuk AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action tidak ditemukan');
        }

        $action = $_POST['action'];
        $result = [];

        switch ($action) {
            case 'create':
                if (!isset($_FILES['file_surat'])) {
                    throw new Exception('File surat harus diupload');
                }

                // Validasi input
                if (
                    empty($_POST['nomor_surat']) || empty($_POST['tanggal_surat']) ||
                    empty($_POST['tanggal_diterima']) || empty($_POST['tujuan_surat'])
                ) {
                    throw new Exception('Semua field harus diisi');
                }

                $result = createSuratDisposisi($_POST, $_FILES);
                break;

            case 'update':
                if (!isset($_POST['id'])) {
                    throw new Exception('ID surat tidak ditemukan');
                }
                $result = updateSuratDisposisi($_POST['id'], $_POST, $_FILES);
                break;

            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('ID surat tidak ditemukan');
                }
                $result = deleteSuratDisposisi($_POST['id']);
                break;

            case 'approve':
                if (!isset($_POST['id'])) {
                    throw new Exception('ID surat tidak ditemukan');
                }
                $result = approveSuratDisposisi($_POST['id'], $_POST['keterangan'] ?? '');
                break;

            case 'reject':
                if (!isset($_POST['id'])) {
                    throw new Exception('ID surat tidak ditemukan');
                }
                $result = rejectSuratDisposisi($_POST['id'], $_POST['keterangan'] ?? '');
                break;

            default:
                throw new Exception('Action tidak valid');
        }

        if (!$result) {
            throw new Exception('Terjadi kesalahan saat memproses permintaan');
        }

        echo json_encode($result);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

// Handler untuk GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    try {
        // Handler untuk get single data by ID
        if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
            $surat = getSuratDisposisi($_GET['id']);
            if ($surat) {
                echo json_encode($surat);
                exit;
            } else {
                throw new Exception('Data surat tidak ditemukan');
            }
        }

        // Handler untuk get all data dengan filter (kode yang sudah ada)
        $data = getSuratDisposisi();

        // Filter data jika parameter ada
        if ($data && isset($_GET['tanggal_mulai']) || isset($_GET['tanggal_akhir']) || isset($_GET['status'])) {
            $filtered = array_filter($data, function ($item) {
                $valid = true;

                if (!empty($_GET['tanggal_mulai'])) {
                    $valid = $valid && strtotime($item['tanggal_surat']) >= strtotime($_GET['tanggal_mulai']);
                }

                if (!empty($_GET['tanggal_akhir'])) {
                    $valid = $valid && strtotime($item['tanggal_surat']) <= strtotime($_GET['tanggal_akhir']);
                }

                if (!empty($_GET['status'])) {
                    $valid = $valid && $item['status'] === $_GET['status'];
                }

                return $valid;
            });

            $data = array_values($filtered);
        }

        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}



function createSuratDisposisi($data, $file)
{
    global $pdo;

    try {
        // Generate ID
        $stmt = $pdo->prepare("CALL generate_surat_id('DSP', @generated_id)");
        $stmt->execute();
        $stmt = $pdo->query("SELECT @generated_id as id");
        $id = $stmt->fetch()['id'];

        // Handle file upload
        $file_name = $file['file_surat']['name'];
        $file_tmp = $file['file_surat']['tmp_name'];
        $file_path = "../../uploads/surat_disposisi/" . $id . "_" . $file_name;

        if (!move_uploaded_file($file_tmp, $file_path)) {
            throw new Exception("Gagal mengupload file");
        }

        $stmt = $pdo->prepare("
            INSERT INTO surat_disposisi (
                id, nomor_surat, tanggal_surat, tanggal_diterima, 
                tujuan_surat, file_surat, created_by
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?
            )
        ");

        $result = $stmt->execute([
            $id,
            $data['nomor_surat'],
            $data['tanggal_surat'],
            $data['tanggal_diterima'],
            $data['tujuan_surat'],
            $file_path,
            $_SESSION['user_id']
        ]);

        return [
            'status' => true,
            'message' => 'Surat disposisi berhasil ditambahkan',
            'id' => $id
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal menambahkan surat disposisi: ' . $e->getMessage()
        ];
    }
}

function getSuratDisposisi($id = null)
{
    global $pdo;

    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT 
                    sd.*,
                    DATE_FORMAT(sd.tanggal_surat, '%Y-%m-%d') as tanggal_surat,
                    DATE_FORMAT(sd.tanggal_diterima, '%Y-%m-%dT%H:%i') as tanggal_diterima,
                    u.nama as created_by_name 
                FROM surat_disposisi sd
                JOIN users u ON sd.created_by = u.id
                WHERE sd.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        $stmt = $pdo->query("
            SELECT 
                sd.*,
                DATE_FORMAT(sd.tanggal_surat, '%Y-%m-%d') as tanggal_surat,
                DATE_FORMAT(sd.tanggal_diterima, '%Y-%m-%dT%H:%i') as tanggal_diterima,
                u.nama as created_by_name 
            FROM surat_disposisi sd
            JOIN users u ON sd.created_by = u.id
            ORDER BY sd.tanggal_diterima DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log('Error in getSuratDisposisi: ' . $e->getMessage());
        return false;
    }
}

function updateSuratDisposisi($id, $data, $file = null)
{
    global $pdo;

    try {
        // Check current status
        $stmt = $pdo->prepare("SELECT status FROM surat_disposisi WHERE id = ?");
        $stmt->execute([$id]);
        $currentStatus = $stmt->fetch()['status'];

        // Only allow updates if status is 'pending' or 'rejected'
        if ($currentStatus === 'approved') {
            throw new Exception('Tidak dapat mengubah surat yang sudah disetujui');
        }

        $updateFields = [
            'nomor_surat' => $data['nomor_surat'],
            'tanggal_surat' => $data['tanggal_surat'],
            'tanggal_diterima' => $data['tanggal_diterima'],
            'tujuan_surat' => $data['tujuan_surat']
        ];

        // If surat was rejected and being resubmitted, reset status to pending
        if ($currentStatus === 'rejected') {
            $updateFields['status'] = 'pending';
            $updateFields['keterangan'] = null; // Clear previous rejection reason
        }

        // Handle file upload if new file is provided
        if ($file && $file['file_surat']['size'] > 0) {
            $file_name = $file['file_surat']['name'];
            $file_tmp = $file['file_surat']['tmp_name'];
            $file_path = "../../uploads/surat_disposisi/" . $id . "_" . $file_name;

            if (!move_uploaded_file($file_tmp, $file_path)) {
                throw new Exception("Gagal mengupload file");
            }

            $updateFields['file_surat'] = $file_path;
        }

        $sql = "UPDATE surat_disposisi SET ";
        $params = [];
        foreach ($updateFields as $key => $value) {
            $sql .= "$key = ?, ";
            $params[] = $value;
        }
        $sql = rtrim($sql, ", ");
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);

        $message = $currentStatus === 'rejected' ? 
            'Surat disposisi berhasil diupdate dan diajukan kembali' : 
            'Surat disposisi berhasil diupdate';

        return [
            'status' => true,
            'message' => $message
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal mengupdate surat disposisi: ' . $e->getMessage()
        ];
    }
}


function deleteSuratDisposisi($id)
{
    global $pdo;

    try {
        // Get file path before deleting record
        $stmt = $pdo->prepare("SELECT file_surat FROM surat_disposisi WHERE id = ?");
        $stmt->execute([$id]);
        $surat = $stmt->fetch();

        if ($surat && file_exists($surat['file_surat'])) {
            unlink($surat['file_surat']);
        }

        $stmt = $pdo->prepare("DELETE FROM surat_disposisi WHERE id = ?");
        $result = $stmt->execute([$id]);

        return [
            'status' => true,
            'message' => 'Surat disposisi berhasil dihapus'
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal menghapus surat disposisi: ' . $e->getMessage()
        ];
    }
}

function approveSuratDisposisi($id, $keterangan = '')
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get surat disposisi data first
        $stmt = $pdo->prepare("SELECT file_surat FROM surat_disposisi WHERE id = ?");
        $stmt->execute([$id]);
        $suratDisposisi = $stmt->fetch();
        
        if (!$suratDisposisi) {
            throw new Exception("Surat disposisi tidak ditemukan");
        }

        // Update status surat disposisi
        $stmt = $pdo->prepare("
            UPDATE surat_disposisi 
            SET status = 'approved', keterangan = ? 
            WHERE id = ?
        ");
        $stmt->execute([$keterangan, $id]);

        // Generate ID for surat masuk
        $stmt = $pdo->prepare("CALL generate_surat_id('MSK', @generated_id)");
        $stmt->execute();
        $stmt = $pdo->query("SELECT @generated_id as id");
        $newId = $stmt->fetch()['id'];

        // Create surat masuk
        $stmt = $pdo->prepare("
            INSERT INTO surat_masuk (
                id, id_surat_disposisi, tanggal_persetujuan,
                disetujui_oleh, keterangan_persetujuan, file_surat
            ) VALUES (
                ?, ?, NOW(),
                ?, ?, ?
            )
        ");
        $stmt->execute([
            $newId,
            $id,
            $_SESSION['user_id'],
            $keterangan,
            $suratDisposisi['file_surat']
        ]);

        $pdo->commit();
        return [
            'status' => true,
            'message' => 'Surat disposisi berhasil disetujui',
            'id_surat_masuk' => $newId
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'status' => false,
            'message' => 'Gagal menyetujui surat disposisi: ' . $e->getMessage()
        ];
    }
}


function rejectSuratDisposisi($id, $keterangan = '')
{
    global $pdo;

    try {
        if (empty($keterangan)) {
            throw new Exception('Keterangan penolakan harus diisi');
        }

        $stmt = $pdo->prepare("
            UPDATE surat_disposisi 
            SET status = 'rejected', 
                keterangan = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $result = $stmt->execute([$keterangan, $id]);

        return [
            'status' => true,
            'message' => 'Surat disposisi ditolak dan dikembalikan untuk revisi'
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal menolak surat disposisi: ' . $e->getMessage()
        ];
    }
}
