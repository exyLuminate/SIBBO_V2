<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---

// Ambil tanggal dari URL, jika tidak ada, pakai tanggal hari ini
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

$judul_halaman = "Laporan Penjualan";
include 'templates/header.php';
?>

<?php 
if (isset($_SESSION['message'])) {
    echo '<div class="alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>

<div class="card">
    <div class="card-header">
        <h3>Filter Laporan</h3>
    </div>
    <div class="card-body">
        <form action="laporan_transaksi.php" method="GET" class="filter-form">
            <div class="form-group">
                <label for="tgl_mulai">Dari Tanggal</label>
                <input type="date" id="tgl_mulai" name="tgl_mulai" 
                       value="<?php echo htmlspecialchars($tgl_mulai); ?>" class="form-control-search">
            </div>
            <div class="form-group">
                <label for="tgl_selesai">Sampai Tanggal</label>
                <input type="date" id="tgl_selesai" name="tgl_selesai"
                       value="<?php echo htmlspecialchars($tgl_selesai); ?>" class="form-control-search">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="laporan_transaksi.php" class="btn btn-secondary">Reset (Hari Ini)</a>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>Riwayat Transaksi</h3>
    </div>
    <div class="card-body">
        <table class="table table-laporan">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Invoice</th>
                    <th>Kasir</th>
                    <th>Metode Bayar</th>
                    <th>Total (Rp)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Tambahkan jam 23:59:59 ke tanggal selesai agar transaksi di tanggal itu ikut terambil
                $tgl_selesai_end = $tgl_selesai . ' 23:59:59';
                
                // Konfigurasi pagination
                $limit = 15;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit;

                // Ambil total data untuk pagination
                $sql_count = "SELECT COUNT(id_transaksi) AS total FROM transaksi WHERE deleted_at IS NULL AND (tanggal BETWEEN ? AND ?)";
                $stmt_count = mysqli_prepare($koneksi, $sql_count);
                mysqli_stmt_bind_param($stmt_count, "ss", $tgl_mulai, $tgl_selesai_end);
                mysqli_stmt_execute($stmt_count);
                $result_count = mysqli_stmt_get_result($stmt_count);
                $row_count = mysqli_fetch_assoc($result_count);
                $total_rows = $row_count['total'];
                $total_pages = ceil($total_rows / $limit);

                // Query utama dengan LIMIT dan OFFSET
                $sql = "SELECT 
                            transaksi.id_transaksi, 
                            transaksi.tanggal, 
                            transaksi.nomor_invoice, 
                            pengguna.username, 
                            metodepembayaran.nama_metode, 
                            transaksi.total_harga,
                            transaksi.status
                        FROM 
                            transaksi
                        JOIN 
                            pengguna ON transaksi.id_pengguna = pengguna.id_pengguna
                        JOIN 
                            metodepembayaran ON transaksi.id_metode = metodepembayaran.id_metode
                        WHERE 
                            transaksi.deleted_at IS NULL
                            AND (transaksi.tanggal BETWEEN ? AND ?)
                        ORDER BY 
                            transaksi.tanggal DESC
                        LIMIT ? OFFSET ?";
                
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "ssii", $tgl_mulai, $tgl_selesai_end, $limit, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        $is_canceled = $row['status'] === 'dibatalkan';
                ?>
                    <tr style="<?php echo $is_canceled ? 'opacity: 0.65;' : ''; ?>">
                        <td data-label="Tanggal"><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                        <td data-label="No. Invoice"><?php echo htmlspecialchars($row['nomor_invoice']); ?></td>
                        <td data-label="Kasir"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td data-label="Metode Bayar"><?php echo htmlspecialchars($row['nama_metode']); ?></td>
                        <td data-label="Total (Rp)"><?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        <td data-label="Status">
                            <?php if ($is_canceled): ?>
                                <span class="badge-danger" style="background: var(--danger-gradient); color: white; padding: 0.3rem 0.6rem; font-size: 0.75rem; border-radius: var(--radius-sm); font-weight: 700; box-shadow: 0 2px 6px rgba(244, 63, 94, 0.2);">Dibatalkan</span>
                            <?php else: ?>
                                <span class="badge-success" style="background: var(--success-gradient); color: white; padding: 0.3rem 0.6rem; font-size: 0.75rem; border-radius: var(--radius-sm); font-weight: 700; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.2);">Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Aksi">
                            <a href="laporan_detail.php?id=<?php echo $row['id_transaksi']; ?>" class="btn btn-primary btn-sm">Detail</a>
                            
                            <?php if (!$is_canceled): ?>
                                <a href="void_transaksi.php?id=<?php echo $row['id_transaksi']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Apakah Anda yakin ingin membatalkan/void transaksi <?php echo htmlspecialchars($row['nomor_invoice']); ?> ini? Kuantitas barang akan dikembalikan ke stok inventori.');">
                                   Batal
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>Tidak ada data transaksi pada periode "
                         . htmlspecialchars($tgl_mulai) . " s/d " . htmlspecialchars($tgl_selesai) . ".</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Navigasi Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <!-- Tombol Previous -->
            <a href="laporan_transaksi.php?tgl_mulai=<?php echo urlencode($tgl_mulai); ?>&tgl_selesai=<?php echo urlencode($tgl_selesai); ?>&page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>" 
               class="pagination-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"
               <?php echo ($page <= 1) ? 'onclick="return false;"' : ''; ?>>&laquo; Prev</a>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="laporan_transaksi.php?tgl_mulai=<?php echo urlencode($tgl_mulai); ?>&tgl_selesai=<?php echo urlencode($tgl_selesai); ?>&page=<?php echo $i; ?>" 
                   class="pagination-item <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <a href="laporan_transaksi.php?tgl_mulai=<?php echo urlencode($tgl_mulai); ?>&tgl_selesai=<?php echo urlencode($tgl_selesai); ?>&page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>" 
               class="pagination-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"
               <?php echo ($page >= $total_pages) ? 'onclick="return false;"' : ''; ?>>Next &raquo;</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'templates/footer.php';
?>