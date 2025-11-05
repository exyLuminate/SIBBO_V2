<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---

// Ambil data barang untuk dropdown
$barang_sql = "SELECT id_barang, nama_barang, stok FROM barang WHERE deleted_at IS NULL ORDER BY nama_barang ASC";
$barang_result = mysqli_query($koneksi, $barang_sql);


$judul_halaman = "Manajemen Stok Masuk";
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
        <h3>Form Tambah Stok Masuk</h3>
    </div>
    <div class="card-body">
        <form action="stok_masuk_proses.php" method="POST">
            <input type="hidden" name="action" value="tambah_stok">
            
            <div class="form-group">
                <label for="id_barang">Pilih Barang</label>
                <select id="id_barang" name="id_barang" required>
                    <option value="">-- Pilih Barang --</option>
                    <?php
                    // Kita akan menampilkan stok saat ini di dropdown
                    while($barang = mysqli_fetch_assoc($barang_result)) {
                        echo "<option value='{$barang['id_barang']}'>" . htmlspecialchars($barang['nama_barang']) . " (Stok: {$barang['stok']})" . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="jumlah_masuk">Jumlah Masuk</label>
                <input type="number" id="jumlah_masuk" name="jumlah_masuk" required min="1">
            </div>
            
            <div class="form-group">
                <label for="catatan">Catatan (Opsional)</label>
                <input type="text" id="catatan" name="catatan" placeholder="Misal: Stok opname, Pembelian dari Supplier A">
            </div>
            
            <button type="submit" class="btn btn-primary">Simpan Stok</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Riwayat Stok Masuk (10 Terakhir)</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Masuk</th>
                    <th>Dicatat Oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query JOIN untuk riwayat
                $sql_riwayat = "SELECT 
                                    stok_masuk.tanggal_masuk, 
                                    barang.nama_barang, 
                                    stok_masuk.jumlah_masuk, 
                                    pengguna.username, 
                                    stok_masuk.catatan
                                FROM 
                                    stok_masuk
                                JOIN 
                                    barang ON stok_masuk.id_barang = barang.id_barang
                                JOIN 
                                    pengguna ON stok_masuk.id_pengguna = pengguna.id_pengguna
                                WHERE 
                                    stok_masuk.deleted_at IS NULL
                                ORDER BY 
                                    stok_masuk.id_stok_masuk DESC
                                LIMIT 10"; // Hanya tampilkan 10 terbaru
                
                $result_riwayat = mysqli_query($koneksi, $sql_riwayat);

                if (mysqli_num_rows($result_riwayat) > 0) {
                    while($row = mysqli_fetch_assoc($result_riwayat)) {
                ?>
                    <tr>
                        <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal_masuk'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td><?php echo $row['jumlah_masuk']; ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Belum ada riwayat stok masuk.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'templates/footer.php';
?>