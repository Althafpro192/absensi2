<?php
// --- PENGATURAN DEBUGGING (HAPUS/MATIKAN DI LINGKUNGAN PRODUKSI) ---
// error_reporting(E_ALL); // Laporkan semua jenis error PHP
// ini_set('display_errors', 1); // Tampilkan error di browser (atau respons ke klien)

// // Path untuk file log debug PHP untuk tambah.php
// $logFile = __DIR__ . '/tambah_debug.log';

// // Fungsi untuk menulis log ke file
// function writeLog($message) {
//     global $logFile;
//     // Tambahkan timestamp dan pesan ke file log
//     file_put_contents($logFile, date('[Y-m-d H:i:s]') . ' ' . $message . PHP_EOL, FILE_APPEND);
// }

//writeLog("--- New Request for Tambah Data Siswa Started ---"); // Log awal permintaan

// Pastikan file koneksi.php sudah benar dan terhubung ke database
include "koneksi.php";

// Periksa apakah koneksi database berhasil
if (!$konek) {
    $errorMessage = "Koneksi database gagal: " . mysqli_connect_error();
    //writeLog("FATAL ERROR: " . $errorMessage);
    echo "<script>
    alert('" . addslashes($errorMessage) . "');
    window.location.href = 'tambah.php';
    </script>";
    exit(); // Hentikan eksekusi jika koneksi gagal
}
//writeLog("Koneksi database berhasil.");

