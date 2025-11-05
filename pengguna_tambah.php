<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---

// Ambil data peran (roles) untuk dropdown
$peran_sql = "SELECT id_peran, nama_peran FROM peran WHERE deleted_at IS NULL ORDER BY nama_peran ASC";
$peran_result = mysqli_query($koneksi, $peran_sql);


$judul_halaman = "Tambah Pengguna Baru";
include 'templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Form Tambah Pengguna</h3>
    </div>
    <div class="card-body">
        <form action="pengguna_proses.php" method="POST">
            <input type="hidden" name="action" value="tambah">
            
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="id_peran">Peran (Role)</label>
                <select id="id_peran" name="id_peran" required>
                    <option value="">-- Pilih Peran --</option>
                    <?php
                    while($peran = mysqli_fetch_assoc($peran_result)) {
                        echo "<option value='{$peran['id_peran']}'>" . htmlspecialchars($peran['nama_peran']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            <a href="pengguna.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
include 'templates/footer.php';
?>