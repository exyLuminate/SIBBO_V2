<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ---
// Cek apakah sudah login (Admin dan Kasir boleh akses ini)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


$judul_halaman = "Halaman Kasir";
include 'templates/header.php';
?>

<h3>Halaman Penjualan (POS)</h3>
<p>Ini adalah halaman utama untuk kasir melakukan transaksi.</p>
<p>Fitur keranjang belanja dan proses transaksi akan dibuat di Fase 4.</p>

<?php
include 'templates/footer.php';
?>