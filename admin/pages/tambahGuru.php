<?php
// admin/pages/tambahGuru.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
require_once '../functions/mapel/read.php';  // untuk getAllMapel()
check_admin();  // memastikan hanya admin yang bisa akses

$page_title = "Tambah Data Guru - SMKN 1 Kakas";
$daftarMapel = getAllMapel($pdo);  // ambil daftar mata pelajaran untuk dropdown
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Tambah Data Guru</h1>
                <a href="./dataGuru.php" class="text-sky-500 hover:text-sky-600">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>

            <form id="formTambahGuru" action="../functions/guru/index.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="tambahGuru">

                <!-- Data Pribadi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                        <input type="text" name="nip" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="nama_lengkap" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kontak</label>
                        <input type="text" name="kontak" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <!-- Informasi Kepegawaian -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="">Pilih Status</option>
                            <option value="PNS">PNS</option>
                            <option value="Honorer">Honorer</option>
                            <option value="Kontrak">Kontrak</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung *</label>
                        <input type="date" name="tanggal_bergabung" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <!-- Mata Pelajaran -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran yang Diajar</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($daftarMapel as $mapel): ?>
                            <div class="flex items-center">
                                <input type="checkbox" name="mata_pelajaran[]" value="<?php echo htmlspecialchars($mapel['id']); ?>"
                                    class="rounded border-gray-300 text-sky-500 focus:ring-sky-500">
                                <label class="ml-2 text-sm text-gray-700">
                                    <?php echo htmlspecialchars($mapel['nama_mata_pelajaran']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                    <textarea name="alamat" rows="3" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                    <input type="file" name="foto" accept="image/jpeg,image/jpg,image/png"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="text-sm text-gray-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 10MB.</p>
                </div>

                <div class="flex justify-end gap-4">
                    <button type="reset" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100 transition duration-300">
                        Reset
                    </button>
                    <button type="submit" class="px-4 py-2 bg-sky-500 text-white rounded hover:bg-sky-600 transition duration-300">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#formTambahGuru').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: '../functions/guru/',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const result = typeof response === 'string' ? JSON.parse(response) : response;

                            if (result.status === 'success') {
                                $.toast({
                                    heading: 'Sukses',
                                    text: result.message,
                                    icon: 'success',
                                    position: 'top-right',
                                    hideAfter: 3000
                                });
                                setTimeout(() => {
                                    window.location.href = 'dataGuru.php';
                                }, 2000);
                            } else {
                                $.toast({
                                    heading: 'Error',
                                    text: result.message,
                                    icon: 'error',
                                    position: 'top-right'
                                });
                            }
                        } catch (e) {
                            $.toast({
                                heading: 'Error',
                                text: 'Gagal memproses response: ' + e.message,
                                icon: 'error',
                                position: 'top-right'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $.toast({
                            heading: 'Error',
                            text: 'Gagal mengirim data: ' + error,
                            icon: 'error',
                            position: 'top-right'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>