if (isset($_POST['btnSimpan'])) {
    //writeLog("Form submitted with btnSimpan.");
    // Mengambil dan membersihkan data dari form
    $rfid = htmlspecialchars(trim($_POST['rfid']));
    $nama = htmlspecialchars(trim($_POST['nama']));
    $nisn = htmlspecialchars(trim($_POST['nisn']));
    $kelas = htmlspecialchars(trim($_POST['kelas'])); // Ini akan berisi ID kelas dari tabel 'kelas'
    $no_siswa = htmlspecialchars(trim($_POST['no_siswa']));
    $no_ortu = htmlspecialchars(trim($_POST['no_ortu']));

    //writeLog("Input received: RFID=" . $rfid . ", Nama=" . $nama . ", NISN=" . $nisn . ", KelasID=" . $kelas . ", no_siswa=" . $no_siswa . ", no_ortu=" . $no_ortu);

    // Validasi input RFID tidak boleh kosong
    if (empty($rfid)) {
        //writeLog("ERROR: Kode RFID kosong.");
        echo "<script>
        alert('Kode RFID tidak boleh kosong.');
        window.location.href = 'tambah.php';
        </script>";
        exit;
    }

    // --- Validasi input Kelas tidak boleh kosong ---
    if (empty($kelas)) {
        //writeLog("ERROR: Kelas tidak boleh kosong.");
        echo "<script>
        alert('Kelas tidak boleh kosong. Mohon pilih kelas.');
        window.location.href = 'tambah.php';
        </script>";
        exit;
    }
    //writeLog("Kelas tidak kosong.");

     if (empty($no_siswa)) {
        writeLog("ERROR: Nomor Telepon Siswa kosong.");
        echo "<script>alert('Nomor Telepon Siswa tidak boleh kosong.'); window.location.href = 'tambah.php';</script>";
        exit;
    }
    // Remove any non-numeric characters
    $no_siswa = preg_replace('/[^0-9]/', '', $no_siswa);
    // Add '62' prefix if not present and the number is not already starting with '62'
    if (substr($no_siswa, 0, 2) !== '62') {
        // If it starts with '0', replace '0' with '62'
        if (substr($no_siswa, 0, 1) === '0') {
            $no_siswa = '62' . substr($no_siswa, 1);
        } else {
            // Otherwise, just prepend '62'
            $no_siswa = '62' . $no_siswa;
        }
    }
    //writeLog("Formatted No. Siswa: " . $no_siswa);


    // --- NEW: Validasi dan format nomor orang tua ---
    if (empty($no_ortu)) {
        //writeLog("ERROR: Nomor Telepon Orang Tua kosong.");
        echo "<script>alert('Nomor Telepon Orang Tua tidak boleh kosong.'); window.location.href = 'tambah.php';</script>";
        exit;
    }
    // Remove any non-numeric characters
    $no_ortu = preg_replace('/[^0-9]/', '', $no_ortu);
    // Add '62' prefix if not present and the number is not already starting with '62'
    if (substr($no_ortu, 0, 2) !== '62') {
        // If it starts with '0', replace '0' with '62'
        if (substr($no_ortu, 0, 1) === '0') {
            $no_ortu = '62' . substr($no_ortu, 1);
        } else {
            // Otherwise, just prepend '62'
            $no_ortu = '62' . $no_ortu;
        }
    }
    //writeLog("Formatted No. Ortu: " . $no_ortu);



    // --- Cek apakah RFID sudah ada di tabel siswa ---
    try {
        // Menggunakan kolom 'rfid' sesuai dengan nama kolom di database Anda
        $stmt = $konek->prepare("SELECT COUNT(*) as count FROM siswa WHERE rfid = ?");
        if ($stmt === false) {
            throw new Exception("Gagal menyiapkan statement RFID check: " . $konek->error);
        }
        $stmt->bind_param("s", $rfid);
        //writeLog("Menjalankan RFID check untuk: " . $rfid);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if ($data['count'] > 0) {
            //writeLog("ERROR: RFID sudah ada.");
            echo "<script>
            alert('RFID sudah ada. Mohon gunakan RFID yang berbeda.');
            window.location.href = 'tambah.php';
            </script>";
            exit;
        }
        //writeLog("RFID belum ada di database.");
    } catch (Exception $e) {
        //writeLog("EXCEPTION (RFID check): " . $e->getMessage());
        echo "<script>
        alert('Kesalahan saat cek RFID: " . addslashes($e->getMessage()) . "');
        window.location.href = 'tambah.php';
        </script>";
        exit;
    }

    // --- Cek apakah NISN sudah ada di tabel siswa ---
    try {
        $stmt = $konek->prepare("SELECT COUNT(*) as count FROM siswa WHERE nisn = ?");
        if ($stmt === false) {
            throw new Exception("Gagal menyiapkan statement NISN check: " . $konek->error);
        }
        $stmt->bind_param("s", $nisn);
        //writeLog("Menjalankan NISN check untuk: " . $nisn);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if ($data['count'] > 0) {
            //writeLog("ERROR: NISN sudah ada.");
            echo "<script>
            alert('NISN sudah ada. Mohon gunakan NISN yang berbeda.');
            window.location.href = 'tambah.php';
            </script>";
            exit;
        }
        //writeLog("NISN belum ada di database.");
    } catch (Exception $e) {
        //writeLog("EXCEPTION (NISN check): " . $e->getMessage());
        echo "<script>
        alert('Kesalahan saat cek NISN: " . addslashes($e->getMessage()) . "');
        window.location.href = 'tambah.php';
        </script>";
        exit;
    }

    // --- Jika validasi lolos, masukkan data ke dalam database ---
    try {
        // Mengubah kolom target dari 'kelas_id' menjadi 'kelas'
        // Karena 'kelas' adalah kolom NOT NULL di tabel siswa yang akan diisi dengan ID kelas
        $stmt = $konek->prepare("INSERT INTO siswa (rfid, nama, nisn, kelas, no_siswa, no_ortu) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Gagal menyiapkan statement student insert: " . $konek->error);
        }
        // 'i' untuk integer, karena 'kelas' di tabel siswa adalah INT UNSIGNED
        $stmt->bind_param("sssssi", $rfid, $nama, $nisn, $kelas, $no_siswa, $no_ortu);
        //writeLog("Menjalankan INSERT siswa dengan RFID=" . $rfid . ", Nama=" . $nama . ", NISN=" . $nisn . ", KelasID (ke kolom kelas)=" . $kelas. ", No. Siswa=" . $no_siswa . ", No. Ortu=" . $no_ortu);

        if ($stmt->execute()) {
           // writeLog("Data siswa berhasil disimpan ke database.");
            $stmt->close();

            // --- Bagian Baru: Mengirim perintah ke ESP32 untuk menulis RFID ---
            $esp32_ip = "192.168.20.198"; // Ganti dengan IP Address ESP32 Anda
            // $esp32_port = "80"; // Ganti dengan port yang digunakan ESP32 jika bukan 80
            $esp32_endpoint = "/set_rfid"; // Endpoint yang diharapkan di ESP32 Anda

            $url_esp32 = "http://$esp32_ip$esp32_endpoint?code=" . urlencode($rfid);
            //writeLog("Mengirim request ke ESP32 URL: " . $url_esp32);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_esp32);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Tingkatkan timeout cURL menjadi 10 detik
            $esp32_response = curl_exec($ch);
            $curl_error = curl_error($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Dapatkan HTTP status code
            curl_close($ch);

            if ($curl_error) {
              //  writeLog("ERROR (cURL): " . $curl_error);
                echo "<script>
                alert('Data siswa berhasil disimpan, tetapi gagal mengirim RFID ke ESP32: " . addslashes($curl_error) . "');
                window.location.href = 'datasiswa.php';
                </script>";
            } else {
                //writeLog("cURL berhasil. HTTP Code: " . $http_code . ", Respon ESP32: " . $esp32_response);
                echo "<script>
                alert('Data siswa berhasil disimpan dan RFID berhasil dikirim ke ESP32. Silakan tempelkan kartu pada alat RFID. Respon ESP32: " . addslashes($esp32_response) . "');
                window.location.href = 'datasiswa.php';
                </script>";
            }
        } else {
            throw new Exception("Gagal mengeksekusi INSERT siswa: " . $stmt->error);
        }
    } catch (Exception $e) {
        //writeLog("EXCEPTION (Student Insert/cURL): " . $e->getMessage());
        echo "<script>
        alert('Gagal menyimpan data siswa: " . addslashes($e->getMessage()) . "');
        window.location.href = 'tambah.php';
        </script>";
    }
}

