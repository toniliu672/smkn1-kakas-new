<?php
// admin/functions/surat-keluar.php

require_once '../../config/koneksi.php';

function createSuratKeluar($data, $file)
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Generate ID
        $stmt = $pdo->prepare("CALL generate_surat_id('KLR', @generated_id)");
        $stmt->execute();
        $stmt = $pdo->query("SELECT @generated_id as id");
        $id = $stmt->fetch()['id'];

        // Handle file upload
        $file_name = $file['file_surat_keluar']['name'];
        $file_tmp = $file['file_surat_keluar']['tmp_name'];
        $file_path = "../../uploads/surat_keluar/" . $id . "_" . $file_name;

        if (!move_uploaded_file($file_tmp, $file_path)) {
            throw new Exception("Gagal mengupload file");
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

        $result = $stmt->execute([
            $id,
            $data['id_surat_masuk'],
            $data['tanggal_keluar'],
            $data['nomor_surat_keluar'],
            $file_path,
            $_SESSION['user_id'],
            $data['keterangan_keluar']
        ]);

        $pdo->commit();
        return [
            'status' => true,
            'message' => 'Surat keluar berhasil ditambahkan',
            'id' => $id
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        return [
            'status' => false,
            'message' => 'Gagal menambahkan surat keluar: ' . $e->getMessage()
        ];
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

        // Update status surat keluar
        $stmt = $pdo->prepare("
            UPDATE surat_keluar 
            SET status = 'cancelled',
                keterangan_keluar = CONCAT(keterangan_keluar, '\nDibatalkan: ', ?)
            WHERE id = ?
        ");
        $stmt->execute([$keterangan, $id]);

        // Status surat masuk akan diupdate otomatis melalui trigger

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
