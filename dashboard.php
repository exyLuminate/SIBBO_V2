<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
// 1. Cek apakah sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// 2. Cek apakah rolenya 'Admin'
if ($_SESSION['role'] != 'Admin') {
    // Jika bukan Admin, tendang ke halaman yang sesuai
    header('Location: kasir.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


$judul_halaman = "Dashboard";
include 'templates/header.php';
?>

<h3>Selamat Datang, Admin!</h3>
<p>Ini adalah halaman Dashboard utama Anda.</p>
<p>Anda bisa mengelola seluruh sistem dari sini.</p>

<?php
include 'templates/footer.php';
?>