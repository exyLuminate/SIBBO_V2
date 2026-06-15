<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-shopping-bag"></i>
            <span>SIBBO</span>
        </div>
    </div>
    <ul class="nav-menu">
        <li class="<?php echo ($current_page == 'kasir.php') ? 'active' : ''; ?>">
            <a href="kasir.php">
                <i class="fas fa-cash-register nav-icon"></i>
                <span>Kasir</span>
            </a>
        </li>
        
        <?php
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'Admin') :
        ?>
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-chart-line nav-icon"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo in_array($current_page, ['barang.php', 'barang_tambah.php', 'barang_edit.php']) ? 'active' : ''; ?>">
                <a href="barang.php">
                    <i class="fas fa-box nav-icon"></i>
                    <span>Manajemen Barang</span>
                </a>
            </li>
            <li class="<?php echo in_array($current_page, ['kategori.php', 'kategori_edit.php']) ? 'active' : ''; ?>">
                <a href="kategori.php">
                    <i class="fas fa-tags nav-icon"></i>
                    <span>Manajemen Kategori</span>
                </a>
            </li>
            <li class="<?php echo ($current_page == 'stok_masuk.php') ? 'active' : ''; ?>">
                <a href="stok_masuk.php">
                    <i class="fas fa-cubes nav-icon"></i>
                    <span>Manajemen Stok</span>
                </a>
            </li>
            <li class="<?php echo in_array($current_page, ['pengguna.php', 'pengguna_tambah.php', 'pengguna_edit.php']) ? 'active' : ''; ?>">
                <a href="pengguna.php">
                    <i class="fas fa-users nav-icon"></i>
                    <span>Manajemen Pengguna</span>
                </a>
            </li>
            <li class="<?php echo in_array($current_page, ['laporan_transaksi.php', 'laporan_detail.php']) ? 'active' : ''; ?>">
                <a href="laporan_transaksi.php">
                    <i class="fas fa-file-invoice-dollar nav-icon"></i>
                    <span>Laporan Penjualan</span>
                </a>
            </li>
        <?php 
        endif; 
        ?>
        
        <li class="logout-item">
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt nav-icon"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>