<?php
// auth/login.php
session_start();
require_once '../config/koneksi.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Cek apakah user ditemukan dan password cocok
        if ($user && $password == $user['password']) { // Perbandingan langsung tanpa `password_verify`
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['nama'];

            // Redirect ke dashboard
            header("Location: ../admin/pages/dashboard.php");
            exit();
        } else {
            $error = "Email atau password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMK NEGERI 1 KAKAS</title>
    <link href="../public/css/tailwind.css" rel="stylesheet">
    <link rel="shortcut icon" href="../public/logo.jpg" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold mb-6 text-center text-gray-800">Login</h2>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-600 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <form id="loginForm" method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input type="email" id="email" name="email" class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" id="password" name="password" class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 w-full">
                    Login
                </button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#loginForm').submit(function(e) {
                let error = '';
                const email = $('#email').val().trim();
                const password = $('#password').val();

                if (email === '' || password === '') {
                    error = 'Email dan password harus diisi.';
                }

                if (error !== '') {
                    e.preventDefault();
                    alert(error);
                }
            });
        });
    </script>
</body>

</html>