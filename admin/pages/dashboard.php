<?php
// admin/pages/dashboard.php
require_once '../../auth/auth_check.php';
check_login();

$page_title = "Dashboard - SMKN 1 Kakas";
?>

<!DOCTYPE html>
<html lang="en">
<?php include '../components/head.php'; ?>

<body class="bg-gray-50">
    <?php include '../components/navbar.php'; ?>

    <!-- Hero Section dengan Search -->
    <div class="bg-blue-500 py-16 px-4">
        <div class="container mx-auto max-w-6xl">
            <div class="text-center mb-8">
                <h1 class="text-white text-4xl font-bold mb-4">Sistem Informasi SMKN 1 KAKAS</h1>
                <p class="text-purple-100 text-xl">Pengelolaan Data Siswa, Guru, dan Administrasi Surat</p>
            </div>

            <!-- Search Bar -->
            <div class="max-w-3xl mx-auto">
                <form action="search.php" method="GET" class="flex gap-2">
                    <div class="flex-1 relative">
                        <input 
                            type="text" 
                            name="keyword" 
                            class="w-full px-4 py-3 rounded-lg border-2 border-purple-400 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-white"
                            placeholder="Cari nama siswa, NIS, nama guru, NIP..."
                        >
                    </div>
                    <button type="submit" class="px-6 py-3 bg-white text-purple-700 rounded-lg hover:bg-purple-50 font-semibold transition-colors">
                        <i class="fas fa-search mr-2"></i>
                        Cari
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Menu Grid -->
    <div class="container mx-auto max-w-6xl px-4 -mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Data Siswa -->
            <a href="dataSiswa.php" class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow group">
                <div class="mb-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-user-graduate text-2xl text-blue-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Data Siswa</h3>
                <p class="text-gray-600">Kelola data dan informasi siswa</p>
            </a>

            <!-- Data Guru -->
            <a href="dataGuru.php" class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow group">
                <div class="mb-4">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <i class="fas fa-chalkboard-teacher text-2xl text-green-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Data Guru</h3>
                <p class="text-gray-600">Kelola data dan informasi guru</p>
            </a>

            <!-- Surat Masuk -->
            <a href="suratMasuk.php" class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow group">
                <div class="mb-4">
                    <div class="w-14 h-14 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                        <i class="fas fa-envelope-open-text text-2xl text-yellow-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Surat Masuk</h3>
                <p class="text-gray-600">Kelola arsip surat masuk</p>
            </a>

            <!-- Surat Keluar -->
            <a href="suratKeluar.php" class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow group">
                <div class="mb-4">
                    <div class="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center group-hover:bg-red-200 transition-colors">
                        <i class="fas fa-paper-plane text-2xl text-red-600"></i>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Surat Keluar</h3>
                <p class="text-gray-600">Kelola arsip surat keluar</p>
            </a>
        </div>
    </div>
</body>
</html>