<?php
session_start();

// Already logged in? Redirect based on role
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'mahasiswa') {
        header('Location: dashboard/mahasiswa.php');
        exit;
    } elseif ($role === 'asisten') {
        header('Location: dashboard/asisten.php');
        exit;
    }
}

// Header setup
$pageTitle = 'Beranda';
$activePage = 'home';
include 'index/indexheader.php';
?>

<h1 class="text-3xl font-bold mb-4">Selamat Datang di SIMPRAK</h1>
<p class="mb-4">Sistem Informasi Manajemen Praktikum berbasis web.</p>

<div class="flex gap-4 mt-4">
    <a href="login.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Login</a>
    <a href="register.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Register</a>
    <a href="katalog_praktikum.php" class="px-4 py-2 bg-gray-300 text-black rounded hover:bg-gray-400 transition">Lihat
        Katalog Praktikum</a>
</div>

<?php include 'index/indexfooter.php'; ?>