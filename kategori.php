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


$judul_halaman = "Manajemen Kategori";
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
        <h3>Tambah Kategori Baru</h3>
    </div>
    <div class="card-body">
        <form action="kategori_proses.php" method="POST">
            <input type="hidden" name="action" value="tambah"> 
            
            <div class="form-group">
                <label for="nama_kategori">Nama Kategori</label>
                <input type="text" id="nama_kategori" name="nama_kategori" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h3>Daftar Kategori</h3>
    </div>
    <div class="card-body">
    
     <form action="kategori.php" method="GET" class="search-form">
    <input type="text" name="search" 
           placeholder="Cari Nama Kategori..." 
           value="<?php echo htmlspecialchars($search); ?>"
           class="form-control-search"> <button type="submit" class="btn btn-primary">Cari</button>
    <?php if (!empty($search)): ?>
        <a href="kategori.php" class="btn btn-secondary">Reset</a>
    <?php endif; ?>
</form>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // [MODIFIKASI 3] Ubah Query SQL untuk menangani pencarian
                
                $sql = "SELECT id_kategori, nama_kategori FROM kategori WHERE deleted_at IS NULL";

                // Tambahkan kondisi WHERE jika ada pencarian
                if (!empty($search)) {
                    $sql .= " AND (nama_kategori LIKE ?)";
                }

                $sql .= " ORDER BY nama_kategori ASC";

                $stmt = mysqli_prepare($koneksi, $sql);

                if (!empty($search)) {
                    // Bind 1 parameter string ("s")
                    mysqli_stmt_bind_param($stmt, "s", $search_param);
                }

                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id_kategori']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_kategori']); ?></td>
                        <td>
                            <a href="kategori_edit.php?id=<?php echo $row['id_kategori']; ?>" class="btn btn-warning">Edit</a>
                            
                            <a href="kategori_proses.php?action=hapus&id=<?php echo $row['id_kategori']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Anda yakin ingin menghapus kategori ini?');">
                               Hapus
                            </a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    // [MODIFIKASI 4] Tampilkan pesan yang lebih relevan
                    if (!empty($search)) {
                        echo "<tr><td colspan='3' style='text-align:center;'>Kategori tidak ditemukan untuk: '" . htmlspecialchars($search) . "'</td></tr>";
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>Belum ada data kategori.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


<?php
include 'templates/footer.php';
?>