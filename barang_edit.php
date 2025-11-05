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
    $_SESSION['error'] = "Barang tidak ditemukan.";
    header('Location: barang.php');
    exit;
}
$id_barang = $_GET['id'];

// Ambil data barang yang mau diedit
$sql = "SELECT * FROM barang WHERE id_barang = ? AND deleted_at IS NULL";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_barang);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$barang = mysqli_fetch_assoc($result);

if (!$barang) {
    $_SESSION['error'] = "Barang tidak valid atau sudah dihapus.";
    header('Location: barang.php');
    exit;
}

// Ambil data kategori untuk dropdown
$kategori_sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE deleted_at IS NULL ORDER BY nama_kategori ASC";
$kategori_result = mysqli_query($koneksi, $kategori_sql);

$judul_halaman = "Edit Barang";
include 'templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Edit Barang: <?php echo htmlspecialchars($barang['nama_barang']); ?></h3>
    </div>
    <div class="card-body">
        <form action="barang_proses.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id_barang" value="<?php echo $barang['id_barang']; ?>">
            
            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="id_kategori">Kategori</label>
                <select id="id_kategori" name="id_kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    mysqli_data_seek($kategori_result, 0); // Reset pointer hasil query kategori
                    while($kategori = mysqli_fetch_assoc($kategori_result)) {
                        // Cek apakah kategori ini adalah kategori yang sedang dipilih oleh barang
                        $selected = ($kategori['id_kategori'] == $barang['id_kategori']) ? 'selected' : '';
                        echo "<option value='{$kategori['id_kategori']}' $selected>" . htmlspecialchars($kategori['nama_kategori']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="kode_sku">Kode SKU (Opsional)</label>
                <input type="text" id="kode_sku" name="kode_sku" value="<?php echo htmlspecialchars($barang['kode_sku'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="harga_jual">Harga Jual (Rp)</label>
                <input type="number" id="harga_jual" name="harga_jual" value="<?php echo $barang['harga_jual']; ?>" required min="0">
            </div>
            
            <div class="form-group">
                <label for="stok">Stok</label>
                <input type="number" id="stok" name="stok" value="<?php echo $barang['stok']; ?>" readonly disabled>
                <small>Stok hanya bisa diubah melalui menu "Manajemen Stok" atau saat "Transaksi".</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="barang.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
include 'templates/footer.php';
?>