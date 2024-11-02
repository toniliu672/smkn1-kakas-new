<?php
// admin/pages/dataGuru.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
require_once '../functions/guru/index.php';
require_once '../functions/mapel/read.php';  // hanya untuk getAllMapel()
check_login();

$page_title = "Data Guru - SMKN 1 Kakas";
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Dapatkan daftar mapel untuk dropdown
$daftarMapel = getAllMapel($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Data Guru</h1>
                <?php if ($is_admin): ?>
                    <div class="flex space-x-2">
                        <a href="./tambahGuru.php" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded transition duration-300">
                            Tambah Guru
                        </a>
                        <a href="./dataMapel.php" class="bg-slate-500 hover:bg-slate-600 text-white px-4 py-2 rounded transition duration-300">
                            Data Mapel
                        </a>
                    <?php endif; ?>
                    <a href="./printGuru.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded transition duration-300">
                        Cetak Data
                    </a>
                    </div>

            </div>


            <!-- Filter dan Pencarian -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <input type="text" id="searchInput" placeholder="Cari nama atau NIP..."
                        class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <select class="filter-select w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"
                        data-filter="status">
                        <option value="Semua">Semua Status</option>
                        <option value="PNS">PNS</option>
                        <option value="Honorer">Honorer</option>
                        <option value="Kontrak">Kontrak</option>
                    </select>
                </div>
                <div>
                    <select class="filter-select w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"
                        data-filter="status_aktif">
                        <option value="Semua">Semua Status Aktif</option>
                        <option value="aktif">Aktif</option>
                        <option value="non-aktif">Non-Aktif</option>
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
                            <th class="px-4 py-3 text-left">NIP</th>
                            <th class="px-4 py-3 text-left">Nama Lengkap</th>
                            <th class="px-4 py-3 text-left">Kontak</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Status Aktif</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data akan diisi oleh JavaScript -->
                    </tbody>
                </table>
            </div>
            <div id="debugInfo" class="mt-4 p-4 bg-gray-100 rounded hidden">
                <pre id="debugOutput"></pre>
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
                <h3 class="text-xl font-bold" id="modalTitle">Detail Guru</h3>
                <button class="modal-close text-gray-600 hover:text-gray-800">&times;</button>
            </div>
            <div id="modalContent">
                <!-- Content akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>

    <!-- Hidden input untuk role -->
    <input type="hidden" id="userRole" value="<?php echo htmlspecialchars($_SESSION['user_role']); ?>">

    <script src="../javascript/guruScript.js"></script>
    <script>
        $(document).ready(function() {
            if (window.location.search.includes('debug=true')) {
                $('#debugInfo').removeClass('hidden');
            }

            // Tambahkan fungsi debug di guruScript.js
            window.debugResponse = function(response) {
                if ($('#debugInfo').length) {
                    $('#debugOutput').text(JSON.stringify(response, null, 2));
                }
            }
        });
    </script>
</body>

</html>