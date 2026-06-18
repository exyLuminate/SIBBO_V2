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

<?php
// Konfigurasi pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Ambil total data untuk pagination
$sql_count = "SELECT COUNT(*) AS total FROM stok_masuk WHERE deleted_at IS NULL";
$res_count = mysqli_query($koneksi, $sql_count);
$count_data = mysqli_fetch_assoc($res_count);
$total_rows = $count_data['total'];
$total_pages = ceil($total_rows / $limit);
?>
<div class="card">
    <div class="card-header">
        <h3>Riwayat Stok Masuk</h3>
    </div>
    <div class="card-body">
        <table class="table table-stok">
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
                // Query JOIN untuk riwayat dengan LIMIT dan OFFSET
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
                                LIMIT ? OFFSET ?";
                
                $stmt_riwayat = mysqli_prepare($koneksi, $sql_riwayat);
                mysqli_stmt_bind_param($stmt_riwayat, "ii", $limit, $offset);
                mysqli_stmt_execute($stmt_riwayat);
                $result_riwayat = mysqli_stmt_get_result($stmt_riwayat);

                if (mysqli_num_rows($result_riwayat) > 0) {
                    while($row = mysqli_fetch_assoc($result_riwayat)) {
                ?>
                    <tr>
                        <td data-label="Waktu"><?php echo date('d-m-Y H:i', strtotime($row['tanggal_masuk'])); ?></td>
                        <td data-label="Nama Barang"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td data-label="Jumlah Masuk"><?php echo $row['jumlah_masuk']; ?></td>
                        <td data-label="Dicatat Oleh"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td data-label="Catatan"><?php echo htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Belum ada riwayat stok masuk.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Navigasi Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <!-- Tombol Previous -->
            <a href="stok_masuk.php?page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>" 
               class="pagination-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"
               <?php echo ($page <= 1) ? 'onclick="return false;"' : ''; ?>>&laquo; Prev</a>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="stok_masuk.php?page=<?php echo $i; ?>" 
                   class="pagination-item <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <a href="stok_masuk.php?page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>" 
               class="pagination-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"
               <?php echo ($page >= $total_pages) ? 'onclick="return false;"' : ''; ?>>Next &raquo;</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'templates/footer.php';
?>