<?php
// admin/functions/guru/create.php
function createGuru($pdo, $data, $foto = null)
{
    try {
        // Generate ID yang lebih pendek
        $prefix = "GR";
        $year = date('y');
        $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $id = $prefix . $year . $random;

        // Cek duplikasi ID
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM guru WHERE id = ?");
        $stmt->execute([$id]);
        while ($stmt->fetchColumn() > 0) {
            $random = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
            $id = $prefix . $year . $random;
            $stmt->execute([$id]);
        }

        // Validasi NIP jika ada
        if (!empty($data['nip'])) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM guru WHERE nip = ?");
            $stmt->execute([$data['nip']]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("NIP sudah terdaftar!");
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

            $uploadDir = "../../../uploads/guru/";
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
            $fotoPath = 'uploads/guru/' . $id . '.' . $extension;

            if (!move_uploaded_file($foto['tmp_name'], "../../../" . $fotoPath)) {
                throw new Exception("Gagal mengupload foto.");
            }
        }

        // Mulai transaksi
        $pdo->beginTransaction();

        // Insert data guru
        $query = "INSERT INTO guru (id, nip, nama_lengkap, email, kontak, foto, alamat, 
                 tanggal_bergabung, tanggal_keluar, alasan_keluar, keterangan_keluar, 
                 status, status_aktif) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $id,
            $data['nip'] ?? null,
            $data['nama_lengkap'],
            $data['email'] ?? null,
            $data['kontak'] ?? null,
            $fotoPath,
            $data['alamat'] ?? null,
            $data['tanggal_bergabung'],
            $data['tanggal_keluar'] ?? null,
            $data['alasan_keluar'] ?? null,
            $data['keterangan_keluar'] ?? null,
            $data['status'],
            $data['status_aktif'] ?? 'aktif'
        ]);

        // Handle mata pelajaran yang diajar
        if (!empty($data['mata_pelajaran'])) {
            $stmtMapel = $pdo->prepare("INSERT INTO guru_mata_pelajaran (id_guru, id_mata_pelajaran) VALUES (?, ?)");
            foreach ($data['mata_pelajaran'] as $mapel) {
                $stmtMapel->execute([$id, $mapel]);
            }
        }

        // Handle jurusan dengan sistem tracking baru
        if (!empty($data['jurusan'])) {
            $stmtJurusan = $pdo->prepare("CALL insert_guru_jurusan(?, ?, ?, ?)");
            foreach ($data['jurusan'] as $id_jurusan) {
                $stmtJurusan->execute([
                    $id,
                    $id_jurusan,
                    'INITIAL',
                    'Penempatan awal guru'
                ]);
            }
        }

        $pdo->commit();
        return ['status' => 'success', 'message' => 'Data guru berhasil ditambahkan'];
    } catch (Exception $e) {
        $pdo->rollBack();
        if (isset($fotoPath) && file_exists("../../../" . $fotoPath)) {
            unlink("../../../" . $fotoPath);
        }
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
