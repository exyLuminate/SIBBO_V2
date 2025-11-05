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
    <title>Login - SIBBO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <form action="proses_login.php" method="POST">
            <h2>Login SIBBO</h2>
            
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