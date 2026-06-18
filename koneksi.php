<?php
// Fungsi pembantu untuk memuat file .env secara manual (tanpa composer)
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Abaikan komentar
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Pisahkan key & value berdasarkan tanda '=' pertama
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1]);
                
                // Masukkan ke env & server variable jika belum di-set
                if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                    putenv(sprintf('%s=%s', $key, $val));
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
    }
}

// Muat berkas .env
loadEnv(__DIR__ . '/.env');

// Ambil kredensial dari environment variable dengan fallback default
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') ?: 'sibbo';

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (mysqli_connect_errno()) {
    echo 'Gagal melakukan koneksi ke Database : ' . mysqli_connect_error();
    die();
}

// Atur charset koneksi agar mendukung karakter khusus secara aman (utf8mb4)
mysqli_set_charset($koneksi, 'utf8mb4');

// Inisialisasi token CSRF jika session aktif namun token belum dibuat
if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
    }
}
?>