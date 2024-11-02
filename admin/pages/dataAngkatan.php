<?php
// admin/pages/dataAngkatan.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
check_login();

$page_title = "Data Angkatan - SMKN 1 Kakas";
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Data Angkatan</h1>
                <?php if ($is_admin): ?>
                    <button id="btnTambahAngkatan" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded transition duration-300">
                        Tambah Angkatan
                    </button>
                <?php endif; ?>
            </div>

            <!-- Filter dan Pencarian -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <input type="text" id="searchInput" placeholder="Cari tahun angkatan..."
                        class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <select id="itemsPerPage" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                    </select>
                </div>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto">
                <table id="dataTable" class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">No</th>
                            <th class="px-4 py-3 text-left">Tahun</th>
                            <th class="px-4 py-3 text-center">Jumlah Siswa</th>
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

    <!-- Modal -->
    <div id="modalContainer" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="modal-content bg-white rounded-lg w-full max-w-md mx-4 md:mx-auto mt-20 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="modalTitle">Tambah Angkatan</h3>
                <button class="modal-close text-gray-600 hover:text-gray-800">&times;</button>
            </div>
            <form id="formAngkatan" class="space-y-4">
                <input type="hidden" name="action" value="tambahAngkatan">
                <input type="hidden" name="id" value="">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tahun Angkatan</label>
                    <input type="number" name="tahun" required min="2000" max="2100"
                           class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="flex justify-end gap-4">
                    <button type="button" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100 modal-close">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-sky-500 text-white rounded hover:bg-sky-600">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../javascript/angkatanScript.js"></script>
</body>
</html>