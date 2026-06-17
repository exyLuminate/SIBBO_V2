<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: kasir.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


// ==========================================================
// --- LOGIKA PENGAMBILAN DATA DASHBOARD ---
// ==========================================================

$hari_ini = date('Y-m-d');

// 1. STATISTIK HARI INI (Kartu Stat)
$sql_today = "SELECT 
                COALESCE(SUM(total_harga), 0) AS total_penjualan, 
                COUNT(id_transaksi) AS jumlah_transaksi 
              FROM transaksi 
              WHERE 
                DATE(tanggal) = ? AND status = 'selesai'";
$stmt_today = mysqli_prepare($koneksi, $sql_today);
mysqli_stmt_bind_param($stmt_today, "s", $hari_ini);
mysqli_stmt_execute($stmt_today);
$result_today = mysqli_stmt_get_result($stmt_today);
$stats_today = mysqli_fetch_assoc($result_today);


// 2. STATISTIK TOTAL (Kartu Stat)
$sql_total_barang = "SELECT COUNT(id_barang) AS total_barang FROM barang WHERE deleted_at IS NULL";
$result_total_barang = mysqli_query($koneksi, $sql_total_barang);
$stats_total_barang = mysqli_fetch_assoc($result_total_barang);

$sql_total_stok = "SELECT SUM(stok) AS total_stok FROM barang WHERE deleted_at IS NULL";
$result_total_stok = mysqli_query($koneksi, $sql_total_stok);
$stats_total_stok = mysqli_fetch_assoc($result_total_stok);


// 3. DATA UNTUK CHART (Barang Terlaris)
$sql_top_produk = "SELECT 
                        barang.nama_barang, 
                        SUM(detailtransaksi.jumlah) AS total_terjual 
                    FROM 
                        detailtransaksi
                    JOIN 
                        barang ON detailtransaksi.id_barang = barang.id_barang
                    JOIN 
                        transaksi ON detailtransaksi.id_transaksi = transaksi.id_transaksi
                    WHERE 
                        transaksi.status = 'selesai'
                    GROUP BY 
                        barang.nama_barang 
                    ORDER BY 
                        total_terjual DESC 
                    LIMIT 5"; // Ambil 5 barang terlaris

$result_top_produk = mysqli_query($koneksi, $sql_top_produk);
$top_products = [];
while($row = mysqli_fetch_assoc($result_top_produk)) {
    $top_products[] = $row;
}

// Siapkan data untuk Chart.js
// Ubah array PHP menjadi format JSON yang bisa dibaca JavaScript
$labels_chart = json_encode(array_column($top_products, 'nama_barang'));
$data_chart = json_encode(array_column($top_products, 'total_terjual'));


// ==========================================================
// --- MULAI TAMPILAN HTML ---
// ==========================================================
$judul_halaman = "Dashboard";
include 'templates/header.php';
?>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-info">
            <h3>Penjualan Hari Ini</h3>
            <p>Rp <?php echo number_format($stats_today['total_penjualan'], 0, ',', '.'); ?></p>
        </div>
        <div class="stat-card-icon">
            <i class="fas fa-wallet"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <h3>Transaksi Hari Ini</h3>
            <p><?php echo $stats_today['jumlah_transaksi']; ?> Transaksi</p>
        </div>
        <div class="stat-card-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <h3>Total Jenis Barang</h3>
            <p><?php echo $stats_total_barang['total_barang']; ?> Item</p>
        </div>
        <div class="stat-card-icon">
            <i class="fas fa-box"></i>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-info">
            <h3>Total Stok Tersedia</h3>
            <p><?php echo $stats_total_stok['total_stok'] ?? 0; ?> Unit</p>
        </div>
        <div class="stat-card-icon">
            <i class="fas fa-cubes"></i>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>5 Barang Terlaris (Berdasarkan Total Penjualan)</h3>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>


<?php
// Kita akan meletakkan script Chart.js di sini, sebelum footer
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Ambil elemen <canvas> dan konteks 2D untuk membuat gradient
    const canvas = document.getElementById('myChart');
    const ctx = canvas.getContext('2d');
    
    // Ambil data dari PHP
    const labels = <?php echo $labels_chart; ?>;
    const data = <?php echo $data_chart; ?>;

    // Buat gradient background
    const bgGradient = ctx.createLinearGradient(0, 0, 0, 300);
    bgGradient.addColorStop(0, 'rgba(99, 102, 241, 0.45)');  // Indigo
    bgGradient.addColorStop(1, 'rgba(139, 92, 246, 0.05)'); // Violet fading out

    // Buat gradient border
    const borderGradient = ctx.createLinearGradient(0, 0, 0, 300);
    borderGradient.addColorStop(0, 'rgba(99, 102, 241, 1)');
    borderGradient.addColorStop(1, 'rgba(139, 92, 246, 1)');

    // Buat chart baru
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Terjual (unit)',
                data: data,
                backgroundColor: bgGradient,
                borderColor: borderGradient,
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(99, 102, 241, 0.6)',
                hoverBorderColor: 'rgba(99, 102, 241, 1)'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)',
                        drawTicks: false
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            weight: '500'
                        },
                        color: '#64748b'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            weight: '600'
                        },
                        color: '#64748b'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            family: 'Plus Jakarta Sans',
                            weight: '700',
                            size: 13
                        },
                        color: '#0f172a'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: {
                        family: 'Plus Jakarta Sans',
                        weight: '700'
                    },
                    bodyFont: {
                        family: 'Plus Jakarta Sans'
                    },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: false
                }
            },
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 1200,
                easing: 'easeOutQuart'
            }
        }
    });
</script>


<?php
include 'templates/footer.php';
?>