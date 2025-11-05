<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Kategori tidak ditemukan.";
    header('Location: kategori.php');
    exit;
}

$id_kategori = $_GET['id'];

// Ambil data kategori yang mau diedit
$sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE id_kategori = ? AND deleted_at IS NULL";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_kategori);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kategori = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (!$kategori) {
    $_SESSION['error'] = "Kategori tidak valid atau sudah dihapus.";
    header('Location: kategori.php');
    exit;
}


$judul_halaman = "Edit Kategori";
include 'templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Edit Kategori: <?php echo htmlspecialchars($kategori['nama_kategori']); ?></h3>
    </div>
    <div class="card-body">
        <form action="kategori_proses.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_kategori" value="<?php echo $kategori['id_kategori']; ?>">
            
            <div class="form-group">
                <label for="nama_kategori">Nama Kategori</label>
                <input type="text" id="nama_kategori" name="nama_kategori" 
                       value="<?php echo htmlspecialchars($kategori['nama_kategori']); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="kategori.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
include 'templates/footer.php';
?>