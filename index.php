<!-- index.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMK NEGERI 1 KAKAS - Selamat Datang</title>
    <link href="./public/css/tailwind.css" rel="stylesheet">
    <link rel="stylesheet" href="./public/css/styles.css">
    <link rel="shortcut icon" href="public/logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="flex flex-col min-h-screen bg-gray-100">
    <?php include 'components/navbar.php'; ?>

    <main class="flex-grow">
        <section class="hero-section">
            <!-- Blur Background -->
            <div class="bg-image"></div>

            <!-- Text Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4">
                <h1 class="text-5xl font-bold text-sky-500 mb-6" style="text-shadow: 1px 1px 0 #000, -1px 1px 0 #000, 1px -1px 0 #000, -1px -1px 0 #000;">
                    Selamat Datang di SMK NEGERI 1 KAKAS
                </h1>
                <p class="text-xl font-bold mb-8 text-sky-500" style="text-shadow: 1px 1px 0 #000, -1px 1px 0 #000, 1px -1px 0 #000, -1px -1px 0 #000;">
                    Menyediakan pendidikan yang berkualitas menuju Indonesia Emas <span class="text-orange-400">2045</span>
                </p>
            </div>


            <!-- HAPUS DIV INI untuk menghilangkan hover overlay effect -->
            <div class="hover-overlay" id="hoverOverlay"></div>
        </section>

        <!-- Tentang Kami Section -->
        <section class="py-12 bg-gray-100">
            <div class="max-w-6xl mx-auto px-4 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Tentang Kami</h2>
                <p class="text-gray-700 text-lg mb-6">SMK NEGERI 1 KAKAS adalah lembaga pendidikan vokasi yang berkomitmen untuk menghasilkan lulusan berkualitas dan siap kerja. Kami menyediakan fasilitas modern serta kurikulum yang relevan dengan industri.</p>
            </div>
        </section>

        <!-- Fitur Utama Section -->
        <section class="py-12 bg-white">
            <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <!-- Card 1 -->
                <div class="p-6 bg-gray-100 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-book-open text-sky-500 text-5xl mb-4 transition duration-300 hover:text-sky-700"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Program Kejuruan</h3>
                    <p class="text-gray-600">Beragam program studi kejuruan yang disesuaikan dengan kebutuhan industri modern.</p>
                </div>

                <!-- Card 2 -->
                <div class="p-6 bg-gray-100 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-chalkboard-teacher text-sky-500 text-5xl mb-4 transition duration-300 hover:text-sky-700"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Fasilitas Modern</h3>
                    <p class="text-gray-600">Laboratorium dan fasilitas pengajaran terbaik untuk mendukung pembelajaran praktis.</p>
                </div>

                <!-- Card 3 -->
                <div class="p-6 bg-gray-100 rounded-lg shadow-md transform transition duration-300 hover:scale-105 hover:shadow-lg">
                    <i class="fas fa-briefcase text-sky-500 text-5xl mb-4 transition duration-300 hover:text-sky-700"></i>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Kerjasama Industri</h3>
                    <p class="text-gray-600">Bekerja sama dengan perusahaan besar untuk memastikan kesempatan magang dan kerja nyata.</p>
                </div>
            </div>
        </section>

    </main>

    <?php include 'components/footer.php'; ?>
    <script src="./js/overlay.js"></script>
</body>

</html>