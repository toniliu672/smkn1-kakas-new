<?php
// admin/pages/suratKeluar.php
require_once '../../auth/auth_check.php';
check_surat_access();
$page_title = "Surat Keluar";
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-50" data-user-role="<?php echo htmlspecialchars($_SESSION['user_role']); ?>">
    <?php include '../components/navbar.php'; ?>

    <div class="p-4">
        <div class="p-4 flex flex-col gap-4">
            <!-- Header -->
            <div class="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Surat Keluar</h1>
                    <p class="text-gray-600">Kelola surat yang telah dikeluarkan</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" id="filterTanggalMulai" class="mt-1 p-2 border rounded-md w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                        <input type="date" id="filterTanggalAkhir" class="mt-1 p-2 border rounded-md w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="filterStatus" class="mt-1 p-2 border rounded-md w-full">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-700">Total Surat Keluar</h3>
                    <p class="text-3xl font-bold text-blue-600" id="totalSurat">0</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-700">Surat Aktif</h3>
                    <p class="text-3xl font-bold text-green-600" id="activeSurat">0</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-700">Surat Dibatalkan</h3>
                    <p class="text-3xl font-bold text-red-600" id="cancelledSurat">0</p>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table id="suratTable" class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Surat Keluar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Surat Asal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Keluar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oleh</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white">
            <div class="flex flex-col gap-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Pembatalan Surat</h3>
                    <button type="button" class="closeModal text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="cancelForm" class="space-y-4">
                    <input type="hidden" id="cancelSuratId" name="id">
                    <input type="hidden" name="action" value="cancel">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alasan Pembatalan</label>
                        <textarea id="cancelKeterangan" name="keterangan" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="closeModal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                            Konfirmasi Pembatalan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../javascript/suratKeluar.js"></script>
</body>
</html>