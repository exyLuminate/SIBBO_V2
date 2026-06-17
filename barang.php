<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


// [MODIFIKASI 1] Ambil kata kunci pencarian dari URL
$search = $_GET['search'] ?? ''; // Jika tidak ada, pakai string kosong
$search_param = "%" . $search . "%"; // Siapkan parameter untuk query LIKE


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
        <a href="barang_tambah.php" class="btn btn-primary">Tambah Barang Baru</a>
    </div>
    <div class="card-body">
    
       <form action="barang.php" method="GET" class="search-form">
    <input type="text" name="search" 
           placeholder="Cari Nama Barang atau SKU..." 
           value="<?php echo htmlspecialchars($search); ?>"
           class="form-control-search"> <button type="submit" class="btn btn-primary">Cari</button>
    <?php if (!empty($search)): ?>
        <a href="barang.php" class="btn btn-secondary">Reset</a>
    <?php endif; ?>
</form>

        <h3 style="margin-bottom: 10px">Daftar Barang</h3>

        <table class="table table-barang">
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
                // [MODIFIKASI 3] Ubah Query SQL untuk menangani pencarian & pagination
                
                // Konfigurasi pagination
                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit;

                // Ambil total data untuk pagination (termasuk filter pencarian)
                $sql_count = "SELECT COUNT(barang.id_barang) AS total 
                              FROM barang 
                              JOIN kategori ON barang.id_kategori = kategori.id_kategori 
                              WHERE barang.deleted_at IS NULL";
                if (!empty($search)) {
                    $sql_count .= " AND (barang.nama_barang LIKE ? OR barang.kode_sku LIKE ?)";
                }
                
                $stmt_count = mysqli_prepare($koneksi, $sql_count);
                if (!empty($search)) {
                    mysqli_stmt_bind_param($stmt_count, "ss", $search_param, $search_param);
                }
                mysqli_stmt_execute($stmt_count);
                $result_count = mysqli_stmt_get_result($stmt_count);
                $row_count = mysqli_fetch_assoc($result_count);
                $total_rows = $row_count['total'];
                $total_pages = ceil($total_rows / $limit);

                // Query utama dengan LIMIT & OFFSET
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
                            barang.deleted_at IS NULL";

                if (!empty($search)) {
                    $sql .= " AND (barang.nama_barang LIKE ? OR barang.kode_sku LIKE ?)";
                }

                $sql .= " ORDER BY barang.nama_barang ASC LIMIT ? OFFSET ?";
                
                $stmt = mysqli_prepare($koneksi, $sql);
                if (!empty($search)) {
                    mysqli_stmt_bind_param($stmt, "ssii", $search_param, $search_param, $limit, $offset);
                } else {
                    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
                }

                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td data-label="ID"><?php echo $row['id_barang']; ?></td>
                        <td data-label="SKU"><?php echo htmlspecialchars($row['kode_sku'] ?? '-'); ?></td>
                        <td data-label="Nama Barang"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                        <td data-label="Kategori"><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td data-label="Harga Jual (Rp)"><?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                        <td data-label="Stok"><?php echo $row['stok']; ?></td>
                        <td data-label="Aksi">
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
                    if (!empty($search)) {
                        echo "<tr><td colspan='7' style='text-align:center;'>Barang tidak ditemukan untuk kata kunci: '" . htmlspecialchars($search) . "'</td></tr>";
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>Belum ada data barang.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>

        <!-- Navigasi Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <!-- Tombol Previous -->
            <a href="barang.php?search=<?php echo urlencode($search); ?>&page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>" 
               class="pagination-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"
               <?php echo ($page <= 1) ? 'onclick="return false;"' : ''; ?>>&laquo; Prev</a>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="barang.php?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" 
                   class="pagination-item <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <a href="barang.php?search=<?php echo urlencode($search); ?>&page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>" 
               class="pagination-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"
               <?php echo ($page >= $total_pages) ? 'onclick="return false;"' : ''; ?>>Next &raquo;</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'templates/footer.php';
?>