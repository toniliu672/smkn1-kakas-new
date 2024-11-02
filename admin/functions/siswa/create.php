<?php
// admin/functions/siswa/create.php

function createSiswa($pdo, $data, $foto = null)
{
    try {
        // Generate ID
        $timestamp = date('ymd');
        $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $id = "SW-{$timestamp}-{$random}";

        // Cek duplikasi ID
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE id = ?");
        $stmt->execute([$id]);
        while ($stmt->fetchColumn() > 0) {
            $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $id = "SW-{$timestamp}-{$random}";
            $stmt->execute([$id]);
        }

        // Validasi NIS dan NISN
        if (!empty($data['nis'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE nis = ?");
            $stmt->execute([$data['nis']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("NIS sudah terdaftar!");
            }
        }

        if (!empty($data['nisn'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE nisn = ?");
            $stmt->execute([$data['nisn']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("NISN sudah terdaftar!");
            }
        }

        // Handle foto
        $fotoPath = null;
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $maxSize = 10 * 1024 * 1024; // 10MB

            if (!in_array($foto['type'], $allowedTypes)) {
                throw new Exception("Tipe file tidak diizinkan. Hanya JPG, JPEG, dan PNG yang diperbolehkan.");
            }

            if ($foto['size'] > $maxSize) {
                throw new Exception("Ukuran file terlalu besar. Maksimal 10MB.");
            }

            $uploadDir = "../../../uploads/siswa/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
            $fotoPath = 'uploads/siswa/' . $id . '.' . $extension;

            if (!move_uploaded_file($foto['tmp_name'], "../../../" . $fotoPath)) {
                throw new Exception("Gagal mengupload foto.");
            }
        }

        // Mulai transaksi
        $pdo->beginTransaction();

        // Validasi angkatan dan jurusan
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM angkatan WHERE id = ?");
        $stmt->execute([$data['id_angkatan']]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Angkatan tidak valid!");
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM jurusan WHERE id = ?");
        $stmt->execute([$data['id_jurusan']]);
        if ($stmt->fetchColumn() == 0) {
            throw new Exception("Jurusan tidak valid!");
        }

        // Prepare tanggal lahir
        $tanggal_lahir = !empty($data['tanggal_lahir']) ? $data['tanggal_lahir'] : null;

        // Insert query
        $query = "INSERT INTO siswa (
            id, id_angkatan, id_jurusan, nis, nisn, nama_lengkap, 
            foto_siswa, tempat_lahir, tanggal_lahir, jenis_kelamin, agama,
            alamat, no_hp, email, nama_ayah, nama_ibu, 
            pekerjaan_ayah, pekerjaan_ibu, no_hp_orang_tua
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $id,
            $data['id_angkatan'],
            $data['id_jurusan'],
            $data['nis'] ?? null,
            $data['nisn'] ?? null,
            $data['nama_lengkap'],
            $fotoPath,
            $data['tempat_lahir'] ?? null,
            $tanggal_lahir,
            $data['jenis_kelamin'] ?? null,
            $data['agama'] ?? null,
            $data['alamat'] ?? null,
            $data['no_hp'] ?? null,
            $data['email'] ?? null,
            $data['nama_ayah'] ?? null,
            $data['nama_ibu'] ?? null,
            $data['pekerjaan_ayah'] ?? null,
            $data['pekerjaan_ibu'] ?? null,
            $data['no_hp_orang_tua'] ?? null
        ]);

        $pdo->commit();
        return [
            'status' => 'success',
            'message' => 'Data siswa berhasil ditambahkan',
            'id' => $id
        ];
    } catch (Exception $e) {
        $pdo->rollBack();
        if (isset($fotoPath) && file_exists("../../../" . $fotoPath)) {
            unlink("../../../" . $fotoPath);
        }
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}