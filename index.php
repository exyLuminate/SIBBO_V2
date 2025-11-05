<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Jika sudah login, arahkan berdasarkan role
if ($_SESSION['role'] == 'Admin') {
    header('Location: dashboard.php');
    exit;
} elseif ($_SESSION['role'] == 'Kasir') {
    header('Location: kasir.php');
    exit;
} else {
    // Jika role tidak dikenal (seharusnya tidak terjadi)
    header('Location: login.php');
    exit;
}
?>