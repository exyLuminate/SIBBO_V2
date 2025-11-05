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

// Helper function untuk mengubah SKU kosong menjadi NULL
function skuToNull($sku) {
    return empty(trim($sku)) ? NULL : trim($sku);
}


// Logika Aksi TAMBAH (CREATE)
if (isset($_POST['action']) && $_POST['action'] == 'tambah') {
    
    $nama_barang = $_POST['nama_barang'];
    $id_kategori = $_POST['id_kategori'];
    $harga_jual = $_POST['harga_jual'];
    $stok = $_POST['stok'];
    $kode_sku = skuToNull($_POST['kode_sku']); // Konversi jika kosong

    if (!empty($nama_barang) && !empty($id_kategori) && isset($harga_jual) && isset($stok)) {
        
        $sql = "INSERT INTO barang (nama_barang, id_kategori, kode_sku, harga_jual, stok) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            "sisdi", // s=string, i=integer, s=string, d=decimal, i=integer
            $nama_barang, 
            $id_kategori, 
            $kode_sku, 
            $harga_jual, 
            $stok
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Barang baru berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Gagal menambahkan barang: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "Semua field (kecuali SKU) wajib diisi.";
    }
    
    header('Location: barang.php');
    exit;

// Logika Aksi EDIT (UPDATE)
} elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
    
    $id_barang = $_POST['id_barang'];
    $nama_barang = $_POST['nama_barang'];
    $id_kategori = $_POST['id_kategori'];
    $harga_jual = $_POST['harga_jual'];
    $kode_sku = skuToNull($_POST['kode_sku']);
    // Stok TIDAK di-update dari sini

    if (!empty($id_barang) && !empty($nama_barang) && !empty($id_kategori) && isset($harga_jual)) {
        
        $sql = "UPDATE barang SET 
                    nama_barang = ?, 
                    id_kategori = ?, 
                    kode_sku = ?, 
                    harga_jual = ?, 
                    updated_at = NOW() 
                WHERE id_barang = ?";
        
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            "sisdi", // s, i, s, d, i
            $nama_barang, 
            $id_kategori, 
            $kode_sku, 
            $harga_jual, 
            $id_barang
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Barang berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui barang: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "Data tidak lengkap.";
    }
    
    header('Location: barang.php');
    exit;

// Logika Aksi HAPUS (DELETE - Soft Delete)
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {

    $id_barang = $_GET['id'];

    if (!empty($id_barang)) {
        // Kita menggunakan SOFT DELETE
        $sql = "UPDATE barang SET deleted_at = NOW() WHERE id_barang = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_barang);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Barang berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Gagal menghapus barang: " . mysqli_error($koneksi);
        }
    } else {
        $_SESSION['error'] = "ID Barang tidak valid.";
    }
    
    header('Location: barang.php');
    exit;

} else {
    // Jika tidak ada aksi yang cocok
    $_SESSION['error'] = "Aksi tidak dikenal.";
    header('Location: barang.php');
    exit;
}
?>