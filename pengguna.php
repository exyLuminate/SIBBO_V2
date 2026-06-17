<?php
session_start();
include 'koneksi.php';

// --- PENJAGA HALAMAN ADMIN ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'Admin') {
    header('Location: login.php');
    exit;
}
// --- BATAS AKHIR PENJAGA HALAMAN ---


$judul_halaman = "Manajemen Pengguna";
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
        <h3>Daftar Pengguna</h3>
        <a href="pengguna_tambah.php" class="btn btn-primary">Tambah Pengguna Baru</a>
    </div>
    <div class="card-body">
        <table class="table table-pengguna">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Peran (Role)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Konfigurasi pagination
                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($page < 1) $page = 1;
                $offset = ($page - 1) * $limit;

                // Ambil total data untuk pagination
                $sql_count = "SELECT COUNT(id_pengguna) AS total FROM pengguna WHERE deleted_at IS NULL";
                $res_count = mysqli_query($koneksi, $sql_count);
                $count_data = mysqli_fetch_assoc($res_count);
                $total_rows = $count_data['total'];
                $total_pages = ceil($total_rows / $limit);

                // Query JOIN untuk mengambil nama peran dengan LIMIT & OFFSET
                $sql = "SELECT 
                            pengguna.id_pengguna, 
                            pengguna.nama_lengkap, 
                            pengguna.username, 
                            peran.nama_peran
                        FROM 
                            pengguna 
                        JOIN 
                            peran ON pengguna.id_peran = peran.id_peran 
                        WHERE 
                            pengguna.deleted_at IS NULL 
                        ORDER BY 
                            pengguna.nama_lengkap ASC LIMIT ? OFFSET ?";
                
                $stmt = mysqli_prepare($koneksi, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td data-label="ID"><?php echo $row['id_pengguna']; ?></td>
                        <td data-label="Nama Lengkap"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td data-label="Username"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td data-label="Peran (Role)"><?php echo htmlspecialchars($row['nama_peran']); ?></td>
                        <td data-label="Aksi">
                            <a href="pengguna_edit.php?id=<?php echo $row['id_pengguna']; ?>" class="btn btn-warning">Edit</a>
                            
                            <?php
                            // PENTING: Jangan biarkan admin menghapus dirinya sendiri!
                            if ($row['id_pengguna'] != $_SESSION['user_id']) : 
                            ?>
                                <a href="pengguna_proses.php?action=hapus&id=<?php echo $row['id_pengguna']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Anda yakin ingin menghapus pengguna ini?');">
                                   Hapus
                                </a>
                            <?php else: ?>
                                <button class="btn" disabled>Hapus</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data pengguna.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Navigasi Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <!-- Tombol Previous -->
            <a href="pengguna.php?page=<?php echo ($page > 1) ? ($page - 1) : 1; ?>" 
               class="pagination-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>"
               <?php echo ($page <= 1) ? 'onclick="return false;"' : ''; ?>>&laquo; Prev</a>

            <!-- Nomor Halaman -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="pengguna.php?page=<?php echo $i; ?>" 
                   class="pagination-item <?php echo ($page == $i) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <a href="pengguna.php?page=<?php echo ($page < $total_pages) ? ($page + 1) : $total_pages; ?>" 
               class="pagination-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>"
               <?php echo ($page >= $total_pages) ? 'onclick="return false;"' : ''; ?>>Next &raquo;</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
include 'templates/footer.php';
?>