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
    $_SESSION['error'] = "Transaksi tidak ditemukan.";
    header('Location: laporan_transaksi.php');
    exit;
}
$id_transaksi = $_GET['id'];

// --- Query 1: Ambil data 'kepala' transaksi ---
$sql_header = "SELECT 
                    transaksi.id_transaksi, 
                    transaksi.tanggal, 
                    transaksi.nomor_invoice, 
                    pengguna.username, 
                    metodepembayaran.nama_metode, 
                    transaksi.total_harga,
                    transaksi.jumlah_bayar,
                    transaksi.kembalian
                FROM 
                    transaksi
                JOIN 
                    pengguna ON transaksi.id_pengguna = pengguna.id_pengguna
                JOIN 
                    metodepembayaran ON transaksi.id_metode = metodepembayaran.id_metode
                WHERE 
                    transaksi.id_transaksi = ?";

$stmt_header = mysqli_prepare($koneksi, $sql_header);
mysqli_stmt_bind_param($stmt_header, "i", $id_transaksi);
mysqli_stmt_execute($stmt_header);
$result_header = mysqli_stmt_get_result($stmt_header);
$trx = mysqli_fetch_assoc($result_header);

if (!$trx) {
    $_SESSION['error'] = "Transaksi tidak valid.";
    header('Location: laporan_transaksi.php');
    exit;
}

$judul_halaman = "Detail Transaksi";
include 'templates/header.php';
?>

<div class="card print-invoice-card">
    <div class="card-header">
        <h3>Detail Invoice: <?php echo htmlspecialchars($trx['nomor_invoice']); ?></h3>
        <div class="no-print" style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Struk</button>
            <a href="laporan_transaksi.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <div class="card-body">
        <div class="info-transaksi">
            <p><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($trx['tanggal'])); ?></p>
            <p><strong>Kasir:</strong> <?php echo htmlspecialchars($trx['username']); ?></p>
            <p><strong>Metode Bayar:</strong> <?php echo htmlspecialchars($trx['nama_metode']); ?></p>
        </div>

        <hr>
        <h4>Item yang Dibeli:</h4>
        
        <table class="table table-laporan-detail">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Harga Satuan (Rp)</th>
                    <th>Jumlah</th>
                    <th>Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- Query 2: Ambil data 'isi' transaksi ---
                $sql_detail = "SELECT 
                                    barang.nama_barang,
                                    detailtransaksi.harga_saat_transaksi,
                                    detailtransaksi.jumlah,
                                    detailtransaksi.subtotal
                                FROM 
                                    detailtransaksi
                                JOIN 
                                    barang ON detailtransaksi.id_barang = barang.id_barang
                                WHERE 
                                    detailtransaksi.id_transaksi = ?
                                ORDER BY 
                                    barang.nama_barang ASC";
                
                $stmt_detail = mysqli_prepare($koneksi, $sql_detail);
                mysqli_stmt_bind_param($stmt_detail, "i", $id_transaksi);
                mysqli_stmt_execute($stmt_detail);
                $result_detail = mysqli_stmt_get_result($stmt_detail);

                while($item = mysqli_fetch_assoc($result_detail)):
                ?>
                    <tr>
                        <td data-label="Nama Barang">
                            <div class="laporan-detail-info">
                                <span class="laporan-detail-nama"><?php echo htmlspecialchars($item['nama_barang']); ?></span>
                                <span class="laporan-detail-meta">
                                    <small><?php echo number_format($item['harga_saat_transaksi'], 0, ',', '.'); ?> x <?php echo $item['jumlah']; ?></small>
                                    <span class="laporan-detail-subtotal-mobile">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                                </span>
                            </div>
                        </td>
                        <td data-label="Harga Satuan (Rp)"><?php echo number_format($item['harga_saat_transaksi'], 0, ',', '.'); ?></td>
                        <td data-label="Jumlah"><?php echo $item['jumlah']; ?></td>
                        <td data-label="Subtotal (Rp)"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <hr>
        
        <div class="info-pembayaran">
            <p><strong>Total Harga:</strong> Rp <?php echo number_format($trx['total_harga'], 0, ',', '.'); ?></p>
            <p><strong>Jumlah Bayar:</strong> Rp <?php echo number_format($trx['jumlah_bayar'], 0, ',', '.'); ?></p>
            <p><strong>Kembalian:</strong> Rp <?php echo number_format($trx['kembalian'], 0, ',', '.'); ?></p>
        </div>
        
    </div>
</div>

<?php
include 'templates/footer.php';
?>