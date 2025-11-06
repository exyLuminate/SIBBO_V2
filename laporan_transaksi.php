<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


// [MODIFIKASI 1] Ambil tanggal dari URL, jika tidak ada, pakai tanggal hari ini
$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-d');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');


$judul_halaman = "Laporan Penjualan";
include 'templates/header.php';
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
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>No. Invoice</th>
                    <th>Kasir</th>
                    <th>Metode Bayar</th>
                    <th>Total (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // [MODIFIKASI 3] Ubah Query SQL untuk menangani filter tanggal
                
                // Penting: tambahkan jam 23:59:59 ke tanggal selesai
                // agar transaksi di tanggal itu ikut terambil
                $tgl_selesai_end = $tgl_selesai . ' 23:59:59';
                
                $sql = "SELECT 
                            transaksi.id_transaksi, 
                            transaksi.tanggal, 
                            transaksi.nomor_invoice, 
                            pengguna.username, 
                            metodepembayaran.nama_metode, 
                            transaksi.total_harga
                        FROM 
                            transaksi
                        JOIN 
                            pengguna ON transaksi.id_pengguna = pengguna.id_pengguna
                        JOIN 
                            metodepembayaran ON transaksi.id_metode = metodepembayaran.id_metode
                        WHERE 
                            transaksi.status = 'selesai' AND transaksi.deleted_at IS NULL
                            AND (transaksi.tanggal BETWEEN ? AND ?) -- Tambah filter tanggal
                        ORDER BY 
                            transaksi.tanggal DESC";
                
                // Hapus LIMIT 100 agar semua hasil filter tampil
                
                $stmt = mysqli_prepare($koneksi, $sql);
                // Bind 2 parameter string (s, s)
                mysqli_stmt_bind_param($stmt, "ss", $tgl_mulai, $tgl_selesai_end);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nomor_invoice']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_metode']); ?></td>
                        <td><?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="laporan_detail.php?id=<?php echo $row['id_transaksi']; ?>" class="btn btn-primary btn-sm">Detail</a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    // [MODIFIKASI 4] Tampilkan pesan yang lebih relevan
                    echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada data transaksi pada periode "
                         . htmlspecialchars($tgl_mulai) . " s/d " . htmlspecialchars($tgl_selesai) . ".</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'templates/footer.php';
?>