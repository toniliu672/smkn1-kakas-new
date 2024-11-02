<?php
// admin/pages/search.php
require_once '../../auth/auth_check.php';
require_once '../../config/koneksi.php';
require_once '../functions/guru/read.php';
require_once '../functions/siswa/read.php';
check_login();

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$page_title = "Hasil Pencarian - SMKN 1 Kakas";

// Inisialisasi hasil pencarian
$guruResults = ['status' => 'success', 'data' => []];
$siswaResults = ['status' => 'success', 'data' => []];

// Lakukan pencarian hanya jika ada keyword
if (!empty($keyword)) {
    // Gunakan fungsi searchGlobal yang sudah dibuat khusus untuk pencarian
    $guruResults = searchGuruGlobal($pdo, $keyword);
    $siswaResults = searchSiswaGlobal($pdo, $keyword);
    
    // Validasi hasil pencarian
    if ($guruResults['status'] === 'error') {
        $guruResults = ['status' => 'success', 'data' => []];
    }
    if ($siswaResults['status'] === 'error') {
        $siswaResults = ['status' => 'success', 'data' => []];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-50">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto max-w-6xl px-4 py-8">
        <!-- Search Header with Search Bar -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    <?php if (!empty($keyword)): ?>
                        Hasil Pencarian: "<?php echo htmlspecialchars($keyword); ?>"
                    <?php else: ?>
                        Pencarian
                    <?php endif; ?>
                </h1>
                <a href="dashboard.php" class="text-purple-600 hover:text-purple-800 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                </a>
            </div>

            <!-- Search Form -->
            <form action="search.php" method="GET" class="mb-8">
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        name="keyword" 
                        value="<?php echo htmlspecialchars($keyword); ?>"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Cari nama siswa, NIS, nama guru, NIP..."
                    >
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Guru Results -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Data Guru</h2>
            <?php if (!empty($guruResults['data'])): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($guruResults['data'] as $guru): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($guru['nip']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($guru['nama_lengkap']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php echo $guru['status_aktif'] === 'Aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo htmlspecialchars($guru['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="detailGuru.php?id=<?php echo $guru['id']; ?>" 
                                           class="text-purple-600 hover:text-purple-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 bg-white rounded-lg shadow">
                    <p class="text-gray-500">Tidak ditemukan data guru yang sesuai dengan pencarian.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Siswa Results -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Data Siswa</h2>
            <?php if (!empty($siswaResults['data'])): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NISN</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jurusan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angkatan</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($siswaResults['data'] as $siswa): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($siswa['nis']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($siswa['nisn']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($siswa['nama_lengkap']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($siswa['nama_jurusan']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($siswa['angkatan']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="detailSiswa.php?id=<?php echo $siswa['id']; ?>" 
                                           class="text-purple-600 hover:text-purple-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 bg-white rounded-lg shadow">
                    <p class="text-gray-500">Tidak ditemukan data siswa yang sesuai dengan pencarian.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tambahkan script yang diperlukan -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>