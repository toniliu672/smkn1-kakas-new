<?php
// admin/pages/editGuru.php
require_once '../../config/koneksi.php';
require_once '../../auth/auth_check.php';
require_once '../functions/guru/index.php';
require_once '../functions/mapel/read.php';
check_admin();

$page_title = "Edit Data Guru - SMKN 1 Kakas";
$id = $_GET['id'] ?? '';

if (empty($id)) {
   header('Location: dataGuru.php');
   exit;
}

// Ambil data guru yang akan diedit
$result = getDetailGuru($pdo, $id);
if (!$result || isset($result['status']) && $result['status'] === 'error') {
   header('Location: dataGuru.php');
   exit;
}

$guru = $result;
$daftarMapel = getAllMapel($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-100">
   <?php include '../components/navbar.php'; ?>
   <div class="bg-image"></div>

   <div class="container mx-auto px-4 py-10">
       <div class="bg-white rounded-lg shadow-md p-6">
           <div class="flex justify-between items-center mb-6">
               <h1 class="text-2xl font-bold text-gray-800">Edit Data Guru</h1>
               <a href="./dataGuru.php" class="text-sky-500 hover:text-sky-600">
                   <i class="fas fa-arrow-left mr-2"></i>Kembali
               </a>
           </div>

           <form id="formEditGuru" enctype="multipart/form-data">
               <input type="hidden" name="action" value="updateGuru">
               <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

               <!-- Data Pribadi -->
               <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                       <input type="text" name="nip" value="<?php echo htmlspecialchars($guru['nip'] ?? ''); ?>"
                           class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                       <input type="text" name="nama_lengkap" required value="<?php echo htmlspecialchars($guru['nama_lengkap']); ?>"
                           class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                       <input type="email" name="email" value="<?php echo htmlspecialchars($guru['email'] ?? ''); ?>"
                           class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Kontak</label>
                       <input type="text" name="kontak" value="<?php echo htmlspecialchars($guru['kontak'] ?? ''); ?>"
                           class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                   </div>
               </div>

               <!-- Informasi Kepegawaian -->
               <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                       <select name="status" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                           <option value="PNS" <?php echo ($guru['status'] === 'PNS') ? 'selected' : ''; ?>>PNS</option>
                           <option value="Honorer" <?php echo ($guru['status'] === 'Honorer') ? 'selected' : ''; ?>>Honorer</option>
                           <option value="Kontrak" <?php echo ($guru['status'] === 'Kontrak') ? 'selected' : ''; ?>>Kontrak</option>
                       </select>
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Status Aktif *</label>
                       <select name="status_aktif" required class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                           <option value="aktif" <?php echo ($guru['status_aktif'] === 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                           <option value="non-aktif" <?php echo ($guru['status_aktif'] === 'non-aktif') ? 'selected' : ''; ?>>Non-Aktif</option>
                       </select>
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung *</label>
                       <input type="date" name="tanggal_bergabung" required value="<?php echo date('Y-m-d', strtotime($guru['tanggal_bergabung'])); ?>"
                           class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500">
                   </div>
               </div>

               <!-- Mata Pelajaran -->
               <div class="mb-6">
                   <label class="block text-sm font-medium text-gray-700 mb-2">Mata Pelajaran yang Diajar</label>
                   <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                       <?php
                       $guruMapel = array_map(function($m) { return $m['id']; }, $guru['mata_pelajaran'] ?? []);
                       foreach ($daftarMapel as $mapel):
                       ?>
                           <div class="flex items-center">
                               <input type="checkbox" name="mata_pelajaran[]" value="<?php echo htmlspecialchars($mapel['id']); ?>"
                                   <?php echo in_array($mapel['id'], $guruMapel) ? 'checked' : ''; ?>
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
                   <textarea name="alamat" rows="3" class="w-full px-4 py-2 rounded border focus:outline-none focus:ring-2 focus:ring-sky-500"><?php echo htmlspecialchars($guru['alamat'] ?? ''); ?></textarea>
               </div>

               <!-- Foto -->
               <div class="mb-6">
                   <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                   <?php if (!empty($guru['foto'])): ?>
                       <div class="mb-2">
                           <img src="../../<?php echo htmlspecialchars($guru['foto']); ?>" alt="Foto Guru"
                               class="w-32 h-32 object-cover rounded">
                       </div>
                   <?php endif; ?>
                   <input type="file" name="foto" accept="image/jpeg,image/jpg,image/png"
                       class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-500">
                   <p class="text-sm text-gray-500 mt-1">Format: JPG, JPEG, PNG. Maksimal 10MB.</p>
               </div>

               <div class="flex justify-end gap-4">
                   <a href="dataGuru.php" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100">
                       Batal
                   </a>
                   <button type="submit" class="px-4 py-2 bg-sky-500 text-white rounded hover:bg-sky-600">
                       Simpan Perubahan
                   </button>
               </div>
           </form>
       </div>
   </div>

   <script>
       $(document).ready(function() {
           $('#formEditGuru').on('submit', function(e) {
               e.preventDefault();
               const formData = new FormData(this);

               $.ajax({
                   url: '../functions/guru/',
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
                               heading: 'Sukses',
                               text: response.message,
                               icon: 'success',
                               position: 'top-right',
                               hideAfter: 3000,
                               afterHidden: function() {
                                   window.location.href = 'dataGuru.php';
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
                           text: 'Terjadi kesalahan: ' + error,
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