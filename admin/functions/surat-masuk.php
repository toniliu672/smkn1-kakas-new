<?php
// admin/functions/surat-masuk.php

require_once '../../config/koneksi.php';

function getSuratMasuk($id = null) {
    global $pdo;
    
    try {
        if ($id) {
            $stmt = $pdo->prepare("
                SELECT sm.*, sd.*, u.nama as approved_by_name,
                       sd.file_surat as file_surat_disposisi
                FROM surat_masuk sm
                JOIN surat_disposisi sd ON sm.id_surat_disposisi = sd.id
                JOIN users u ON sm.disetujui_oleh = u.id
                WHERE sm.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        }

        $stmt = $pdo->query("
            SELECT sm.*, sd.nomor_surat, sd.tanggal_surat, 
                   sd.tujuan_surat, u.nama as approved_by_name
            FROM surat_masuk sm
            JOIN surat_disposisi sd ON sm.id_surat_disposisi = sd.id
            JOIN users u ON sm.disetujui_oleh = u.id
            ORDER BY sm.tanggal_persetujuan DESC
        ");
        return $stmt->fetchAll();

    } catch (Exception $e) {
        return false;
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