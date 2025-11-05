<?php
session_start();
include 'koneksi.php';

// --- PENJAGA LOGIKA ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['error'] = "Anda tidak memiliki hak akses.";
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA LOGIKA ---


if (isset($_POST['action']) && $_POST['action'] == 'tambah_stok') {
    
    $id_barang = $_POST['id_barang'];
    $jumlah_masuk = $_POST['jumlah_masuk'];
    $catatan = $_POST['catatan'] ?? null; // Ambil catatan, jika kosong jadikan NULL
    
    // Ambil ID pengguna yang sedang login (yang mencatat)
    $id_pengguna = $_SESSION['user_id'];
    
    // Set tanggal saat ini
    $tanggal_masuk = date('Y-m-d H:i:s');

    // Validasi input
    if (empty($id_barang) || empty($jumlah_masuk) || $jumlah_masuk <= 0) {
        $_SESSION['error'] = "Data tidak valid. Pastikan barang dipilih dan jumlah masuk lebih dari 0.";
        header('Location: stok_masuk.php');
        exit;
    }

    // --- LOGIKA DUA QUERY ---
    // Untuk keamanan data, kita gunakan Transaksi. 
    // Jika salah satu query gagal, keduanya akan dibatalkan (rollback).
    
    mysqli_autocommit($koneksi, false); // Mulai mode transaksi
    $sukses = true; // Tandai sukses

    // Query 1: INSERT ke tabel stok_masuk (Riwayat/Log)
    $sql_insert = "INSERT INTO stok_masuk (id_barang, id_pengguna, jumlah_masuk, tanggal_masuk, catatan) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
    mysqli_stmt_bind_param(
        $stmt_insert, 
        "iiiss", // i=integer, i, i, s=string, s
        $id_barang, 
        $id_pengguna, 
        $jumlah_masuk, 
        $tanggal_masuk, 
        $catatan
    );

    if (!mysqli_stmt_execute($stmt_insert)) {
        $sukses = false; // Gagal query 1
        $_SESSION['error'] = "Gagal mencatat riwayat stok: " . mysqli_error($koneksi);
    }
    
    // Query 2: UPDATE tabel barang (Stok Master)
    if ($sukses) { // Hanya lanjut jika query 1 berhasil
        $sql_update = "UPDATE barang SET stok = stok + ?, updated_at = NOW() WHERE id_barang = ?";
        $stmt_update = mysqli_prepare($koneksi, $sql_update);
        mysqli_stmt_bind_param(
            $stmt_update, 
            "ii", // i=integer, i
            $jumlah_masuk, 
            $id_barang
        );

        if (!mysqli_stmt_execute($stmt_update)) {
            $sukses = false; // Gagal query 2
            $_SESSION['error'] = "Gagal memperbarui stok master: " . mysqli_error($koneksi);
        }
    }

    // Selesaikan Transaksi
    if ($sukses) {
        mysqli_commit($koneksi); // Simpan semua perubahan
        $_SESSION['message'] = "Stok berhasil ditambahkan.";
    } else {
        mysqli_rollback($koneksi); // Batalkan semua perubahan
        // Pesan error sudah di-set di atas
    }

    mysqli_autocommit($koneksi, true); // Kembalikan ke mode normal
    header('Location: stok_masuk.php');
    exit;

} else {
    // Jika tidak ada aksi yang cocok
    $_SESSION['error'] = "Aksi tidak dikenal.";
    header('Location: stok_masuk.php');
    exit;
}
?>