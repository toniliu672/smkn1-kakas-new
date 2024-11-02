<?php
// admin/pages/tambahSiswa.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
require_once '../functions/jurusan/read.php';
require_once '../functions/angkatan/read.php';
check_admin();

$page_title = "Tambah Data Siswa - SMKN 1 Kakas";
$daftarJurusan = getAllJurusan($pdo);
$daftarAngkatan = getAllAngkatan($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
    <?php include '../components/navbar.php'; ?>

    <div class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Tambah Data Siswa</h1>
                <a href="./dataSiswa.php" class="text-sky-500 hover:text-sky-600">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>

            <form id="formTambahSiswa" enctype="multipart/form-data">
                <input type="hidden" name="action" value="tambahSiswa">

                <!-- Data Utama -->
                <div class="border-b pb-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Utama</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIS</label>
                            <input type="text" name="nis" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">NISN</label>
                            <input type="text" name="nisn" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                            <input type="text" name="nama_lengkap" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Agama</label>
                            <select name="agama" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Data Akademik -->
                <div class="border-b pb-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Akademik</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Angkatan *</label>
                            <select name="id_angkatan" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">Pilih Angkatan</option>
                                <?php foreach ($daftarAngkatan as $angkatan): ?>
                                    <option value="<?php echo htmlspecialchars($angkatan['id']); ?>">
                                        <?php echo htmlspecialchars($angkatan['tahun']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan *</label>
                            <select name="id_jurusan" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                                <option value="">Pilih Jurusan</option>
                                <?php foreach ($daftarJurusan as $jurusan): ?>
                                    <option value="<?php echo htmlspecialchars($jurusan['id']); ?>">
                                        <?php echo htmlspecialchars($jurusan['nama_jurusan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Data Kontak -->
                <div class="border-b pb-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Kontak</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. HP</label>
                            <input type="text" name="no_hp" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="alamat" rows="3" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div class="border-b pb-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Data Orang Tua</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan Ayah</label>
                            <input type="text" name="pekerjaan_ayah" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan Ibu</label>
                            <input type="text" name="pekerjaan_ibu" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. HP Orang Tua</label>
                            <input type="text" name="no_hp_orang_tua" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>
                </div>

                <!-- Foto -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Foto Siswa</h2>
                    <div id="fotoPreviewContainer" class="mb-4 hidden">
                        <img id="fotoPreview" src="" alt="Preview Foto" class="w-32 h-32 object-cover rounded">
                    </div>
                    <input type="file" name="foto" accept="image/jpeg,image/jpg,image/png"
                        class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="text-sm text-gray-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 10MB.</p>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="dataSiswa.php" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-sky-500 text-white rounded hover:bg-sky-600">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Form validation
            function validateForm() {
                const requiredFields = ['nama_lengkap', 'id_angkatan', 'id_jurusan'];
                let isValid = true;

                requiredFields.forEach(field => {
                    const value = $(`[name="${field}"]`).val();
                    if (!value) {
                        isValid = false;
                        $.toast({
                            heading: 'Error',
                            text: `${field.replace('_', ' ')} harus diisi!`,
                            icon: 'error',
                            position: 'top-right'
                        });
                    }
                });

                return isValid;
            }

            // Validasi nomor HP
            $('input[name="no_hp"], input[name="no_hp_orang_tua"]').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Preview foto
            $('input[name="foto"]').on('change', function() {
                const file = this.files[0];
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 10 * 1024 * 1024; // 10MB

                if (file) {
                    // Validasi tipe file
                    if (!allowedTypes.includes(file.type)) {
                        $.toast({
                            heading: 'Error',
                            text: 'Format file tidak diizinkan. Hanya file JPG, JPEG, dan PNG yang diperbolehkan.',
                            icon: 'error',
                            position: 'top-right'
                        });
                        this.value = '';
                        return;
                    }

                    // Validasi ukuran file
                    if (file.size > maxSize) {
                        $.toast({
                            heading: 'Error',
                            text: 'Ukuran file terlalu besar. Maksimal 10MB.',
                            icon: 'error',
                            position: 'top-right'
                        });
                        this.value = '';
                        return;
                    }

                    // Preview foto
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#fotoPreview').attr('src', e.target.result);
                        $('#fotoPreviewContainer').removeClass('hidden');
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#fotoPreviewContainer').addClass('hidden');
                }
            });

            // Form submission
            $('#formTambahSiswa').on('submit', function(e) {
                e.preventDefault();

                if (!validateForm()) return;

                const formData = new FormData(this);

                $.ajax({
                    url: '../functions/siswa/',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }

                        if (response.status === 'success') {
                            $.toast({
                                heading: 'Success',
                                text: response.message,
                                icon: 'success',
                                position: 'top-right',
                                hideAfter: 3000,
                                afterHidden: function() {
                                    window.location.href = 'dataSiswa.php';
                                }
                            });
                        } else {
                            $.toast({
                                heading: 'Error',
                                text: response.message,
                                icon: 'error',
                                position: 'top-right'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $.toast({
                            heading: 'Error',
                            text: 'Terjadi kesalahan saat menyimpan data: ' + error,
                            icon: 'error',
                            position: 'top-right'
                        });
                    }
                });
            });

            // Reset form
            function resetForm() {
                $('#formTambahSiswa')[0].reset();
                $('#fotoPreviewContainer').addClass('hidden');
            }

            // Reset button handler
            $('button[type="reset"]').on('click', function() {
                resetForm();
            });

            // Validasi tambahan
            $('input[name="nisn"]').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });

            $('input[name="nis"]').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });

            // Format tanggal lahir
            $('input[name="tanggal_lahir"]').on('change', function() {
                const date = new Date(this.value);
                if (!isNaN(date.getTime())) {
                    // Validasi umur (minimal 5 tahun)
                    const minDate = new Date();
                    minDate.setFullYear(minDate.getFullYear() - 5);

                    if (date > minDate) {
                        $.toast({
                            heading: 'Peringatan',
                            text: 'Umur minimal siswa adalah 5 tahun',
                            icon: 'warning',
                            position: 'top-right'
                        });
                        this.value = '';
                    }
                }
            });

            // Email validation
            $('input[name="email"]').on('change', function() {
                const email = this.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (email && !emailRegex.test(email)) {
                    $.toast({
                        heading: 'Error',
                        text: 'Format email tidak valid',
                        icon: 'error',
                        position: 'top-right'
                    });
                    this.value = '';
                }
            });

            // Validasi panjang input
            const maxLengths = {
                'nama_lengkap': 100,
                'tempat_lahir': 100,
                'alamat': 255,
                'nama_ayah': 100,
                'nama_ibu': 100,
                'pekerjaan_ayah': 100,
                'pekerjaan_ibu': 100,
                'no_hp': 20,
                'no_hp_orang_tua': 20,
                'email': 100
            };

            Object.keys(maxLengths).forEach(field => {
                $(`input[name="${field}"], textarea[name="${field}"]`).on('input', function() {
                    if (this.value.length > maxLengths[field]) {
                        this.value = this.value.slice(0, maxLengths[field]);
                        $.toast({
                            heading: 'Peringatan',
                            text: `Maksimal ${maxLengths[field]} karakter untuk ${field.replace('_', ' ')}`,
                            icon: 'warning',
                            position: 'top-right'
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>