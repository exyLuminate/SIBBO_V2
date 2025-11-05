<?php
// session_start(); 
// Tidak perlu session_start() di sini, karena semua halaman yang 
// memanggil header.php (spt dashboard.php) SUDAH memanggil session_start() 
// di baris paling atas untuk "Penjaga Sesi".
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman; ?> - SIBBO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <?php include 'templates/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <h2><?php echo $judul_halaman; ?></h2>
            <div class="user-info">
                Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)
            </div>
        </header>
        
        <main></main>