// Menghapus data dari tabel tmprfid
// Ini relevan jika ada proses lain yang mengisi tmprfid yang perlu dihapus setelah form ini.
// Jika tmprfid hanya digunakan untuk pembacaan otomatis yang sekarang dihilangkan, baris ini bisa dihapus.
// Saya akan biarkan ini, tetapi tanpa alert ke pengguna jika gagal, hanya log.
if (!$konek->query("DELETE FROM tmprfid")) {
    //writeLog("WARNING: Gagal menghapus data sementara dari tmprfid: " . $konek->error);
}
//writeLog("Request for Tambah Data Siswa finished.");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>

    <!-- Link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- jQuery (tetap diperlukan jika ada script lain yang menggunakannya) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        // Baris ini dihapus karena RFID akan diinput manual
        // setInterval(function() {
        //     $("#norfid").load('rfid.php');
        // }, 500); 
    });

    function confirmLogout() {
        if (confirm('Anda yakin ingin logout?')) {
            window.location.href = 'logout.php';
        }
    }
    </script>

    <title>Tambah Data Siswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <style>
    html,
    body {
        height: 100%;
        margin: 0;
        font-family: 'Poppins', sans-serif;
        background-color: #f9f9f9;
    }

    body {
        display: flex;
        flex-direction: column;
        margin: 0;
    }

    .header-container {
        width: calc(100% - 269px);
        height: 70px;
        position: fixed;
        top: 0;
        left: 269px;
        background: white;
        border-bottom: 1px #EFF3FF solid;
        display: flex;
        align-items: center;
        padding: 0 20px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .header-content {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-title {
        font-size: 16.5px;
        font-weight: 600;
        color: #98A6AD;
    }

    .logout-button {
        background: #FBF2EF;
        border-radius: 7px;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        border: none;
        cursor: pointer;
        font-size: 14.5px;
        font-weight: 700;
        color: #DC3545;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .logout-button i {
        margin-right: 8px;
        transition: color 0.3s ease;
    }

    .logout-button:hover {
        background-color: #F8D7DA;
        transform: scale(1.05);
    }

    .logout-button:hover i {
        color: #C82333;
    }

    .main-content {
        flex: 1;
        padding: 20px;
        margin-top: 32px;
        margin-left: 269px;
        background: #f9f9f9;
        padding-bottom: 60px;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .headline {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        border-bottom: 3px solid #5D87FF;
        padding-bottom: 15px;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 16px;
    }

    .form-group input,
    .form-group select {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        color: #333;
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #5D87FF;
        outline: none;
        box-shadow: 0 0 5px rgba(93, 135, 255, 0.2);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        padding: 10px;
        margin-top: 30px;
    }

    .btn-save {
        background-color: #5D87FF;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 12px 24px;
        font-size: 18px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s ease;
        display: inline-block;
        text-align: center;
    }

    .btn-save:hover {
        background-color: #4a6ee0;
    }

    .footer {
        height: 60px;
        padding: 20px;
        background: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #98A6AD;
        font-size: 13px;
        line-height: 19.5px;
        font-weight: 400;
        box-shadow: 0 -1px 2px rgba(0, 0, 0, 0.1);
        border-top: 1px solid #e0e0e0;
        margin-top: auto;
    }

    .footer .left,
    .footer .right {
        flex: 1;
    }

    .footer .left {
        text-align: left;
    }

    .footer .right {
        text-align: right;
    }
    </style>
</head>

<body>
    <?php include "menu.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Tambah Data Siswa</div>
            <button class="logout-button" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> LOG OUT
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <h4 class="headline">Tambah Data Siswa</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="rfid">Kode RFID</label>
                    <input type="text" name="rfid" id="rfid" placeholder="Scan kartu RFID atau masukkan kode manual" required maxlength="16">
                </div>
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" placeholder="Nama" required>
                </div>
                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <input type="text" name="nisn" id="nisn" placeholder="NISN" required pattern="[0-9]{9,15}"
                        inputmode="numeric" minlength="9" maxlength="15"
                        title="NISN harus terdiri dari 10 digit angka.">
                </div>
                <div class="form-group">
                    <label for="kelas">Kelas</label>
                    <select name="kelas" id="kelas" required>
                        <option value="">Pilih Kelas</option>
                        <?php
                        // Fetch kelas options
                        $kelas_query = "SELECT id, kelas FROM kelas ORDER BY kelas";
                        $kelas_result = mysqli_query($konek, $kelas_query);
                        while ($kelas_row = mysqli_fetch_assoc($kelas_result)) {
                            echo "<option value=\"" . htmlspecialchars($kelas_row['id']) . "\">" . htmlspecialchars($kelas_row['kelas']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                 <div class="form-group">
                    <label for="no_siswa">Nomor Telepon Siswa</label>
                    <input type="tel" name="no_siswa" id="no_siswa" placeholder="Contoh: 081234567890 atau 6281234567890" required
                           pattern="^(0|\+?62)\d{8,15}$"
                           title="Masukkan nomor telepon siswa (diawali 0 atau 62, 10-13 digit).">
                </div>
                <div class="form-group">
                    <label for="no_ortu">Nomor Telepon Orang Tua</label>
                    <input type="tel" name="no_ortu" id="no_ortu" placeholder="Contoh: 081234567890 atau 6281234567890" required
                           pattern="^(0|\+?62)\d{8,15}$"
                           title="Masukkan nomor telepon orang tua (diawali 0 atau 62, 10-13 digit).">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save" name="btnSimpan" id="btnSimpan">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <div class="left">2024 © van Derren</div>
        <div class="right">Design & Develop by van Derren</div>
    </div>
</body>

</html>
