<?php
// admin/pages/dataMapel.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
check_login();

$page_title = "Data Mata Pelajaran - SMKN 1 Kakas";
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="glassmorphism mb-6">
            <div class="flex justify-between items-center mb-6">
                <a href="./dataGuru.php" class="text-sky-500 hover:text-sky-600">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Data Mata Pelajaran</h1>

                <?php if ($is_admin): ?>
                    <button id="btnTambahMapel" class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded transition duration-300">
                        Tambah Mata Pelajaran
                    </button>
                <?php endif; ?>
            </div>



            <!-- Filter dan Pencarian -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div>
                    <input type="text" id="searchInput" placeholder="Cari nama atau kode..."
                        class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <div>
                    <select class="filter-select w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"
                        data-filter="kategori">
                        <option value="Semua">Semua Kategori</option>
                        <option value="Umum">Umum</option>
                        <option value="Kejuruan">Kejuruan</option>
                        <option value="Muatan Lokal">Muatan Lokal</option>
                    </select>
                </div>
                <div>
                    <select class="filter-select w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"
                        data-filter="tingkat">
                        <option value="Semua">Semua Tingkat</option>
                        <option value="X">X</option>
                        <option value="XI">XI</option>
                        <option value="XII">XII</option>
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
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama Mata Pelajaran</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Tingkat</th>
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
        <div class="modal-content bg-white rounded-lg w-full max-w-2xl mx-4 md:mx-auto mt-20 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold" id="modalTitle">Detail Mata Pelajaran</h3>
                <button class="modal-close text-gray-600 hover:text-gray-800">&times;</button>
            </div>
            <div id="modalContent">
                <!-- Form Tambah/Edit -->
                <form id="formMapel" class="hidden">
                    <input type="hidden" name="action" value="tambahMapel">
                    <input type="hidden" name="id" value="">

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="kode_mapel">
                            Kode Mapel
                        </label>
                        <input type="text" id="kode_mapel" name="kode_mapel"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_mata_pelajaran">
                            Nama Mata Pelajaran *
                        </label>
                        <input type="text" id="nama_mata_pelajaran" name="nama_mata_pelajaran" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="kategori">
                            Kategori *
                        </label>
                        <select id="kategori" name="kategori" required
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="Umum">Umum</option>
                            <option value="Kejuruan">Kejuruan</option>
                            <option value="Muatan Lokal">Muatan Lokal</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Tingkat *</label>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <input type="checkbox" id="tingkat_x" name="tingkat[]" value="X"
                                    class="rounded border-gray-300 text-sky-500 focus:ring-sky-500">
                                <label for="tingkat_x" class="ml-2">Kelas X</label>
                            </div>
                            <div>
                                <input type="checkbox" id="tingkat_xi" name="tingkat[]" value="XI"
                                    class="rounded border-gray-300 text-sky-500 focus:ring-sky-500">
                                <label for="tingkat_xi" class="ml-2">Kelas XI</label>
                            </div>
                            <div>
                                <input type="checkbox" id="tingkat_xii" name="tingkat[]" value="XII"
                                    class="rounded border-gray-300 text-sky-500 focus:ring-sky-500">
                                <label for="tingkat_xii" class="ml-2">Kelas XII</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-6">
                        <button type="button" class="modal-close px-4 py-2 border rounded text-gray-600 hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-sky-500 text-white rounded hover:bg-sky-600">
                            Simpan
                        </button>
                    </div>
                </form>

                <!-- Detail View -->
                <div id="detailView" class="hidden">
                    <!-- Detail content will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="userRole" value="<?php echo htmlspecialchars($_SESSION['user_role']); ?>">

    <script src="../javascript/mapelScript.js"></script>
</body>

</html>