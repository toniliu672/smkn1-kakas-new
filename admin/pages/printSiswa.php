<?php
// admin/pages/printSiswa.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
require_once '../functions/siswa/read.php';
require_once '../functions/jurusan/read.php';
require_once '../functions/angkatan/read.php';
check_login();

// Get filter params dari URL
$filters = [
    'angkatan' => $_GET['angkatan'] ?? null,
    'jurusan' => $_GET['jurusan'] ?? null
];

// Ambil data siswa dengan filter
$result = getAllSiswaForPrint($pdo, $filters);
$siswa_data = $result['status'] === 'success' ? $result['data'] : [];

// Ambil informasi filter untuk display
$filter_info = [
    'angkatan' => null,
    'jurusan' => null
];

if (!empty($filters['angkatan'])) {
    $stmt = $pdo->prepare("SELECT tahun FROM angkatan WHERE id = ?");
    $stmt->execute([$filters['angkatan']]);
    $filter_info['angkatan'] = $stmt->fetchColumn();
}

if (!empty($filters['jurusan'])) {
    $stmt = $pdo->prepare("SELECT nama_jurusan FROM jurusan WHERE id = ?");
    $stmt->execute([$filters['jurusan']]);
    $filter_info['jurusan'] = $stmt->fetchColumn();
}

$current_date = date('d F Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa - SMK NEGERI 1 KAKAS</title>
    <link href="../../public/css/tailwind.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: landscape;
                margin: 1cm;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #000;
                padding: 8px;
                font-size: 12px;
            }
            th {
                background-color: #f3f4f6 !important;
            }
        }
    </style>
</head>

<body class="bg-white p-6">
    <!-- Print Button dan Filter Info -->
    <div class="no-print mb-6">
        <div class="flex justify-between items-center mb-4">
            <a href="./dataSiswa.php<?php echo !empty($filters) ? '?' . http_build_query($filters) : ''; ?>" 
               class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                Kembali
            </a>
            <button onclick="window.print()" class="bg-sky-500 text-white px-4 py-2 rounded hover:bg-sky-600">
                Cetak Data
            </button>
        </div>
        <?php if ($filter_info['angkatan'] || $filter_info['jurusan']): ?>
            <div class="text-gray-600 mb-4">
                Filter aktif: 
                <?php if ($filter_info['jurusan']): ?>
                    <span class="font-semibold">Jurusan: <?php echo htmlspecialchars($filter_info['jurusan']); ?></span>
                <?php endif; ?>
                <?php if ($filter_info['angkatan']): ?>
                    <?php if ($filter_info['jurusan']) echo " | "; ?>
                    <span class="font-semibold">Angkatan: <?php echo htmlspecialchars($filter_info['angkatan']); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold mb-2">SMK NEGERI 1 KAKAS</h1>
        <h2 class="text-xl font-semibold mb-4">DAFTAR SISWA</h2>
        <?php if ($filter_info['angkatan'] || $filter_info['jurusan']): ?>
            <p class="text-sm mb-2">
                <?php if ($filter_info['jurusan']): ?>
                    Jurusan: <?php echo htmlspecialchars($filter_info['jurusan']); ?>
                <?php endif; ?>
                <?php if ($filter_info['angkatan']): ?>
                    <?php if ($filter_info['jurusan']) echo " | "; ?>
                    Angkatan: <?php echo htmlspecialchars($filter_info['angkatan']); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <p class="text-sm">Tanggal Cetak: <?php echo $current_date; ?></p>
    </div>

    <!-- Table -->
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-4 py-2">No</th>
                <th class="border border-gray-300 px-4 py-2">NIS</th>
                <th class="border border-gray-300 px-4 py-2">NISN</th>
                <th class="border border-gray-300 px-4 py-2">Nama Lengkap</th>
                <th class="border border-gray-300 px-4 py-2">Angkatan</th>
                <th class="border border-gray-300 px-4 py-2">Jurusan</th>
                <th class="border border-gray-300 px-4 py-2">No. HP</th>
                <th class="border border-gray-300 px-4 py-2">Alamat</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($siswa_data)): ?>
                <tr>
                    <td colspan="8" class="border border-gray-300 px-4 py-2 text-center">
                        Tidak ada data yang sesuai dengan filter
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($siswa_data as $index => $siswa): ?>
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center"><?php echo $index + 1; ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['nis']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['nisn']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['angkatan']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['nama_jurusan']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['no_hp']); ?></td>
                        <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($siswa['alamat']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="mt-8 print-only text-sm">
        <div class="float-right mr-12">
            <p class="mb-16">Kakas, <?php echo $current_date; ?></p>
            <p>Kepala Sekolah</p>
        </div>
        <div class="clear-both"></div>
    </div>
</body>
</html>