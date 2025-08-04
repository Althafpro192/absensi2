<?php
// --- PENGATURAN DEBUGGING (HAPUS/MATIKAN DI LINGKUNGAN PRODUKSI) ---
// error_reporting(E_ALL); // Laporkan semua jenis error PHP
//ini_set('display_errors', 1); // Tampilkan error di browser (atau respons ke klien)

// Path untuk file log debug PHP
// $logFile = __DIR__ . '/kirimkartu_debug.log';

// Fungsi untuk menulis log ke file
//function writeLog($message) {
  //  global $logFile;
    // Tambahkan timestamp dan pesan ke file log
    //file_put_contents($logFile, date('[Y-m-d H:i:s]') . ' ' . $message . PHP_EOL, FILE_APPEND);/
//}

//writeLog("--- New Request Started ---"); // Log awal permintaan

// Pastikan file koneksi.php sudah benar dan terhubung ke database
// Contoh isi koneksi.php:
// $konek = mysqli_connect("localhost", "username", "password", "nama_database");
// if (mysqli_connect_errno()) { 
//     die("Koneksi database gagal: " . mysqli_connect_error());
// }
include "koneksi.php";

// Periksa apakah koneksi database berhasil
if (!$konek) {
    $response_message = "Koneksi database gagal: " . mysqli_connect_error();
    //writeLog("ERROR: " . $response_message);
    header('Content-Type: application/json'); // Tetap kirim header JSON
    echo json_encode(['message' => $response_message]);
    exit(); // Hentikan eksekusi jika koneksi gagal
}
//writeLog("Koneksi database berhasil.");

// Set header untuk memberitahu klien (ESP32) bahwa respons adalah JSON
header('Content-Type: application/json');

// Inisialisasi pesan respons default
$response_message = "Terjadi kesalahan yang tidak diketahui.";

// Ambil data POST mentah dari body request
$input = file_get_contents('php://input');
//writeLog("Raw input received: " . ($input ? $input : "EMPTY"));

// Dekode JSON menjadi array asosiatif
$data = json_decode($input, true);

// Periksa apakah JSON berhasil didekode dan apakah 'rfid_code' ada
if (json_last_error() === JSON_ERROR_NONE && isset($data['rfid_code'])) {
    // Ambil dan bersihkan data RFID
    $rfid = htmlspecialchars(trim($data['rfid_code']));
    //writeLog("RFID code extracted: " . $rfid);

    // --- Proses Database ---

    // Mulai transaksi untuk memastikan atomisitas operasi
    mysqli_begin_transaction($konek);
    //writeLog("Transaksi database dimulai.");

    try {
        // --- Langkah 1: Kosongkan tabel tmprfid ---
        $stmt_delete = $konek->prepare("DELETE FROM tmprfid");
        if ($stmt_delete === false) {
            throw new Exception("Gagal menyiapkan statement DELETE: " . $konek->error);
        }
        //writeLog("Menjalankan DELETE dari tmprfid.");
        if (!$stmt_delete->execute()) {
            throw new Exception("Gagal mengosongkan tabel tmprfid: " . $stmt_delete->error);
        }
        $stmt_delete->close();
        //writeLog("Tabel tmprfid berhasil dikosongkan.");

        // --- Langkah 2: Simpan nomor RFID yang baru ke tabel tmprfid ---
        $stmt_insert = $konek->prepare("INSERT INTO tmprfid(rfid) VALUES(?)");
        if ($stmt_insert === false) {
            throw new Exception("Gagal menyiapkan statement INSERT: " . $konek->error);
        }
        $stmt_insert->bind_param("s", $rfid);
        //writeLog("Menjalankan INSERT ke tmprfid dengan RFID: " . $rfid);
        if (!$stmt_insert->execute()) {
            throw new Exception("Gagal menyimpan data ke tmprfid: " . $stmt_insert->error);
        }
        $stmt_insert->close();
        //writeLog("RFID " . $rfid . " berhasil disimpan ke tmprfid.");

        // --- Langkah 3: Ambil nama dari tabel siswa berdasarkan RFID ---
        // Pastikan nama kolom RFID di tabel 'siswa' adalah 'rfid_code'
        $stmt_select = $konek->prepare("SELECT nama FROM siswa WHERE rfid = ?");
        if ($stmt_select === false) {
            throw new Exception("Gagal menyiapkan statement SELECT: " . $konek->error);
        }
        $stmt_select->bind_param("s", $rfid);
        //writeLog("Menjalankan SELECT dari siswa dengan rfid_code: " . $rfid);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $row = $result_select->fetch_assoc();
        $stmt_select->close();

        if ($row && isset($row['nama'])) {
            $nama = $row['nama'];
            $response_message = "Absen " . $nama; // Pesan sukses dengan nama siswa
            //writeLog("Nama siswa ditemukan: " . $nama);
        } else {
            $response_message = "Maaf Kartu Tidak Dikenali"; // Kartu tidak ditemukan
            //writeLog("Kartu tidak dikenali di tabel siswa: " . $rfid);
        }

        // Commit transaksi jika semua operasi berhasil
        mysqli_commit($konek);
        //writeLog("Transaksi database berhasil di-commit.");

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($konek);
        $response_message = "Kesalahan database: " . $e->getMessage();
        //writeLog("ERROR (Database Transaction): " . $e->getMessage());
    }

} else {
    // Jika JSON tidak valid atau parameter 'rfid_code' tidak ditemukan
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response_message = "Gagal mendekode JSON: " . json_last_error_msg();
        //writeLog("ERROR: Gagal mendekode JSON. Raw input: " . ($input ? $input : "EMPTY"));
    } else {
        $response_message = "Parameter 'rfid_code' tidak ditemukan dalam permintaan JSON.";
        //writeLog("ERROR: Parameter 'rfid_code' tidak ditemukan. Raw input: " . ($input ? $input : "EMPTY"));
    }
}

// Kirim respons dalam format JSON
$json_response = json_encode(['message' => $response_message]);
//writeLog("Sending JSON response: " . $json_response);
echo $json_response;

// Tutup koneksi database
mysqli_close($konek);
//writeLog("Koneksi database ditutup. Request finished.");
?>
