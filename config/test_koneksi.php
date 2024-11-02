<?php
// config/test_koneksi.php
// Sertakan file koneksi
require_once 'koneksi.php';

// Fungsi untuk menguji koneksi
function testKoneksi($pdo) {
    try {
        // Coba melakukan query sederhana
        $stmt = $pdo->query('SELECT 1');
        
        if ($stmt) {
            echo "Koneksi ke database berhasil!\n";
            
            // Tampilkan informasi versi MySQL
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            echo "Versi MySQL: " . $version . "\n";
            
            // Tampilkan daftar tabel dalam database
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            echo "Daftar tabel dalam database:\n";
            foreach ($tables as $table) {
                echo "- $table\n";
            }
        }
    } catch (PDOException $e) {
        echo "Gagal terkoneksi ke database: " . $e->getMessage() . "\n";
    }
}

// Jalankan tes koneksi
testKoneksi($pdo);