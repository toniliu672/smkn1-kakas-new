<?php
// admin/pages/dataSiswa.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
require_once '../functions/siswa/index.php';
require_once '../functions/jurusan/read.php';  // Untuk dropdown jurusan
require_once '../functions/angkatan/read.php'; // Untuk dropdown angkatan
check_login();

$page_title = "Data Siswa - SMKN 1 Kakas";
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Ambil data untuk dropdown filter
$daftarJurusan = getAllJurusan($pdo);
$daftarAngkatan = getAllAngkatan($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <!-- Judul dan Navigasi Utama -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Data Siswa</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="./dataAngkatan.php"
                        class="bg-slate-500 hover:bg-slate-600 text-white px-4 py-2 rounded transition duration-300 text-sm">
                        <i class="fas fa-graduation-cap mr-2"></i>Data Angkatan
                    </a>
                    <a href="./dataJurusan.php"
                        class="bg-slate-500 hover:bg-slate-600 text-white px-4 py-2 rounded transition duration-300 text-sm">
                        <i class="fas fa-school mr-2"></i>Data Jurusan
                    </a>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex flex-wrap gap-2 justify-end">
                <?php if ($is_admin): ?>
                    <a href="./tambahSiswa.php"
                        class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded transition duration-300 text-sm">
                        <i class="fas fa-plus mr-2"></i>Tambah Siswa
                    </a>
                <?php endif; ?>
                <a href="./printSiswa.php" class="print-link bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition duration-300">
                    <i class="fas fa-print mr-2"></i>Cetak Data
                </a>
            </div>
        </div>

        <!-- Filter dan Pencarian -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div>
                <input type="text" id="searchInput" placeholder="Cari nama/NIS/NISN..."
                    class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>
            <div>
                <select class="filter-select w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"
                    data-filter="angkatan">
                    <option value="">Semua Angkatan</option>
                    <?php foreach ($daftarAngkatan as $angkatan): ?>
                        <option value="<?= htmlspecialchars($angkatan['id']) ?>">
                            <?= htmlspecialchars($angkatan['tahun']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select class="filter-select w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"
                    data-filter="jurusan">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($daftarJurusan as $jurusan): ?>
                        <option value="<?= htmlspecialchars($jurusan['id']) ?>">
                            <?= htmlspecialchars($jurusan['nama_jurusan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <select id="itemsPerPage" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                    <option value="100">100 per halaman</option>
                </select>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="dataTable" class="min-w-full bg-white rounded-lg overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">NIS</th>
                        <th class="px-4 py-3 text-left">NISN</th>
                        <th class="px-4 py-3 text-left">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left">Angkatan</th>
                        <th class="px-4 py-3 text-left">Jurusan</th>
                        <th class="px-4 py-3 text-left">No. HP</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data akan diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
            <div class="text-gray-600">
                Total: <span id="totalData">0</span> data
            </div>
            <div class="pagination flex gap-2">
                <!-- Pagination akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Detail -->
    <div id="modalContainer" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="modal-content bg-white rounded-lg w-full max-w-2xl mx-4 md:mx-auto mt-20 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="modalTitle">Detail Siswa</h3>
                <button class="modal-close text-gray-600 hover:text-gray-800">&times;</button>
            </div>
            <div id="modalContent">
                <!-- Content akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>

    <script src="../javascript/siswaScript.js"></script>
</body>

</html>