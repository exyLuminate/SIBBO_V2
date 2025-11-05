<?php
session_start();
include 'koneksi.php';

// Pastikan hanya user yang login yang bisa akses
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

// Pastikan ini adalah request POST dari form
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['error'] = "Metode request tidak valid.";
    header('Location: kasir.php');
    exit;
}

// Pastikan keranjang tidak kosong
if (empty($_SESSION['keranjang'])) {
    $_SESSION['error'] = "Keranjang Anda kosong.";
    header('Location: kasir.php');
    exit;
}

// Ambil data dari POST
$id_pengguna = $_SESSION['user_id'];
$keranjang = $_SESSION['keranjang'];

// Validasi data form
if (!isset($_POST['id_metode']) || !isset($_POST['total_harga']) || !isset($_POST['jumlah_bayar'])) {
    $_SESSION['error'] = "Data pembayaran tidak lengkap.";
    header('Location: kasir.php');
    exit;
}

$id_metode = $_POST['id_metode'];
$total_harga = (float)$_POST['total_harga'];
$jumlah_bayar = (float)$_POST['jumlah_bayar'];

// Validasi pembayaran
if ($jumlah_bayar < $total_harga) {
    $_SESSION['error'] = "Jumlah bayar kurang dari total harga.";
    header('Location: kasir.php');
    exit;
}

// Persiapan data
$kembalian = $jumlah_bayar - $total_harga;
$tanggal = date('Y-m-d H:i:s');
// Buat nomor invoice unik (Contoh: INV/20251105/UNIQUE_ID)
$nomor_invoice = 'INV/' . date('Ymd') . '/' . strtoupper(uniqid()); 


// ==========================================================
// --- MULAI DATABASE TRANSACTION ---
// Ini adalah bagian paling KRUSIAL.
// Jika salah satu query gagal, semua query akan dibatalkan (rollback).
// ==========================================================

mysqli_autocommit($koneksi, false); // Matikan autocommit
$sukses = true; // Tandai sukses

// 1. QUERY: INSERT ke tabel 'transaksi' (Kepala Struk)
$sql_transaksi = "INSERT INTO transaksi (id_pengguna, id_metode, nomor_invoice, tanggal, total_harga, jumlah_bayar, kembalian, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'selesai')";
$stmt_transaksi = mysqli_prepare($koneksi, $sql_transaksi);
mysqli_stmt_bind_param(
    $stmt_transaksi, 
    "iissddd", // i, i, s, s, d, d, d
    $id_pengguna,
    $id_metode,
    $nomor_invoice,
    $tanggal,
    $total_harga,
    $jumlah_bayar,
    $kembalian
);

if (mysqli_stmt_execute($stmt_transaksi)) {
    // Berhasil, ambil ID transaksi yang baru saja dibuat
    $id_transaksi_baru = mysqli_insert_id($koneksi);

    // 2. QUERY (LOOP): INSERT ke 'detailtransaksi' DAN UPDATE 'barang'
    foreach ($keranjang as $id_barang => $item) {
        if (!$sukses) break; // Hentikan loop jika ada kegagalan sebelumnya

        $harga_saat_transaksi = (float)$item['harga'];
        $jumlah = (int)$item['jumlah'];
        $subtotal = $harga_saat_transaksi * $jumlah;

        // 2a. INSERT ke detailtransaksi
        $sql_detail = "INSERT INTO detailtransaksi (id_transaksi, id_barang, harga_saat_transaksi, jumlah, subtotal) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = mysqli_prepare($koneksi, $sql_detail);
        mysqli_stmt_bind_param(
            $stmt_detail,
            "iidis", // i, i, d, i, s
            $id_transaksi_baru,
            $id_barang,
            $harga_saat_transaksi,
            $jumlah,
            $subtotal
        );
        
        if (!mysqli_stmt_execute($stmt_detail)) {
            $sukses = false;
            $_SESSION['error'] = "Gagal menyimpan detail transaksi: " . mysqli_error($koneksi);
        }

        // 2b. UPDATE stok barang (Kurangi stok)
        if ($sukses) { // Hanya update stok jika insert detail berhasil
            $sql_stok = "UPDATE barang SET stok = stok - ?, updated_at = NOW() WHERE id_barang = ?";
            $stmt_stok = mysqli_prepare($koneksi, $sql_stok);
            mysqli_stmt_bind_param(
                $stmt_stok,
                "ii", // i, i
                $jumlah,
                $id_barang
            );

            if (!mysqli_stmt_execute($stmt_stok)) {
                $sukses = false;
                $_SESSION['error'] = "Gagal memperbarui stok barang: " . mysqli_error($koneksi);
            }
        }
    }

} else {
    // Gagal di query pertama (INSERT transaksi)
    $sukses = false;
    $_SESSION['error'] = "Gagal membuat transaksi utama: " . mysqli_error($koneksi);
}


// --- SELESAIKAN TRANSAKSI (COMMIT / ROLLBACK) ---
if ($sukses) {
    mysqli_commit($koneksi); // Simpan semua perubahan
    $_SESSION['message'] = "Transaksi berhasil disimpan! Kembalian: Rp " . number_format($kembalian, 0, ',', '.');
    
    // KOSONGKAN KERANJANG HANYA JIKA SUKSES
    unset($_SESSION['keranjang']);

} else {
    mysqli_rollback($koneksi); // Batalkan semua perubahan
    // Pesan error sudah di-set di dalam loop/if
    if (empty($_SESSION['error'])) {
        $_SESSION['error'] = "Terjadi kesalahan yang tidak diketahui saat transaksi.";
    }
}

mysqli_autocommit($koneksi, true); // Kembalikan ke mode normal
header('Location: kasir.php'); // Kembali ke halaman kasir
exit;
?>