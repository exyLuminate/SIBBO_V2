SIBBO (Sistem Informasi Belanja Berbasis Online)

# SIBBO adalah aplikasi Sistem Kasir (Point of Sale - POS) berbasis web sederhana namun fungsional yang dibangun menggunakan PHP Native. Aplikasi ini dirancang untuk membantu usaha kecil menengah dalam mencatat transaksi penjualan, mengelola stok barang, dan melihat laporan pendapatan.

## 🌟 Fitur Utama
## Multi-Role User: Mendukung peran Admin (akses penuh) dan Kasir (terbatas pada penjualan).

## Dashboard Admin: Ringkasan statistik penjualan harian, total stok, dan grafik barang terlaris.

## Manajemen Master Data: CRUD (Create, Read, Update, Delete) untuk Barang, Kategori, dan Pengguna.

## Manajemen Stok: Pencatatan riwayat stok masuk (kulakan) yang otomatis menambah stok barang.

## Halaman Kasir (POS) Interaktif:
Pencarian barang instan (tanpa reload).
Keranjang belanja berbasis sesi.
Perhitungan subtotal dan kembalian otomatis.

## Laporan Transaksi: Melihat riwayat penjualan dengan filter rentang tanggal dan detail struk per transaksi.

## Keamanan: Password hashing (Bcrypt) dan proteksi halaman berbasis sesi.

### 🛠️ Teknologi yang Digunakan
Bahasa: PHP (Native)

Database: MySQL / MariaDB

Frontend: HTML5, CSS3 (Custom Styles), JavaScript (Vanilla)

Server Lokal (Development): XAMPP / Laragon

#### 🚀 Instalasi & Persiapan
Ikuti langkah-langkah berikut untuk menjalankan proyek ini di komputer lokal Anda.

1. Persiapan Lingkungan
Pastikan Anda telah menginstal web server lokal seperti Laragon (rekomendasi) atau XAMPP.

2. Setup Folder Proyek
Unduh atau clone repositori proyek ini.

Pindahkan folder sibbo ke dalam direktori root web server Anda:

Laragon: C:\laragon\www\sibbo

XAMPP: C:\xampp\htdocs\sibbo

3. Setup Database
- Buka phpMyAdmin atau aplikasi database manager lain (HeidiSQL, DBeaver).

- Buat database baru dengan nama sibbo.

- Jalankan query dari file database.sql 

4. Konfigurasi Koneksi (Opsional)
Jika Anda menggunakan username atau password database yang berbeda (default Laragon/XAMPP biasanya root tanpa password), sesuaikan file koneksi.php:

$db_host = 'localhost';
$db_user = 'root';      // Sesuaikan user DB Anda
$db_pass = '';          // Sesuaikan password DB Anda
$db_name = 'sibbo';

##### 🧑‍💻 Cara Penggunaan
Buka browser dan akses: http://localhost/sibbo (atau http://sibbo.test jika menggunakan Laragon).

Akun Default
Role : Admin, Username : admin, password : admin123
Role : Kasir, Username : kasir, password : kasir123