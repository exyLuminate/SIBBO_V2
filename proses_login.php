<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

if (empty($username) || empty($password)) {
    $_SESSION['error_message'] = 'Username dan password tidak boleh kosong!';
    header('Location: login.php');
    exit;
}

// Ambil data pengguna DAN perannya
$sql = "SELECT 
            pengguna.id_pengguna, 
            pengguna.username, 
            pengguna.password_hash, 
            peran.nama_peran 
        FROM 
            pengguna
        JOIN 
            peran ON pengguna.id_peran = peran.id_peran
        WHERE 
            pengguna.username = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $user = mysqli_fetch_assoc($result);

    // Verifikasi password
    if (password_verify($password, $user['password_hash'])) {
        // Password benar! Buat session
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id_pengguna'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['nama_peran']; // 'Admin' or 'Kasir'
        
        // Arahkan ke index.php
        header('Location: index.php');
        exit;

    } else {
        // Password salah
        $_SESSION['error_message'] = 'Username atau password salah.';
        header('Location: login.php');
        exit;
    }
} else {
    // Username tidak ditemukan
    $_SESSION['error_message'] = 'Username atau password salah.';
    header('Location: login.php');
    exit;
}
?>