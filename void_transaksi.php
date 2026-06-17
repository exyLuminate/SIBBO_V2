<?php
session_start();
include 'koneksi.php';

// 1. PENJAGA KEAMANAN: Pastikan hanya Admin yang sudah login yang bisa melakukan void
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = "Akses ditolak: Anda tidak memiliki wewenang untuk membatalkan transaksi.";
    header('Location: laporan_transaksi.php');
    exit;
}

// 2. VALIDASI CSRF TOKEN
$csrf_token = $_GET['csrf_token'] ?? '';
if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = "Aksi ditolak: Token keamanan CSRF tidak valid atau kedaluwarsa.";
    header('Location: laporan_transaksi.php');
    exit;
}

// 3. AMBIL ID TRANSAKSI
$id_transaksi = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_transaksi <= 0) {
    $_SESSION['error'] = "ID transaksi tidak valid.";
    header('Location: laporan_transaksi.php');
    exit;
}

// 4. MEMULAI TRANSAKSI DATABASE (COMMIT/ROLLBACK)
mysqli_autocommit($koneksi, false);
$sukses = true;

// 4a. Cek status transaksi saat ini (harus berstatus 'selesai')
$sql_cek = "SELECT status, nomor_invoice FROM transaksi WHERE id_transaksi = ? FOR UPDATE";
$stmt_cek = mysqli_prepare($koneksi, $sql_cek);
mysqli_stmt_bind_param($stmt_cek, "i", $id_transaksi);
mysqli_stmt_execute($stmt_cek);
$res_cek = mysqli_stmt_get_result($stmt_cek);
$trx = mysqli_fetch_assoc($res_cek);

if (!$trx) {
    $sukses = false;
    $_SESSION['error'] = "Transaksi tidak ditemukan.";
} elseif ($trx['status'] !== 'selesai') {
    $sukses = false;
    $_SESSION['error'] = "Transaksi '{$trx['nomor_invoice']}' sudah dibatalkan sebelumnya.";
}

if ($sukses) {
    $nomor_invoice = $trx['nomor_invoice'];
    
    // 4b. Ambil seluruh item belanja dari detail transaksi
    $sql_items = "SELECT id_barang, jumlah FROM detailtransaksi WHERE id_transaksi = ?";
    $stmt_items = mysqli_prepare($koneksi, $sql_items);
    mysqli_stmt_bind_param($stmt_items, "i", $id_transaksi);
    mysqli_stmt_execute($stmt_items);
    $res_items = mysqli_stmt_get_result($stmt_items);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($res_items)) {
        $items[] = $row;
    }
    
    // 4c. Kembalikan stok masing-masing barang
    foreach ($items as $item) {
        $id_barang = $item['id_barang'];
        $jumlah_kembali = $item['jumlah'];
        
        $sql_restor_stok = "UPDATE barang SET stok = stok + ?, updated_at = NOW() WHERE id_barang = ?";
        $stmt_restor = mysqli_prepare($koneksi, $sql_restor_stok);
        mysqli_stmt_bind_param($stmt_restor, "ii", $jumlah_kembali, $id_barang);
        
        if (!mysqli_stmt_execute($stmt_restor)) {
            $sukses = false;
            $_SESSION['error'] = "Gagal mengembalikan stok barang.";
            break;
        }
    }
    
    // 4d. Ubah status transaksi menjadi 'dibatalkan'
    if ($sukses) {
        $sql_void = "UPDATE transaksi SET status = 'dibatalkan', updated_at = NOW() WHERE id_transaksi = ?";
        $stmt_void = mysqli_prepare($koneksi, $sql_void);
        mysqli_stmt_bind_param($stmt_void, "i", $id_transaksi);
        
        if (!mysqli_stmt_execute($stmt_void)) {
            $sukses = false;
            $_SESSION['error'] = "Gagal memperbarui status transaksi.";
        }
    }
}

// 5. FINALIZE DB TRANSACTION (COMMIT/ROLLBACK)
if ($sukses) {
    mysqli_commit($koneksi);
    $_SESSION['message'] = "Transaksi '{$nomor_invoice}' berhasil dibatalkan dan stok telah dikembalikan!";
} else {
    mysqli_rollback($koneksi);
    if (empty($_SESSION['error'])) {
        $_SESSION['error'] = "Terjadi kesalahan sistem saat membatalkan transaksi.";
    }
}

mysqli_autocommit($koneksi, true);
header('Location: laporan_transaksi.php');
exit;
?>
