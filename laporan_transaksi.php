<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


$judul_halaman = "Laporan Penjualan";
include 'templates/header.php';
?>

<div class="card">
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
                // Query JOIN 3 tabel: transaksi, pengguna, metodepembayaran
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
                        ORDER BY 
                            transaksi.tanggal DESC
                        LIMIT 100"; // Tampilkan 100 transaksi terbaru
                
                $result = mysqli_query($koneksi, $sql);

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
                    echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data transaksi.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'templates/footer.php';
?>