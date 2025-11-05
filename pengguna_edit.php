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
    $_SESSION['error'] = "Pengguna tidak ditemukan.";
    header('Location: pengguna.php');
    exit;
}
$id_pengguna = $_GET['id'];

// Ambil data pengguna yang mau diedit
$sql = "SELECT * FROM pengguna WHERE id_pengguna = ? AND deleted_at IS NULL";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_pengguna);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pengguna = mysqli_fetch_assoc($result);

if (!$pengguna) {
    $_SESSION['error'] = "Pengguna tidak valid atau sudah dihapus.";
    header('Location: pengguna.php');
    exit;
}

// Ambil data peran (roles) untuk dropdown
$peran_sql = "SELECT id_peran, nama_peran FROM peran WHERE deleted_at IS NULL ORDER BY nama_peran ASC";
$peran_result = mysqli_query($koneksi, $peran_sql);

$judul_halaman = "Edit Pengguna";
include 'templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Edit Pengguna: <?php echo htmlspecialchars($pengguna['username']); ?></h3>
    </div>
    <div class="card-body">
        <form action="pengguna_proses.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_pengguna" value="<?php echo $pengguna['id_pengguna']; ?>">
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($pengguna['nama_lengkap']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($pengguna['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="id_peran">Peran (Role)</label>
                <select id="id_peran" name="id_peran" required>
                    <option value="">-- Pilih Peran --</option>
                    <?php
                    while($peran = mysqli_fetch_assoc($peran_result)) {
                        $selected = ($peran['id_peran'] == $pengguna['id_peran']) ? 'selected' : '';
                        echo "<option value='{$peran['id_peran']}' $selected>" . htmlspecialchars($peran['nama_peran']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <hr>
            <p><small>Kosongkan password jika tidak ingin mengganti.</small></p>
            
            <div class="form-group">
                <label for="password">Password Baru (Opsional)</label>
                <input type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password">
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="pengguna.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
include 'templates/footer.php';
?>