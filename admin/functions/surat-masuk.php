<?php
// admin/functions/surat-masuk.php

require_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    
    try {
        $data = getSuratMasuk();
        
        if ($data === false) {
            throw new Exception('Gagal mengambil data surat masuk');
        }

        // Filter data if parameters exist
        if (!empty($_GET['tanggal_mulai']) || !empty($_GET['tanggal_akhir']) || !empty($_GET['status'])) {
            $data = array_filter($data, function ($item) {
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action tidak ditemukan');
        }

        switch($_POST['action']) {
            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('ID surat tidak ditemukan');
                }
                echo json_encode(deleteSuratMasuk($_POST['id']));
                break;
            // ... other cases
        }
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

function getSuratMasuk($id = null) {
    global $pdo;
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT 
                    sm.*,
                    sd.nomor_surat,
                    sd.tanggal_surat,
                    sd.tujuan_surat,
                    u.nama as disetujui_oleh_name,
                    sm.file_surat
                FROM surat_masuk sm
                JOIN surat_disposisi sd ON sm.id_surat_disposisi = sd.id
                JOIN users u ON sm.disetujui_oleh = u.id
                WHERE sm.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $stmt = $pdo->query("
            SELECT 
                sm.*,
                sd.nomor_surat,
                sd.tanggal_surat,
                sd.tujuan_surat,
                u.nama as disetujui_oleh_name,
                sm.file_surat
            FROM surat_masuk sm
            JOIN surat_disposisi sd ON sm.id_surat_disposisi = sd.id
            JOIN users u ON sm.disetujui_oleh = u.id
            ORDER BY sm.tanggal_persetujuan DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error in getSuratMasuk: ' . $e->getMessage());
        return [];
    }
}
function updateSuratMasuk($id, $keterangan) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE surat_masuk 
            SET keterangan_persetujuan = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([$keterangan, $id]);

        return [
            'status' => true,
            'message' => 'Surat masuk berhasil diupdate'
        ];

    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Gagal mengupdate surat masuk: ' . $e->getMessage()
        ];
    }
}

function deleteSuratMasuk($id) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();

        // Get surat_disposisi_id first
        $stmt = $pdo->prepare("
            SELECT id_surat_disposisi 
            FROM surat_masuk 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $suratMasuk = $stmt->fetch();
        
        if (!$suratMasuk) {
            throw new Exception('Surat masuk tidak ditemukan');
        }

        // Delete surat masuk
        $stmt = $pdo->prepare("DELETE FROM surat_masuk WHERE id = ?");
        $stmt->execute([$id]);

        // Update surat disposisi status back to pending
        $stmt = $pdo->prepare("
            UPDATE surat_disposisi 
            SET status = 'pending',
                keterangan = CONCAT(IFNULL(keterangan, ''), '\n[System] Surat masuk dibatalkan pada ', NOW())
            WHERE id = ?
        ");
        $stmt->execute([$suratMasuk['id_surat_disposisi']]);

        $pdo->commit();
        return [
            'status' => true,
            'message' => 'Surat masuk berhasil dihapus dan status surat disposisi dikembalikan ke pending'
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'status' => false,
            'message' => 'Gagal menghapus surat masuk: ' . $e->getMessage()
        ];
    }
}
