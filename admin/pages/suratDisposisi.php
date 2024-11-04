<?php
// admin/pages/suratDisposisi.php

require_once '../../auth/auth_check.php';
check_surat_access();
$page_title = "Surat Disposisi";
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-50" data-user-role="<?php echo htmlspecialchars($_SESSION['user_role']); ?>">
    <?php include '../components/navbar.php'; ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 flex flex-col gap-4">
            <div class="bg-white p-4 rounded-lg shadow flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Surat Disposisi</h1>
                    <p class="text-gray-600">Kelola surat disposisi yang masuk</p>
                </div>
                <?php if ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'pengelolaSurat'): ?>
                    <button id="btnTambah" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        Tambah Surat
                    </button>
                <?php endif; ?>
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
                            <option value="pending">Pending</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table id="suratTable" class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Surat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Diterima</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tujuan</th>
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

    <!-- Modal Form -->
    <div id="suratModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white">
            <div class="flex flex-col gap-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Tambah Surat Disposisi</h3>
                    <button type="button" class="closeModal text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="suratForm" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="suratId" name="id">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                        <input type="text" id="nomorSurat" name="nomor_surat" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                        <input type="date" id="tanggalSurat" name="tanggal_surat" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Diterima</label>
                        <input type="datetime-local" id="tanggalDiterima" name="tanggal_diterima" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tujuan Surat</label>
                        <textarea id="tujuanSurat" name="tujuan_surat" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Surat</label>
                        <input type="file" id="fileSurat" name="file_surat" accept=".pdf,.doc,.docx"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">PDF atau Word (Max. 5MB)</p>
                        <div id="fileInfo" class="mt-2 text-sm text-gray-600"></div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" class="closeModal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approval/Rejection Modal -->
    <?php if ($_SESSION['user_role'] == 'kepala_sekolah'): ?>
        <div id="approvalModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
                <div class="flex flex-col gap-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900" id="approvalTitle">Tindakan Surat</h3>
                        <button type="button" class="closeModal text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form id="approvalForm" class="space-y-4">
                        <input type="hidden" id="approvalSuratId" name="id">
                        <input type="hidden" id="approvalAction" name="action">

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                            <textarea id="keterangan" name="keterangan" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <button type="button" class="closeModal px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-md">
                                Batal
                            </button>
                            <button type="submit" id="approvalSubmit" class="px-4 py-2 rounded-md">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="../javascript/suratDisposisi.js"></script>
</body>

</html>