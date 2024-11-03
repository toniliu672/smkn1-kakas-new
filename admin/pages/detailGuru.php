<?php
// admin/pages/detailGuru.php
require_once '../../auth/auth_check.php';
require_once '../../config/koneksi.php';
require_once '../functions/guru/read.php';
require_once '../functions/helpers/dateIndonesia.php';
check_login();

$id = isset($_GET['id']) ? $_GET['id'] : null;
$error = null;
$guru = null;

if (!$id) {
    $error = "ID guru tidak valid";
} else {
    $guru = getGuruDetailWithTracking($pdo, $id);
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
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <a href="editGuru.php?id=<?php echo $id; ?>"
                            class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                    <?php endif; ?>
                    <button onclick="window.print()"
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        <i class="fas fa-print mr-2"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto max-w-5xl px-4">
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded no-print">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($guru): ?>
            <!-- Data Guru -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <!-- Header & Info Utama -->
                <div class="flex items-start gap-6 mb-8">
                    <!-- Foto Profil -->
                    <div class="w-32 h-32 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0">
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
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">
                            <?= htmlspecialchars($guru['nama_lengkap']) ?>
                        </h2>
                        <p class="text-gray-600 mb-2">
                            NIP: <?= $guru['nip'] ? htmlspecialchars($guru['nip']) : '-' ?>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                <?= $guru['status'] === 'PNS' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                <?= htmlspecialchars($guru['status']) ?>
                            </span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium 
                                <?= $guru['status_aktif'] === 'aktif' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ucfirst(htmlspecialchars($guru['status_aktif'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Pribadi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Email</p>
                            <p class="font-medium"><?= $guru['email'] ?: '-' ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kontak</p>
                            <p class="font-medium"><?= $guru['kontak'] ?: '-' ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-600">Alamat</p>
                            <p class="font-medium"><?= $guru['alamat'] ?: '-' ?></p>
                        </div>
                    </div>
                </div>

                <!-- Status Kepegawaian -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Kepegawaian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Tanggal Bergabung</p>
                            <p class="font-medium"><?= formatTanggalIndonesia($guru['tanggal_bergabung']) ?></p>
                        </div>
                        <?php if ($guru['status_aktif'] === 'non-aktif'): ?>
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Keluar</p>
                                <p class="font-medium"><?= formatTanggalIndonesia($guru['tanggal_keluar']) ?></p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-600">Alasan Keluar</p>
                                <p class="font-medium">
                                    <?= ucfirst(str_replace('_', ' ', $guru['alasan_keluar'])) ?>
                                    <?php if ($guru['keterangan_keluar']): ?>
                                        <br>
                                        <span class="text-sm text-gray-600">
                                            <?= htmlspecialchars($guru['keterangan_keluar']) ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Mata Pelajaran yang Diampu</h3>
                    <?php if (!empty($guru['mata_pelajaran'])): ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($guru['mata_pelajaran'] as $mapel): ?>
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">
                                    <?= htmlspecialchars($mapel['nama']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic">Belum ada mata pelajaran yang diampu</p>
                    <?php endif; ?>
                </div>

                <!-- Jurusan & Tracking -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Jurusan & Tracking</h3>

                    <!-- Statistik Tracking -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Total Perubahan</p>
                                <p class="text-xl font-semibold">
                                    <?= $guru['tracking_stats']['total_perubahan'] ?? 0 ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Jurusan Berbeda</p>
                                <p class="text-xl font-semibold">
                                    <?= $guru['tracking_stats']['jurusan_berbeda'] ?? 0 ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Jurusan Aktif</p>
                                <p class="text-xl font-semibold">
                                    <?= $guru['tracking_stats']['jurusan_aktif'] ?? 0 ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Awal Mengajar</p>
                                <p class="text-xl font-semibold">
                                    <?= $guru['tracking_stats']['awal_mengajar'] ?
                                        date('d/m/Y', strtotime($guru['tracking_stats']['awal_mengajar'])) :
                                        '-' ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Jurusan Aktif -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-800 mb-3">Jurusan Saat Ini</h4>
                        <?php if (!empty($guru['jurusan'])): ?>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($guru['jurusan'] as $jurusan): ?>
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                        <?= htmlspecialchars($jurusan['nama']) ?>
                                        <span class="text-xs ml-1">
                                            (sejak <?= date('d/m/Y', strtotime($jurusan['tanggal_mulai'])) ?>)
                                        </span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 italic">Tidak ada jurusan aktif saat ini</p>
                        <?php endif; ?>
                    </div>

                    <!-- Riwayat Perubahan -->
                    <div>
                        <h4 class="font-medium text-gray-800 mb-3">Riwayat Perubahan</h4>
                        <?php if (!empty($guru['jurusan_history'])): ?>
                            <div class="space-y-4">
                                <?php foreach ($guru['jurusan_history'] as $trackingId => $history): ?>
                                    <div class="border-l-4 border-gray-200 pl-4">
                                        <div class="text-sm text-gray-600 mb-2">
                                            Tracking ID: <?= htmlspecialchars($trackingId) ?>
                                        </div>
                                        <?php foreach ($history as $record): ?>
                                            <div class="mb-2 last:mb-0">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium">
                                                        <?= htmlspecialchars($record['nama']) ?>
                                                    </span>
                                                    <span class="text-sm px-2 py-0.5 rounded-full 
                                                        <?= getChangeTypeColor($record['change_type']) ?>">
                                                        <?= formatChangeType($record['change_type']) ?>
                                                    </span>
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    <?= date('d/m/Y', strtotime($record['change_date'])) ?> -
                                                    <?= $record['change_reason'] ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 italic">Belum ada riwayat perubahan</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    function getChangeTypeColor($type)
    {
        switch ($type) {
            case 'INITIAL':
                return 'bg-blue-100 text-blue-800';
            case 'REASSIGNMENT':
                return 'bg-yellow-100 text-yellow-800';
            case 'ADDITIONAL':
                return 'bg-green-100 text-green-800';
            case 'TERMINATION':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function formatChangeType($type)
    {
        switch ($type) {
            case 'INITIAL':
                return 'Penempatan Awal';
            case 'REASSIGNMENT':
                return 'Perpindahan';
            case 'ADDITIONAL':
                return 'Penambahan';
            case 'TERMINATION':
                return 'Pemberhentian';
            default:
                return $type;
        }
    }
    ?>

    <style>
        @media print {
            @page {
                size: A4;
                margin: 2cm;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .shadow-md {
                box-shadow: none !important;
            }

            .bg-white {
                background: none !important;
                padding: 0 !important;
            }

            .rounded-lg {
                border-radius: 0 !important;
            }
        }
    </style>
</body>

</html>