<?php
// admin/pages/detailGuru.php
require_once '../../auth/auth_check.php';
require_once '../../config/koneksi.php';
require_once '../functions/guru/read.php';
require_once '../functions/helpers/dateIndonesia.php';
check_login();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$guru = null;
$error = null;

if (!$id) {
    $error = "ID guru tidak valid";
} else {
    $guru = getDetailGuru($pdo, $id);
    if (!$guru) {
        $error = "Data guru tidak ditemukan";
    }
}

$page_title = "Detail Guru - SMKN 1 Kakas";
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
                <h1 class="text-2xl font-bold text-gray-800">Detail Data Guru</h1>
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
        <?php elseif ($guru): ?>
            <!-- Data Guru -->
            <div class="bg-white rounded-lg shadow-md p-6 print:shadow-none print:p-0">
                <!-- Header -->
                <div class="flex items-start gap-6 mb-8 print:mb-4">
                    <!-- Foto Profil -->
                    <div class="w-32 h-32 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0 print:w-24 print:h-24">
                        <?php if ($guru['foto']): ?>
                            <img src="../../<?= htmlspecialchars($guru['foto']) ?>"
                                alt="Foto <?= htmlspecialchars($guru['nama_lengkap']) ?>"
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
                            <?= htmlspecialchars($guru['nama_lengkap']) ?>
                        </h2>
                        <p class="text-gray-600 mb-2 print:text-sm">
                            NIP: <?= $guru['nip'] ? htmlspecialchars($guru['nip']) : '-' ?>
                        </p>
                        <div class="flex gap-2 print:gap-1">
                            <span class="px-2 py-1 rounded text-sm <?= $guru['status'] === 'PNS' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?> print:text-xs">
                                <?= htmlspecialchars($guru['status']) ?>
                            </span>
                            <span class="px-2 py-1 rounded text-sm <?= $guru['status_aktif'] === 'aktif' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' ?> print:text-xs">
                                <?= ucfirst(htmlspecialchars($guru['status_aktif'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Grid Informasi -->
                <div class="grid grid-cols-1 gap-6 print:gap-4">
                    <!-- Data Pribadi -->
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-4 print:text-sm print:mb-2 print:font-bold">Data Pribadi</h3>
                        <table class="w-full print:text-sm">
                            <tr>
                                <td width="150" class="py-2 text-gray-600 print:py-1">Email</td>
                                <td width="10" class="py-2 text-gray-600 print:py-1">:</td>
                                <td class="py-2 text-gray-900 print:py-1"><?= $guru['email'] ? htmlspecialchars($guru['email']) : '-' ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Kontak</td>
                                <td class="py-2 text-gray-600 print:py-1">:</td>
                                <td class="py-2 text-gray-900 print:py-1"><?= $guru['kontak'] ? htmlspecialchars($guru['kontak']) : '-' ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Alamat</td>
                                <td class="py-2 text-gray-600 print:py-1">:</td>
                                <td class="py-2 text-gray-900 print:py-1"><?= $guru['alamat'] ? htmlspecialchars($guru['alamat']) : '-' ?></td>
                            </tr>
                        </table>
                    </div>

                    <!-- Data Kepegawaian -->
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-4 print:text-sm print:mb-2 print:font-bold">Data Kepegawaian</h3>
                        <table class="w-full print:text-sm">
                            <tr>
                                <td width="150" class="py-2 text-gray-600 print:py-1">Status</td>
                                <td width="10" class="py-2 text-gray-600 print:py-1">:</td>
                                <td class="py-2 text-gray-900 print:py-1"><?= htmlspecialchars($guru['status']) ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Tanggal Bergabung</td>
                                <td class="py-2 text-gray-600 print:py-1">:</td>
                                <td class="py-2 text-gray-900 print:py-1"><?= formatTanggalIndonesia($guru['tanggal_bergabung']) ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-gray-600 print:py-1">Lama Bekerja</td>
                                <td class="py-2 text-gray-600 print:py-1">:</td>
                                <td class="py-2 text-gray-900 print:py-1">
                                    <?php
                                    $tanggal_bergabung = new DateTime($guru['tanggal_bergabung']);
                                    $sekarang = new DateTime();
                                    $interval = $tanggal_bergabung->diff($sekarang);
                                    echo $interval->y . " tahun " . $interval->m . " bulan";
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Mata Pelajaran -->
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-4 print:text-sm print:mb-2 print:font-bold">Mata Pelajaran yang Diajar</h3>
                        <?php if (!empty($guru['mata_pelajaran'])): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($guru['mata_pelajaran'] as $mapel): ?>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded text-sm print:text-xs print:bg-transparent print:px-0">
                                        <?= htmlspecialchars($mapel['nama']) ?>
                                        <?php if (next($guru['mata_pelajaran'])): ?>
                                            <span class="print:inline hidden">,</span>
                                        <?php endif; ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 italic print:text-sm">Belum ada mata pelajaran yang ditugaskan</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Waktu Update -->
                <!-- <div class="mt-6 pt-4 border-t border-gray-200 print:mt-4 print:pt-2">
                    <p class="text-sm text-gray-500 print:text-xs">
                        Terakhir diperbarui: <?= date('d F Y H:i', strtotime($guru['updated_at'])) ?>
                    </p>
                </div> -->
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