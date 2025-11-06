<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ---
// Cek apakah sudah login (Admin dan Kasir boleh akses ini)
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---

// Ambil data metode pembayaran untuk dropdown
$metode_sql = "SELECT id_metode, nama_metode FROM metodepembayaran WHERE deleted_at IS NULL ORDER BY nama_metode ASC";
$metode_result = mysqli_query($koneksi, $metode_sql);

// Ambil data barang untuk ditampilkan
$barang_sql = "SELECT id_barang, nama_barang, harga_jual, stok FROM barang WHERE deleted_at IS NULL ORDER BY nama_barang ASC";
$barang_result = mysqli_query($koneksi, $barang_sql);


$judul_halaman = "Halaman Kasir";
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

<div class="kasir-container">

    <div class="daftar-barang">
        <div class="card">
            <div class="card-header">
                <h3>Daftar Barang</h3>
                
                <div class="form-group" style="margin-bottom: 0; margin-top: 1rem;">
                    <input type="text" id="searchInput" placeholder="Ketik untuk mencari nama barang..." 
                           style="width: 100%; padding: 0.5rem;" autocomplete="off">
                </div>
                
            </div>
            <div class="card-body">
                <table class="table table-stripe">
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Harga (Rp)</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    
                    <tbody id="daftarBarangTbody"> 
                        <?php while($barang = mysqli_fetch_assoc($barang_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($barang['nama_barang']); ?></td>
                            <td><?php echo number_format($barang['harga_jual'], 0, ',', '.'); ?></td>
                            <td>
                                <?php if($barang['stok'] <= 0): ?>
                                    <span class="badge-danger">Habis</span>
                                <?php else: ?>
                                    <?php echo $barang['stok']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($barang['stok'] > 0): ?>
                                    <a href="keranjang_aksi.php?action=tambah&id=<?php echo $barang['id_barang']; ?>" class="btn btn-primary btn-sm">+</a>
                                <?php else: ?>
                                    <button class="btn btn-sm" disabled>Habis</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="keranjang">
        <div class="card">
            <div class="card-header">
                <h3>Keranjang Belanja</h3>
            </div>
            <div class="card-body">
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Jml</th>
                            <th>Subtotal (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $keranjang = $_SESSION['keranjang'] ?? [];
                        $total_harga = 0;
                        if (!empty($keranjang)):
                            foreach ($keranjang as $id_barang => $item):
                                $subtotal = $item['harga'] * $item['jumlah'];
                                $total_harga += $subtotal;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nama']); ?><br>
                                    <small><?php echo number_format($item['harga'], 0, ',', '.'); ?></small>
                                </td>
                                <td>
                                    <a href="keranjang_aksi.php?action=kurang&id=<?php echo $id_barang; ?>" class="btn-qty">-</a>
                                    <?php echo $item['jumlah']; ?>
                                    <a href="keranjang_aksi.php?action=tambah&id=<?php echo $id_barang; ?>" class="btn-qty">+</a>
                                </td>
                                <td><?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                <td>
                                    <a href="keranjang_aksi.php?action=hapus&id=<?php echo $id_barang; ?>" class="btn-qty-danger">x</a>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        else:
                            echo "<tr><td colspan='4' style='text-align:center;'>Keranjang kosong.</td></tr>";
                        endif;
                        ?>
                    </tbody>
                </table>

                <?php if (!empty($keranjang)): ?>
                <a href="keranjang_aksi.php?action=kosongkan" class="btn btn-danger btn-sm" style="margin-top: 10px;" onclick="return confirm('Kosongkan keranjang?')">
                    Kosongkan Keranjang
                </a>
                <?php endif; ?>

                <hr>

                <form action="proses_transaksi.php" method="POST" id="form-pembayaran">
                    <h4>Total Belanja:</h4>
                    <h1 id="total-belanja">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></h1>
                    
                    <input type="hidden" name="total_harga" value="<?php echo $total_harga; ?>">

                    <div class="form-group">
                        <label for="id_metode">Metode Pembayaran</label>
                        <select id="id_metode" name="id_metode" required>
                            <option value="">-- Pilih Metode --</option>
                            <?php
                            mysqli_data_seek($metode_result, 0); // Reset pointer
                            while($metode = mysqli_fetch_assoc($metode_result)) {
                                echo "<option value='{$metode['id_metode']}'>" . htmlspecialchars($metode['nama_metode']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="jumlah_bayar">Jumlah Bayar (Rp)</label>
                        <input type="number" id="jumlah_bayar" name="jumlah_bayar" required min="<?php echo $total_harga; ?>">
                    </div>

                    <div class="form-group">
                        <label>Kembalian (Rp)</label>
                        <h3 id="kembalian">Rp 0</h3>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;" 
                            <?php echo empty($keranjang) ? 'disabled' : ''; ?>>
                        PROSES BAYAR
                    </button>
                    
                </form>

            </div>
        </div>
    </div>

</div>

<script>
// Ambil elemen input dan tabel
const searchInput = document.getElementById('searchInput');
const tableBody = document.getElementById('daftarBarangTbody');
const rows = tableBody.getElementsByTagName('tr');

// Tambahkan event listener 'keyup' (setiap kali tombol dilepas)
searchInput.addEventListener('keyup', function() {
    const filter = searchInput.value.toLowerCase(); // Ambil teks pencarian, ubah jadi huruf kecil

    // Loop semua baris tabel (tr)
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        // Ambil semua sel (td) di dalam baris
        const cells = row.getElementsByTagName('td');
        
        // Ambil teks dari kolom pertama (Nama Barang [indeks 0])
        const namaBarang = cells[0].textContent || cells[0].innerText;
        
        // Cek apakah nama barang mengandung teks pencarian
        if (namaBarang.toLowerCase().indexOf(filter) > -1) {
            row.style.display = ""; // Tampilkan baris
        } else {
            row.style.display = "none"; // Sembunyikan baris
        }
    }
});
</script>


<?php
include 'templates/footer.php';
?>