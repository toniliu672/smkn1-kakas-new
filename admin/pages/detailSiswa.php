<?php
// admin/pages/detailSiswa.php
require_once '../../auth/auth_check.php';
require_once '../../config/koneksi.php';
require_once '../functions/siswa/read.php';
require_once '../functions/helpers/dateIndonesia.php';
check_login();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$siswa = null;
$error = null;

if ($id) {
    $result = getDetailSiswa($pdo, $id);
    if ($result['status'] === 'success') {
        $siswa = $result['data'];
    } else {
        $error = $result['message'];
    }
}

$page_title = "Detail Siswa - SMKN 1 Kakas";
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-50">
    <!-- Navbar dan Tombol hanya tampil saat mode normal -->
    <div class="no-print">
        <?php include '../components/navbar.php'; ?>
        <div class="container mx-auto max-w-5xl px-4 py-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Detail Data Siswa</h1>
                <div class="flex gap-2">
                    <button onclick="window.history.back()"
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </button>
                    <button onclick="window.print()"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        <i class="fas fa-print mr-2"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten yang akan dicetak -->
    <div class="container mx-auto max-w-5xl px-4">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded no-print">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($siswa): ?>
            <!-- Data Siswa -->
            <div class="bg-white rounded-lg shadow-md p-6 print:shadow-none print:p-0">
                <!-- Header dengan Foto -->
                <div class="flex items-start gap-6 mb-8 print:mb-4">
                    <!-- Foto Profil -->
                    <div class="w-32 h-32 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0 print:w-24 print:h-24">
                        <?php if (!empty($siswa['foto'])): ?>
                            <img src="../../uploads/siswa/<?= htmlspecialchars($siswa['foto']) ?>"
                                alt="Foto <?= htmlspecialchars($siswa['nama_lengkap']) ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                <i class="fas fa-user text-4xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info Header -->
                    <div class="flex-grow">
                        <h2 class="text-2xl font-bold text-gray-800 mb-1 print:text-xl">
                            <?= htmlspecialchars($siswa['nama_lengkap']) ?>
                        </h2>
                        <p class="text-gray-600 mb-2 print:text-sm">
                            NIS: <?= htmlspecialchars($siswa['nis']) ?>
                        </p>
                        <p class="text-gray-600 print:text-sm">
                            NISN: <?= htmlspecialchars($siswa['nisn']) ?>
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 print:block print:gap-0">
                    <!-- Data Pribadi -->
                    <div class="print:mb-4">
                        <h3 class="font-semibold text-gray-800 mb-4 print:text-sm print:font-bold">Data Pribadi</h3>
                        <table class="w-full print:text-sm">
                            <tr>
                                <td class="py-2 text-gray-600 w-1/3 print:py-1">Tempat Lahir</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['tempat_lahir']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Tanggal Lahir</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo formatTanggalIndonesia($siswa['tanggal_lahir']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Jenis Kelamin</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['jenis_kelamin']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Agama</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['agama']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">No. HP</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['no_hp']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Email</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['email']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Alamat</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['alamat']); ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Data Akademik -->
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-4 print:text-sm print:font-bold">Data Akademik</h3>
                        <table class="w-full print:text-sm">
                            <tr>
                                <td class="py-2 text-gray-600 w-1/3 print:py-1">Angkatan</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['angkatan']); ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Jurusan</td>
                                <td class="py-2 text-gray-900 print:py-1">: <?php echo htmlspecialchars($siswa['nama_jurusan']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Waktu Update -->
                <!-- <div class="mt-6 pt-4 border-t border-gray-200 print:mt-4 print:pt-2">
                    <p class="text-sm text-gray-500 print:text-xs">
                        Terakhir diperbarui: <?= date('d F Y H:i', strtotime($siswa['updated_at'])) ?>
                    </p>
                </div> -->
            </div>
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded no-print">
                Data siswa tidak ditemukan.
            </div>
        <?php endif; ?>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            @page {
                size: A4;
                margin: 1.5cm;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                background: none;
                font-size: 12pt;
            }

            .no-print {
                display: none !important;
            }

            .container {
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .bg-white {
                background: none !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            td {
                padding-top: 0.3em !important;
                padding-bottom: 0.3em !important;
                color: black !important;
            }

            h3 {
                margin-bottom: 0.8em !important;
                color: black !important;
                font-size: 14pt !important;
            }

            div {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .print\:mb-4 {
                margin-bottom: 2em !important;
            }
        }
    </style>
</body>

</html>