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
    <div class="login-container">
        <form action="proses_login.php" method="POST">
            <div class="login-brand" style="text-align: center; margin-bottom: 2rem;">
                <i class="fas fa-shopping-bag" style="font-size: 3rem; color: var(--primary); margin-bottom: 0.5rem; display: inline-block;"></i>
                <h2 style="margin: 0; font-weight: 800; font-size: 1.6rem; color: var(--text-main); letter-spacing: -0.5px;">SIBBO</h2>
                <span style="color: var(--text-muted); font-size: 0.85rem;">Sistem Belanja Berbasis Online</span>
            </div>
            
            <?php 
            // Tampilkan pesan error jika ada
            if (isset($_SESSION['error_message'])) {
                echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
                unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan
            }
            ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>