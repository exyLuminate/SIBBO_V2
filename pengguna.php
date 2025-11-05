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
        <table class="table">
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
                // Query JOIN untuk mengambil nama peran
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
                            pengguna.nama_lengkap ASC";
                
                $result = mysqli_query($koneksi, $sql);

                if (mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $row['id_pengguna']; ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_peran']); ?></td>
                        <td>
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
    </div>
</div>

<?php
include 'templates/footer.php';
?>