<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


$judul_halaman = "Manajemen Kategori";
include 'templates/header.php';
?>

<?php 
if (isset($_SESSION['message'])) {
    echo '<div class="alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="card">
    <div class="card-header">
        <h3>Tambah Kategori Baru</h3>
    </div>
    <div class="card-body">
        <form action="kategori_proses.php" method="POST">
            <input type="hidden" name="action" value="tambah"> 
            
            <div class="form-group">
                <label for="nama_kategori">Nama Kategori</label>
                <input type="text" id="nama_kategori" name="nama_kategori" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Daftar Kategori</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ambil data kategori yang TIDAK di-soft-delete
                $sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE deleted_at IS NULL ORDER BY nama_kategori ASC";
                $result = mysqli_query($koneksi, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id_kategori']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td>
                            <a href="kategori_edit.php?id=<?php echo $row['id_kategori']; ?>" class="btn btn-warning">Edit</a>
                            
                            <a href="kategori_proses.php?action=hapus&id=<?php echo $row['id_kategori']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Anda yakin ingin menghapus kategori ini?');">
                               Hapus
                            </a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align:center;'>Belum ada data kategori.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<?php
include 'templates/footer.php';
?>