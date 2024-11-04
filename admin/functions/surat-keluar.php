<?php
// admin/functions/surat-keluar.php

// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';

// Set header for JSON response
header('Content-Type: application/json');

// Prevent any unwanted output
ob_start();

// Handler untuk AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    try {
        if (isset($_GET['action']) && $_GET['action'] === 'stats') {
            // Handler untuk statistik
            $stats = getSuratKeluarStats(
                $_GET['tanggal_mulai'] ?? null,
                $_GET['tanggal_akhir'] ?? null
            );
            
            if ($stats === false) {
                throw new Exception('Gagal mengambil statistik');
            }
            
            echo json_encode($stats);
            exit;
        }

        // Handler untuk get single data by ID
        if (isset($_GET['id'])) {
            $surat = getSuratKeluar($_GET['id']);
            if ($surat) {
                echo json_encode($surat);
                exit;
            } else {
                throw new Exception('Data surat tidak ditemukan');
            }
        }

        // Handler untuk get all data dengan filter
        $data = getSuratKeluar();
        if ($data === false) {
            throw new Exception('Gagal mengambil data surat keluar');
        }

        // Filter data jika parameter ada
        if (!empty($_GET['tanggal_mulai']) || !empty($_GET['tanggal_akhir']) || !empty($_GET['status'])) {
            $data = array_filter($data, function ($item) {
                $valid = true;

                if (!empty($_GET['tanggal_mulai'])) {
                    $valid = $valid && strtotime($item['tanggal_keluar']) >= strtotime($_GET['tanggal_mulai']);
                }

                if (!empty($_GET['tanggal_akhir'])) {
                    $valid = $valid && strtotime($item['tanggal_keluar']) <= strtotime($_GET['tanggal_akhir']);
                }

                if (!empty($_GET['status'])) {
                    $valid = $valid && $item['status'] === $_GET['status'];
                }

                return $valid;
            });
        }

        echo json_encode(array_values($data));
        exit;

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Handler untuk POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Clear any previous output
        ob_clean();
        
        if (!isset($_POST['action'])) {
            throw new Exception('Action tidak ditemukan');
        }

        $action = $_POST['action'];
        $result = [];

        switch ($action) {
            case 'create':
                if (empty($_POST['id_surat_masuk'])) {
                    throw new Exception('ID surat masuk tidak ditemukan');
                }
                $result = createSuratKeluar($_POST);
                break;

            case 'cancel':
                if (!isset($_POST['id'])) {
                    throw new Exception('ID surat tidak ditemukan');
                }
                $result = cancelSuratKeluar($_POST['id'], $_POST['keterangan'] ?? '');
                break;

            default:
                throw new Exception('Action tidak valid');
        }

        echo json_encode($result);
        exit;

    } catch (Exception $e) {
        // Clear any previous output
        ob_clean();
        
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}

function createSuratKeluar($data)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Generate ID
        $stmt = $pdo->prepare("CALL generate_surat_id('KLR', @generated_id)");
        $stmt->execute();
        $stmt = $pdo->query("SELECT @generated_id as id");
        $id = $stmt->fetch()['id'];

        // Get surat masuk untuk memastikan status dan mendapatkan file
        $stmt = $pdo->prepare("SELECT status, file_surat FROM surat_masuk WHERE id = ?");
        $stmt->execute([$data['id_surat_masuk']]);
        $suratMasuk = $stmt->fetch();

        if (!$suratMasuk) {
            throw new Exception('Surat masuk tidak ditemukan');
        }

        if ($suratMasuk['status'] !== 'active') {
            throw new Exception('Surat masuk sudah dikeluarkan sebelumnya');
        }

        $stmt = $pdo->prepare("
            INSERT INTO surat_keluar (
                id, id_surat_masuk, tanggal_keluar, nomor_surat_keluar,
                file_surat_keluar, dikeluarkan_oleh, keterangan_keluar
            ) VALUES (
                ?, ?, ?, ?,
                ?, ?, ?
            )
        ");

        $stmt->execute([
            $id,
            $data['id_surat_masuk'],
            $data['tanggal_keluar'],
            $data['nomor_surat_keluar'],
            $suratMasuk['file_surat'],
            $_SESSION['user_id'],
            $data['keterangan_keluar'] ?? ''
        ]);

        $pdo->commit();

        return [
            'status' => true,
            'message' => 'Surat keluar berhasil ditambahkan',
            'id' => $id
        ];

    } catch (Exception $e) {
        $pdo->rollBack();
        throw new Exception($e->getMessage());
    }
}

