<?php
// Ganti 'admin123' menjadi 'kasir123'
$password_polos = 'kasir123'; 

// Membuat hash
$hash_baru = password_hash($password_polos, PASSWORD_BCRYPT);

echo "Password Anda: " . htmlspecialchars($password_polos);
echo "<br><br>";
echo "Hash Baru (Copy ini): <br>";
echo htmlspecialchars($hash_baru);
?>