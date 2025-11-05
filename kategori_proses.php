<?php
session_start();
include 'koneksi.php';

// --- PENJAGA LOGIKA ADMIN ---
// Pastikan hanya Admin yang bisa memproses
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['error'] = "Anda tidak memiliki hak akses.";
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA LOGIKA ---


// Logika Aksi TAMBAH (CREATE)
if (isset($_POST['action']) && $_POST['action'] == 'tambah') {
    
    $nama_kategori = $_POST['nama_kategori'];

    if (!empty($nama_kategori)) {
        $sql = "INSERT INTO kategori (nama_kategori) VALUES (?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $nama_kategori);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Kategori baru berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan kategori: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "Nama kategori tidak boleh kosong.";
    }
    
    header('Location: kategori.php');
    exit;

// Logika Aksi EDIT (UPDATE)
} elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
    
    $id_kategori = $_POST['id_kategori'];
    $nama_kategori = $_POST['nama_kategori'];

    if (!empty($nama_kategori) && !empty($id_kategori)) {
        $sql = "UPDATE kategori SET nama_kategori = ?, updated_at = NOW() WHERE id_kategori = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "si", $nama_kategori, $id_kategori);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Kategori berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui kategori: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "Data tidak lengkap.";
    }
    
    header('Location: kategori.php');
    exit;

// Logika Aksi HAPUS (DELETE - Soft Delete)
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {

    $id_kategori = $_GET['id'];

    if (!empty($id_kategori)) {
        // Kita menggunakan SOFT DELETE (mengisi deleted_at)
        $sql = "UPDATE kategori SET deleted_at = NOW() WHERE id_kategori = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_kategori);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Kategori berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus kategori: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "ID Kategori tidak valid.";
    }
    
    header('Location: kategori.php');
    exit;

} else {
    // Jika tidak ada aksi yang cocok
    $_SESSION['error'] = "Aksi tidak dikenal.";
    header('Location: kategori.php');
    exit;
}
?>