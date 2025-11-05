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


// Logika Aksi TAMBAH (CREATE)
if (isset($_POST['action']) && $_POST['action'] == 'tambah') {
    
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $id_peran = $_POST['id_peran'];
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi dasar
    if (empty($nama_lengkap) || empty($username) || empty($id_peran) || empty($password)) {
        $_SESSION['error'] = "Semua field wajib diisi.";
        header('Location: pengguna_tambah.php');
        exit;
    }

    // Validasi password
    if ($password !== $konfirmasi_password) {
        $_SESSION['error'] = "Password dan Konfirmasi Password tidak cocok.";
        header('Location: pengguna_tambah.php');
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Cek duplikat username
    // (Tambahan keamanan, meskipun di DB sudah UNIQUE)
    $sql_cek = "SELECT id_pengguna FROM pengguna WHERE username = ?";
    $stmt_cek = mysqli_prepare($koneksi, $sql_cek);
    mysqli_stmt_bind_param($stmt_cek, "s", $username);
    mysqli_stmt_execute($stmt_cek);
    $result_cek = mysqli_stmt_get_result($stmt_cek);
    
    if (mysqli_num_rows($result_cek) > 0) {
        $_SESSION['error'] = "Username '$username' sudah digunakan.";
        header('Location: pengguna_tambah.php');
        exit;
    }

    // Insert ke DB
    $sql = "INSERT INTO pengguna (nama_lengkap, username, id_peran, password_hash) 
            VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "ssis", $nama_lengkap, $username, $id_peran, $password_hash);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Pengguna baru berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Gagal menambahkan pengguna: " . mysqli_error($koneksi);
    }
    
    header('Location: pengguna.php');
    exit;

// Logika Aksi EDIT (UPDATE)
} elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
    
    $id_pengguna = $_POST['id_pengguna'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $id_peran = $_POST['id_peran'];
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Validasi dasar
    if (empty($id_pengguna) || empty($nama_lengkap) || empty($username) || empty($id_peran)) {
        $_SESSION['error'] = "Data tidak lengkap.";
        header('Location: pengguna_edit.php?id=' . $id_pengguna);
        exit;
    }

    // Query awal (tanpa password)
    $sql = "UPDATE pengguna SET nama_lengkap = ?, username = ?, id_peran = ?, updated_at = NOW()";
    $params = [$nama_lengkap, $username, $id_peran];
    $types = "ssi"; // Tipe data (string, string, integer)

    // Cek apakah password diisi (artinya mau diganti)
    if (!empty($password)) {
        // Jika password diisi, validasi konfirmasi
        if ($password !== $konfirmasi_password) {
            $_SESSION['error'] = "Password dan Konfirmasi Password tidak cocok.";
            header('Location: pengguna_edit.php?id=' . $id_pengguna);
            exit;
        }
        
        // Hash password baru
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Tambahkan ke query
        $sql .= ", password_hash = ?";
        $params[] = $password_hash;
        $types .= "s"; // Tambah tipe string
    }

    // Selesaikan query
    $sql .= " WHERE id_pengguna = ?";
    $params[] = $id_pengguna;
    $types .= "i"; // Tambah tipe integer
    
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params); // '...' adalah magic untuk unpack array
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Pengguna berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui pengguna: " . mysqli_error($koneksi);
    }
    
    header('Location: pengguna.php');
    exit;

// Logika Aksi HAPUS (DELETE - Soft Delete)
} elseif (isset($_GET['action']) && $_GET['action'] == 'hapus') {

    $id_pengguna = $_GET['id'];

    if (empty($id_pengguna)) {
        $_SESSION['error'] = "ID Pengguna tidak valid.";
        header('Location: pengguna.php');
        exit;
    }

    // PENTING: PENCEGAHAN HAPUS DIRI SENDIRI
    if ($id_pengguna == $_SESSION['user_id']) {
        $_SESSION['error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
        header('Location: pengguna.php');
        exit;
    }

    // Kita menggunakan SOFT DELETE
    $sql = "UPDATE pengguna SET deleted_at = NOW() WHERE id_pengguna = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_pengguna);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Pengguna berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus pengguna: " . mysqli_error($koneksi);
    }
    
    header('Location: pengguna.php');
    exit;

} else {
    // Jika tidak ada aksi yang cocok
    $_SESSION['error'] = "Aksi tidak dikenal.";
    header('Location: pengguna.php');
    exit;
}
?>