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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <?php include 'templates/sidebar.php'; ?>

    <div class="main-content">
        <header>
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h2><?php echo $judul_halaman; ?></h2>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                </div>
            </div>
        </header>
        
        <main class="page-animate">