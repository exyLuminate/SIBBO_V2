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
                
                <div class="form-group" style="margin-bottom: 0; margin-top: 1rem; width: 100%;">
                    <input type="text" id="searchInput" class="form-control-search" placeholder="Ketik untuk mencari nama barang..." autocomplete="off">
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
                                    <div class="quantity-control">
                                        <a href="keranjang_aksi.php?action=kurang&id=<?php echo $id_barang; ?>" class="btn-qty">-</a>
                                        <span class="qty-val"><?php echo $item['jumlah']; ?></span>
                                        <a href="keranjang_aksi.php?action=tambah&id=<?php echo $id_barang; ?>" class="btn-qty">+</a>
                                    </div>
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
                <div style="margin-top: 1.25rem; margin-bottom: 1.25rem;">
                    <a href="keranjang_aksi.php?action=kosongkan" class="btn btn-danger btn-sm" onclick="return confirm('Kosongkan keranjang?')">
                        Kosongkan Keranjang
                    </a>
                </div>
                <?php endif; ?>

                <hr style="border: 0; border-top: 1px solid rgba(226, 232, 240, 0.8); margin: 1.5rem 0;">

                <form action="proses_transaksi.php" method="POST" id="form-pembayaran">
                    <div class="total-belanja-container">
                        <span class="total-belanja-label">Total Belanja:</span>
                        <h1 id="total-belanja">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></h1>
                    </div>
                    
                    <input type="hidden" name="total_harga" value="<?php echo $total_harga; ?>">

                    <div class="form-group">
                        <label for="id_metode">Metode Pembayaran</label>
                        <select id="id_metode" name="id_metode" required>
                            <option value="">-- Pilih Metode --</option>
                            <?php
                            mysqli_data_seek($metode_result, 0); // Reset pointer
                            while($metode = mysqli_fetch_assoc($metode_result)) {
                                echo "<option value='{$metode['id_metode']}' data-name='" . htmlspecialchars($metode['nama_metode']) . "'>" . htmlspecialchars($metode['nama_metode']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group" id="group_jumlah_bayar">
                        <label for="jumlah_bayar">Jumlah Bayar (Rp)</label>
                        <input type="number" id="jumlah_bayar" name="jumlah_bayar" required min="<?php echo $total_harga; ?>">
                    </div>

                    <div class="form-group" id="group_kembalian">
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
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen input dan tabel
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('daftarBarangTbody');
    const rows = tableBody ? tableBody.getElementsByTagName('tr') : null;

    // Tambahkan event listener 'keyup' (setiap kali tombol dilepas)
    if (searchInput && rows) {
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
    }

    // Hitung Kembalian secara Real-time & Handle State untuk QRIS
    const jumlahBayarInput = document.getElementById('jumlah_bayar');
    const kembalianEl = document.getElementById('kembalian');
    const totalHarga = <?php echo $total_harga; ?>;
    const selectMetode = document.getElementById('id_metode');

    if (jumlahBayarInput && kembalianEl) {
        jumlahBayarInput.addEventListener('input', function() {
            const bayar = parseFloat(jumlahBayarInput.value) || 0;
            const kembalian = Math.max(0, bayar - totalHarga);
            kembalianEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(kembalian);
        });
    }

    const groupJumlahBayar = document.getElementById('group_jumlah_bayar');
    const groupKembalian = document.getElementById('group_kembalian');

    if (selectMetode && jumlahBayarInput && kembalianEl) {
        selectMetode.addEventListener('change', function() {
            const selectedOpt = selectMetode.options[selectMetode.selectedIndex];
            const isQris = selectedOpt ? selectedOpt.getAttribute('data-name') === 'QRIS' : false;
            
            if (isQris) {
                jumlahBayarInput.value = totalHarga;
                
                // Sembunyikan form input Jumlah Bayar dan Kembalian untuk QRIS
                if (groupJumlahBayar) groupJumlahBayar.style.display = 'none';
                if (groupKembalian) groupKembalian.style.display = 'none';
                
                kembalianEl.textContent = 'Rp 0';
            } else {
                // Tampilkan kembali form input Jumlah Bayar dan Kembalian untuk Tunai
                if (groupJumlahBayar) groupJumlahBayar.style.display = '';
                if (groupKembalian) groupKembalian.style.display = '';
                
                jumlahBayarInput.value = '';
                kembalianEl.textContent = 'Rp 0';
            }
        });
    }

    // Handler untuk QRIS Modal Scan dengan QR Code Asli yang bisa di-scan
    const formPembayaran = document.getElementById('form-pembayaran');
    const qrisModal = document.getElementById('qrisModal');
    const closeQris = document.getElementById('closeQris');
    const cancelQris = document.getElementById('cancelQris');
    const confirmQris = document.getElementById('confirmQris');
    const qrisAmountText = document.getElementById('qrisAmountText');
    const qrisQrImg = document.getElementById('qrisQrImg');
    const qrisQrSvgFallback = document.getElementById('qrisQrSvgFallback');

    // Generator QRIS EMVCo Standar Nasional (Scannable oleh DANA, GoPay, OVO, dll.)
    function generateQrisString(amount) {
        // Helper to construct Tag-Length-Value
        function makeTlv(tag, value) {
            const len = value.length.toString().padStart(2, '0');
            return tag + len + value;
        }

        // Tag 00: Payload Format Indicator (Value "01")
        const pfi = makeTlv("00", "01");
        
        // Tag 01: Point of Initiation Method (Value "12" for Dynamic QR with amount)
        const poi = makeTlv("01", "12");
        
        // Tag 26: Merchant Account Information
        const guid = makeTlv("00", "ID.CO.QRIS.WWW");
        const merchantPan = makeTlv("01", "936009110000000000");
        const criteria = makeTlv("03", "U00");
        const merchantInfo = makeTlv("26", guid + merchantPan + criteria);
        
        // Tag 52: Merchant Category Code (Value "5411" for Supermarket/Grocery)
        const mcc = makeTlv("52", "5411");
        
        // Tag 53: Transaction Currency (Value "360" for IDR)
        const currency = makeTlv("53", "360");
        
        // Tag 54: Transaction Amount
        const transactionAmount = makeTlv("54", amount.toString());
        
        // Tag 58: Country Code (Value "ID")
        const country = makeTlv("58", "ID");
        
        // Tag 59: Merchant Name
        const merchantName = makeTlv("59", "SIBBO POS MOCK");
        
        // Tag 60: Merchant City
        const merchantCity = makeTlv("60", "JAKARTA");
        
        // Combine all tags, followed by Tag 63 (CRC) with length "04"
        const payloadWithoutCrc = pfi + poi + merchantInfo + mcc + currency + transactionAmount + country + merchantName + merchantCity + "6304";
        
        // Compute CRC-16/CCITT-FALSE Checksum
        let crc = 0xFFFF;
        for (let c = 0; c < payloadWithoutCrc.length; c++) {
            crc ^= payloadWithoutCrc.charCodeAt(c) << 8;
            for (let i = 0; i < 8; i++) {
                if (crc & 0x8000) {
                    crc = ((crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    crc = (crc << 1) & 0xFFFF;
                }
            }
        }
        const crcHex = crc.toString(16).toUpperCase().padStart(4, '0');
        
        return payloadWithoutCrc + crcHex;
    }

    if (formPembayaran && qrisModal && selectMetode && qrisAmountText) {
        formPembayaran.addEventListener('submit', function(e) {
            const selectedOpt = selectMetode.options[selectMetode.selectedIndex];
            const isQris = selectedOpt ? selectedOpt.getAttribute('data-name') === 'QRIS' : false;
            
            if (isQris) {
                e.preventDefault(); // Cegah submit langsung
                
                // Format nominal tagihan di modal
                const formatPrice = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga);
                qrisAmountText.textContent = formatPrice;
                
                // Hasilkan payload QRIS scannable EMVCo
                const qrData = generateQrisString(totalHarga);
                const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrData)}`;
                
                // Muat QR Code dari API
                if (qrisQrImg && qrisQrSvgFallback) {
                    qrisQrImg.onload = function() {
                        qrisQrImg.style.display = 'block';
                        qrisQrSvgFallback.style.display = 'none';
                    };
                    qrisQrImg.onerror = function() {
                        qrisQrImg.style.display = 'none';
                        qrisQrSvgFallback.style.display = 'block';
                    };
                    qrisQrImg.src = qrApiUrl;
                }
                
                // Tampilkan modal QRIS
                qrisModal.classList.add('active');
            }
        });
        
        // Tombol close modal
        closeQris.addEventListener('click', function() {
            qrisModal.classList.remove('active');
        });
        cancelQris.addEventListener('click', function() {
            qrisModal.classList.remove('active');
        });
        
        // Tombol konfirmasi bayar di modal
        confirmQris.addEventListener('click', function() {
            qrisModal.classList.remove('active');
            formPembayaran.submit(); // Submit form asli
        });
    }
});
</script>

<!-- QRIS Payment Modal -->
<div class="qris-modal" id="qrisModal">
    <div class="qris-modal-content">
        <div class="qris-header">
            <span class="qris-title">PEMBAYARAN QRIS</span>
            <button type="button" class="qris-close-btn" id="closeQris">&times;</button>
        </div>
        <div class="qris-body">
            <div class="qris-logo-container">
                <span class="qris-logo-text">QRIS</span>
                <span class="qris-gpn">GPN</span>
            </div>
            
            <div class="qris-amount-info">
                <p>Total Tagihan</p>
                <h2 id="qrisAmountText">Rp 0</h2>
            </div>
            
            <div class="qris-qr-container">
                <div class="qris-scanner-line"></div>
                <!-- Real Scannable QR Code Image -->
                <img id="qrisQrImg" src="" alt="Scan QRIS" width="200" height="200" style="display: none; border-radius: 4px;">
                <!-- Inline SVG QR Code Mockup as fallback -->
                <svg id="qrisQrSvgFallback" width="200" height="200" viewBox="0 0 100 100" class="qris-svg">
                    <rect width="100" height="100" fill="white"/>
                    <rect x="5" y="5" width="25" height="25" fill="black"/>
                    <rect x="9" y="9" width="17" height="17" fill="white"/>
                    <rect x="13" y="13" width="9" height="9" fill="black"/>
                    
                    <rect x="70" y="5" width="25" height="25" fill="black"/>
                    <rect x="74" y="9" width="17" height="17" fill="white"/>
                    <rect x="78" y="13" width="9" height="9" fill="black"/>
                    
                    <rect x="5" y="70" width="25" height="25" fill="black"/>
                    <rect x="9" y="74" width="17" height="17" fill="white"/>
                    <rect x="13" y="78" width="9" height="9" fill="black"/>
                    
                    <rect x="75" y="75" width="10" height="10" fill="black"/>
                    <rect x="77" y="77" width="6" height="6" fill="white"/>
                    <rect x="79" y="79" width="2" height="2" fill="black"/>
                    
                    <rect x="35" y="5" width="5" height="5" fill="black"/>
                    <rect x="45" y="10" width="10" height="5" fill="black"/>
                    <rect x="35" y="20" width="5" height="10" fill="black"/>
                    <rect x="55" y="15" width="10" height="5" fill="black"/>
                    <rect x="5" y="35" width="15" height="5" fill="black"/>
                    <rect x="25" y="35" width="5" height="15" fill="black"/>
                    <rect x="15" y="45" width="5" height="5" fill="black"/>
                    <rect x="35" y="35" width="10" height="10" fill="black"/>
                    <rect x="50" y="35" width="5" height="5" fill="black"/>
                    <rect x="60" y="35" width="10" height="5" fill="black"/>
                    <rect x="5" y="55" width="5" height="5" fill="black"/>
                    <rect x="15" y="55" width="10" height="10" fill="black"/>
                    <rect x="35" y="50" width="5" height="15" fill="black"/>
                    <rect x="45" y="55" width="15" height="5" fill="black"/>
                    <rect x="70" y="35" width="5" height="15" fill="black"/>
                    <rect x="85" y="35" width="10" height="5" fill="black"/>
                    <rect x="80" y="45" width="5" height="15" fill="black"/>
                    <rect x="70" y="55" width="10" height="10" fill="black"/>
                    <rect x="85" y="60" width="5" height="5" fill="black"/>
                    <rect x="35" y="75" width="15" height="5" fill="black"/>
                    <rect x="35" y="85" width="5" height="10" fill="black"/>
                    <rect x="45" y="80" width="10" height="5" fill="black"/>
                    <rect x="60" y="75" width="5" height="15" fill="black"/>
                    <rect x="55" y="90" width="15" height="5" fill="black"/>
                </svg>
            </div>
            
            <p class="qris-instruction">Scan barcode QRIS di atas untuk menyelesaikan pembayaran.</p>
        </div>
        <div class="qris-footer">
            <button type="button" class="btn btn-secondary" id="cancelQris">Batal</button>
            <button type="button" class="btn btn-primary" id="confirmQris">Konfirmasi Bayar</button>
        </div>
    </div>
</div>

<?php
include 'templates/footer.php';
?>