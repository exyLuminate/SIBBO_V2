<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


$judul_halaman = "Manajemen Barang";
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
        <h3>Daftar Barang</h3>
        <a href="barang_tambah.php" class="btn btn-primary">Tambah Barang Baru</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Harga Jual (Rp)</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query JOIN untuk mengambil nama kategori
                $sql = "SELECT 
                            barang.id_barang, 
                            barang.kode_sku, 
                            barang.nama_barang, 
                            kategori.nama_kategori, 
                            barang.harga_jual, 
                            barang.stok 
                        FROM 
                            barang 
                        JOIN 
                            kategori ON barang.id_kategori = kategori.id_kategori 
                        WHERE 
                            barang.deleted_at IS NULL 
                        ORDER BY 
                            barang.nama_barang ASC";
                
                $result = mysqli_query($koneksi, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id_barang']; ?></td>
                        <td><?php echo htmlspecialchars($row['kode_sku'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td><?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                        <td><?php echo $row['stok']; ?></td>
                        <td>
                            <a href="barang_edit.php?id=<?php echo $row['id_barang']; ?>" class="btn btn-warning">Edit</a>
                            
                            <a href="barang_proses.php?action=hapus&id=<?php echo $row['id_barang']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Anda yakin ingin menghapus barang ini?');">
                               Hapus
                            </a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data barang.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'templates/footer.php';
?>