<?php

// ====================================================================
// !!! PERHATIAN: SKRIP INI SANGAT SENSITIF KEAMANAN !!!
// !!! HAPUS SKRIP INI DARI SERVER ANDA SEGERA SETELAH DIGUNAKAN !!!
// ====================================================================

// --- Konfigurasi Database Anda ---
$servername = "localhost"; // Ganti jika database Anda di server lain
$username_db = "althaf";   // Ganti dengan username database Anda
$password_db = "160907";   // Ganti dengan password database Anda
$dbname = "absenrfid";    // Ganti dengan nama database Anda (sesuai login.php)

// --- Konfigurasi Reset Password ---
$target_username = "Aan Setiadi"; // <<< GANTI DENGAN USERNAME YANG INGIN DIRESET PASSWORDNYA
$new_password = "@Dilan1990"; // <<< GANTI DENGAN PASSWORD BARU YANG ANDA INGINKAN

echo "<html><head><title>Reset Password</title></head><body>";
echo "<h1>Reset Password Admin</h1>";
echo "<p>Mencoba mereset password untuk user: <strong>" . htmlspecialchars($target_username) . "</strong></p>";

// Buat koneksi ke database
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("<p style='color: red;'>Koneksi database gagal: " . $conn->connect_error . "</p></body></html>");
}

// Hash password baru
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update password di database
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
if ($stmt === false) {
    die("<p style='color: red;'>Error prepare statement: " . $conn->error . "</p></body></html>");
}

$stmt->bind_param("ss", $hashed_password, $target_username);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<p style='color: green; font-weight: bold;'>Password untuk user '" . htmlspecialchars($target_username) . "' BERHASIL direset!</p>";
        echo "<p>Password baru Anda adalah: <strong style='color: blue;'>" . htmlspecialchars($new_password) . "</strong></p>";
        echo "<p>Sekarang Anda bisa mencoba login dengan password ini.</p>";
        echo "<p style='color: red; font-weight: bold;'>!!! SANGAT PENTING: HAPUS FILE INI DARI SERVER ANDA SEGERA !!!</p>";
    } else {
        echo "<p style='color: orange;'>Gagal mereset password. Kemungkinan username '" . htmlspecialchars($target_username) . "' tidak ditemukan di database.</p>";
    }
} else {
    echo "<p style='color: red;'>Error saat mengeksekusi update: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();

echo "</body></html>";

?>
