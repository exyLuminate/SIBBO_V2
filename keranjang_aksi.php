<?php
session_start();
include 'koneksi.php';

// Pastikan hanya user yang login yang bisa akses
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

$action = $_GET['action'] ?? '';
$id_barang = $_GET['id'] ?? 0;

// ==========================================================
// AKSI: TAMBAH ITEM KE KERANJANG
// ==========================================================
if ($action == 'tambah' && $id_barang > 0) {
    
    // 1. Ambil data barang (stok & harga) dari DB
    $sql_barang = "SELECT nama_barang, harga_jual, stok FROM barang WHERE id_barang = ? AND deleted_at IS NULL";
    $stmt = mysqli_prepare($koneksi, $sql_barang);
    mysqli_stmt_bind_param($stmt, "i", $id_barang);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $barang = mysqli_fetch_assoc($result);

    if ($barang) {
        // 2. Cek stok
        $qty_di_keranjang = $_SESSION['keranjang'][$id_barang]['jumlah'] ?? 0;
        
        if ($barang['stok'] > $qty_di_keranjang) {
            // 3. Cek apakah barang sudah ada di keranjang
            if (isset($_SESSION['keranjang'][$id_barang])) {
                // Jika sudah ada, tambah jumlahnya
                $_SESSION['keranjang'][$id_barang]['jumlah']++;
            } else {
                // Jika belum ada, tambahkan ke keranjang
                $_SESSION['keranjang'][$id_barang] = [
                    'nama' => $barang['nama_barang'],
                    'harga' => $barang['harga_jual'],
                    'jumlah' => 1
                ];
            }
        } else {
            // Jika stok habis
            $_SESSION['error'] = "Stok barang " . htmlspecialchars($barang['nama_barang']) . " tidak mencukupi (sisa: {$barang['stok']}).";
        }
    } else {
        $_SESSION['error'] = "Barang tidak ditemukan.";
    }
}

// ==========================================================
// AKSI: KURANGI ITEM DARI KERANJANG
// ==========================================================
elseif ($action == 'kurang' && $id_barang > 0) {
    // Cek apakah barang ada di keranjang
    if (isset($_SESSION['keranjang'][$id_barang])) {
        // Kurangi jumlahnya
        $_SESSION['keranjang'][$id_barang]['jumlah']--;

        // Jika jumlahnya jadi 0, hapus item dari keranjang
        if ($_SESSION['keranjang'][$id_barang]['jumlah'] <= 0) {
            unset($_SESSION['keranjang'][$id_barang]);
        }
    }
}

// ==========================================================
// AKSI: HAPUS ITEM DARI KERANJANG
// ==========================================================
elseif ($action == 'hapus' && $id_barang > 0) {
    // Hapus item dari keranjang
    if (isset($_SESSION['keranjang'][$id_barang])) {
        unset($_SESSION['keranjang'][$id_barang]);
    }
}

// ==========================================================
// AKSI: KOSONGKAN KERANJANG
// ==========================================================
elseif ($action == 'kosongkan') {
    $_SESSION['keranjang'] = [];
    $_SESSION['message'] = "Keranjang berhasil dikosongkan.";
}


// Setelah selesai memproses aksi, kembalikan ke halaman kasir
header('Location: kasir.php');
exit;
?>