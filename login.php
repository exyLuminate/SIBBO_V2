<?php
session_start();
// Jika sudah login, tendang ke index
if (isset($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIBBO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-decorations">
        <div class="login-bg-circle"></div>
    </div>
    <div class="login-container">
        <form action="proses_login.php" method="POST">
            <div class="login-brand" style="text-align: center; margin-bottom: 2.5rem;">
                <i class="fas fa-shopping-bag" style="font-size: 3.5rem; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; filter: drop-shadow(0 2px 10px rgba(99, 102, 241, 0.4)); margin-bottom: 0.75rem; display: inline-block;"></i>
                <h2>SIBBO</h2>
                <span style="display: block; text-align: center; color: #a5b4fc; font-size: 0.9rem; font-weight: 600; margin-top: 0.5rem; letter-spacing: 0.5px; opacity: 0.85;">Sistem Belanja Berbasis Online</span>
            </div>
            
            <?php 
            // Tampilkan pesan error jika ada
            if (isset($_SESSION['error_message'])) {
                echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan
            }
            ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>