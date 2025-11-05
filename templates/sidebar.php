<nav class="sidebar">
    <div class="sidebar-header">
        <h3>SIBBO POS</h3>
    </div>
    <ul class="nav-menu">
        <li>
            <a href="kasir.php">Kasir</a>
        </li>
        
        <?php
        // --- INI LOGIKA PENTINGNYA ---
        // Tampilkan menu ini HANYA jika role-nya 'Admin'
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'Admin') :
        ?>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="barang.php">Manajemen Barang</a></li>
            <li><a href="kategori.php">Manajemen Kategori</a></li>
            <li><a href="stok_masuk.php">Manajemen Stok</a></li>
            <li><a href="pengguna.php">Manajemen Pengguna</a></li>
            <li><a href="laporan_transaksi.php">Laporan Penjualan</a></li>
        <?php 
        endif; 
        // --- BATAS AKHIR LOGIKA ADMIN ---
        ?>
        
        <li>
            <a href="logout.php" class="logout-link">Logout</a>
        </li>
    </ul>
</nav>