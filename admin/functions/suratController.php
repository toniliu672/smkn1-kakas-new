<?php
// admin/functions/suratController.php

require_once 'surat-disposisi.php';
require_once 'surat-masuk.php';
require_once 'surat-keluar.php';

class SuratController {
    // Function untuk membuat direktori upload jika belum ada
    public static function initializeUploadDirectories() {
        $paths = [
            '../../uploads/surat_disposisi',
            '../../uploads/surat_keluar'
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    // Function untuk validasi file upload
    public static function validateFileUpload($file) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!isset($file['error']) || is_array($file['error'])) {
            throw new Exception('Invalid file parameter');
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file uploaded');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('File size exceeds limit');
            default:
                throw new Exception('Unknown file upload error');
        }

        if ($file['size'] > $max_size) {
            throw new Exception('File size exceeds 5MB limit');
        }

        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only PDF and Word documents are allowed');
        }

        return true;
    }

    // Function untuk membersihkan file yang dihapus
    public static function cleanupFiles() {
        // Implementasi pembersihan file yang sudah tidak terpakai
        // Bisa dijalankan melalui cron job
    }
}

// Initialize upload directories
SuratController::initializeUploadDirectories();