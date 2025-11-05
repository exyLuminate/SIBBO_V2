<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---

// Ambil data kategori untuk dropdown
$kategori_sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE deleted_at IS NULL ORDER BY nama_kategori ASC";
$kategori_result = mysqli_query($koneksi, $kategori_sql);


$judul_halaman = "Tambah Barang Baru";
include 'templates/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3>Form Tambah Barang</h3>
    </div>
    <div class="card-body">
        <form action="barang_proses.php" method="POST">
            <input type="hidden" name="action" value="tambah">
            
            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text" id="nama_barang" name="nama_barang" required>
            </div>
            
            <div class="form-group">
                <label for="id_kategori">Kategori</label>
                <select id="id_kategori" name="id_kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    while($kategori = mysqli_fetch_assoc($kategori_result)) {
                        echo "<option value='{$kategori['id_kategori']}'>" . htmlspecialchars($kategori['nama_kategori']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="kode_sku">Kode SKU (Opsional)</label>
                <input type="text" id="kode_sku" name="kode_sku">
            </div>
            
            <div class="form-group">
                <label for="harga_jual">Harga Jual (Rp)</label>
                <input type="number" id="harga_jual" name="harga_jual" required min="0">
            </div>
            
            <div class="form-group">
                <label for="stok">Stok Awal</label>
                <input type="number" id="stok" name="stok" required min="0" value="0">
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Barang</button>
            <a href="barang.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
include 'templates/footer.php';
?>