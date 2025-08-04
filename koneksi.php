<?php
// Konfigurasi koneksi
$host = "192.168.20.95";     // Ganti dengan host database Anda
$user = "root";          // Ganti dengan username database Anda
$pass = "tefa2025bisa";              // Ganti dengan password database Anda
$dbname = "absenrfid";   // Ganti dengan nama database Anda

// $host = "localhost";     // Ganti dengan host database Anda
// $user = "althaf";          // Ganti dengan username database Anda
// $pass = "160907";              // Ganti dengan password database Anda
// $dbname = "absenrfid";
// Membuat koneksi menggunakan MySQLi
$konek = new mysqli($host, $user, $pass, $dbname);

// Periksa apakah koneksi berhasil
if ($konek->connect_error) {
    die("Koneksi gagal: " . $konek->connect_error);
}


// Jangan menutup koneksi di sini; biarkan itu terbuka sampai diperlukan.
// Fungsi penutupan koneksi dapat dipanggil dari file lain atau di akhir pemrosesan halaman.
?>