function getSuratKeluar($id = null)
{
    global $pdo;

    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT sk.*, sm.id as id_surat_masuk,
                       sd.nomor_surat as nomor_surat_asal,
                       u.nama as dikeluarkan_oleh_name
                FROM surat_keluar sk
                JOIN surat_masuk sm ON sk.id_surat_masuk = sm.id
                JOIN surat_disposisi sd ON sm.id_surat_disposisi = sd.id
                JOIN users u ON sk.dikeluarkan_oleh = u.id
                WHERE sk.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        $stmt = $pdo->query("
            SELECT sk.*, sm.id as id_surat_masuk,
                   sd.nomor_surat as nomor_surat_asal,
                   u.nama as dikeluarkan_oleh_name
            FROM surat_keluar sk
            JOIN surat_masuk sm ON sk.id_surat_masuk = sm.id
            JOIN surat_disposisi sd ON sm.id_surat_disposisi = sd.id
            JOIN users u ON sk.dikeluarkan_oleh = u.id
            ORDER BY sk.tanggal_keluar DESC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return false;
    }
}

function updateSuratKeluar($id, $data, $file = null)
{
    global $pdo;

    try {
        $updateFields = [
            'nomor_surat_keluar' => $data['nomor_surat_keluar'],
            'tanggal_keluar' => $data['tanggal_keluar'],
            'keterangan_keluar' => $data['keterangan_keluar']
        ];

        if ($file && $file['file_surat_keluar']['size'] > 0) {
            $file_name = $file['file_surat_keluar']['name'];
            $file_tmp = $file['file_surat_keluar']['tmp_name'];
            $file_path = "../../uploads/surat_keluar/" . $id . "_" . $file_name;

            if (!move_uploaded_file($file_tmp, $file_path)) {
                throw new Exception("Gagal mengupload file");
            }

            $updateFields['file_surat_keluar'] = $file_path;
        }

        $sql = "UPDATE surat_keluar SET ";
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

        return [
            'status' => true,
            'message' => 'Surat keluar berhasil diupdate'
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal mengupdate surat keluar: ' . $e->getMessage()
        ];
    }
}

function deleteSuratKeluar($id)
{
    global $pdo;

    try {
        // Get file path before deleting record
        $stmt = $pdo->prepare("SELECT file_surat_keluar FROM surat_keluar WHERE id = ?");
        $stmt->execute([$id]);
        $surat = $stmt->fetch();

        if ($surat && file_exists($surat['file_surat_keluar'])) {
            unlink($surat['file_surat_keluar']);
        }

        $stmt = $pdo->prepare("DELETE FROM surat_keluar WHERE id = ?");
        $result = $stmt->execute([$id]);

        return [
            'status' => true,
            'message' => 'Surat keluar berhasil dihapus'
        ];
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal menghapus surat keluar: ' . $e->getMessage()
        ];
    }
}

function cancelSuratKeluar($id, $keterangan = '')
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Get surat keluar info first
        $stmt = $pdo->prepare("
            SELECT 
                sk.id_surat_masuk,
                sk.nomor_surat_keluar,
                u.nama as pembatal
            FROM surat_keluar sk
            JOIN users u ON u.id = sk.dikeluarkan_oleh
            WHERE sk.id = ?
        ");
        $stmt->execute([$id]);
        $suratKeluar = $stmt->fetch();
        
        if (!$suratKeluar) {
            throw new Exception('Surat keluar tidak ditemukan');
        }

        // Format catatan pembatalan
        $keteranganPembatalan = sprintf(
            "Dibatalkan oleh: %s\nWaktu: %s\nAlasan: %s",
            $suratKeluar['pembatal'],
            date('Y-m-d H:i:s'),
            $keterangan ?: 'Tidak ada keterangan'
        );

        // Update status surat masuk kembali ke active dengan catatan pembatalan
        $stmt = $pdo->prepare("
            UPDATE surat_masuk 
            SET status = 'active',
                keterangan_pembatalan = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$keteranganPembatalan, $suratKeluar['id_surat_masuk']]);

        // Delete surat keluar
        $stmt = $pdo->prepare("DELETE FROM surat_keluar WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        return [
            'status' => true,
            'message' => 'Surat keluar berhasil dibatalkan'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'status' => false,
            'message' => 'Gagal membatalkan surat keluar: ' . $e->getMessage()
        ];
    }
}

// Fungsi tambahan untuk mendapatkan statistik surat keluar
function getSuratKeluarStats($start_date = null, $end_date = null)
{
    global $pdo;

    try {
        $where = "";
        $params = [];

        if ($start_date && $end_date) {
            $where = "WHERE tanggal_keluar BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
        }

        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_surat,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_surat,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_surat,
                DATE_FORMAT(MIN(tanggal_keluar), '%Y-%m-%d') as earliest_date,
                DATE_FORMAT(MAX(tanggal_keluar), '%Y-%m-%d') as latest_date
            FROM surat_keluar
            $where
        ");

        $stmt->execute($params);
        return $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}
