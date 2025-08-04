<?php
// --- PENGATURAN DEBUGGING (HAPUS/MATIKAN DI LINGKUNGAN PRODUKSI) ---
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Pastikan file koneksi.php sudah benar dan terhubung ke database
include "koneksi.php";

// Periksa apakah koneksi database berhasil
if (!$konek) {
    $errorMessage = "Koneksi database gagal: " . mysqli_connect_error();
    echo "<script>
    alert('" . addslashes($errorMessage) . "');
    location.replace('datasiswa.php');
    </script>";
    exit();
}

// Validasi ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>
    alert('ID tidak valid');
    location.replace('datasiswa.php');
    </script>";
    exit;
}

// Mengambil data siswa berdasarkan ID dari database
$stmt = $konek->prepare("SELECT * FROM siswa WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result_siswa = $stmt->get_result();
$hasil = $result_siswa->fetch_assoc();
$stmt->close();

if (!$hasil) {
    echo "<script>
    alert('Data tidak ditemukan');
    location.replace('datasiswa.php');
    </script>";
    exit;
}

// Ambil data kelas untuk dropdown
$kelas_query = "SELECT id, kelas FROM kelas";
$kelas_result = $konek->query($kelas_query);
$kelas_options = '';
while ($row = $kelas_result->fetch_assoc()) {
    // Menandai kelas yang saat ini dipilih
    $selected = ($row['id'] == $hasil['kelas']) ? 'selected' : '';
    $kelas_options .= "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['kelas']) . "</option>";
}

// Logika untuk menangani pengiriman formulir
if (isset($_POST['btnSimpan'])) {
    // Sanitasi dan validasi data dari form
    $rfid = htmlspecialchars(trim($_POST['rfid']));
    $nama = htmlspecialchars(trim($_POST['nama']));
    $nisn = htmlspecialchars(trim($_POST['nisn']));
    $kelas = htmlspecialchars(trim($_POST['kelas']));
    $no_siswa = htmlspecialchars(trim($_POST['no_siswa']));
    $no_ortu = htmlspecialchars(trim($_POST['no_ortu']));

    // Ambil RFID lama untuk perbandingan
    $rfid_lama = $hasil['rfid'];

    // --- Validasi dan format nomor telepon siswa ---
    if (empty($no_siswa)) {
        echo "<script>alert('Nomor Telepon Siswa tidak boleh kosong.'); window.location.href = 'edit.php?id=" . $id . "';</script>";
        exit;
    }
    $no_siswa = preg_replace('/[^0-9]/', '', $no_siswa);
    if (substr($no_siswa, 0, 2) !== '62') {
        if (substr($no_siswa, 0, 1) === '0') {
            $no_siswa = '62' . substr($no_siswa, 1);
        } else {
            $no_siswa = '62' . $no_siswa;
        }
    }

    // --- Validasi dan format nomor telepon orang tua ---
    if (empty($no_ortu)) {
        echo "<script>alert('Nomor Telepon Orang Tua tidak boleh kosong.'); window.location.href = 'edit.php?id=" . $id . "';</script>";
        exit;
    }
    $no_ortu = preg_replace('/[^0-9]/', '', $no_ortu);
    if (substr($no_ortu, 0, 2) !== '62') {
        if (substr($no_ortu, 0, 1) === '0') {
            $no_ortu = '62' . substr($no_ortu, 1);
        } else {
            $no_ortu = '62' . $no_ortu;
        }
    }

    // Cek apakah RFID sudah ada (selain yang sekarang diedit)
    $stmt = $konek->prepare("SELECT COUNT(*) as count FROM siswa WHERE rfid = ? AND id != ?");
    $stmt->bind_param("si", $rfid, $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['count'] > 0) {
        echo "<script>
        alert('RFID sudah ada. Mohon gunakan RFID yang berbeda.');
        window.location.href = 'edit.php?id=" . $id . "';
        </script>";
        exit;
    }
    $stmt->close();

    // Cek apakah NISN sudah ada (selain yang sekarang diedit)
    $stmt = $konek->prepare("SELECT COUNT(*) as count FROM siswa WHERE nisn = ? AND id != ?");
    $stmt->bind_param("si", $nisn, $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result['count'] > 0) {
        echo "<script>
        alert('NISN sudah ada. Mohon gunakan NISN yang berbeda.');
        window.location.href = 'edit.php?id=" . $id . "';
        </script>";
        exit;
    }
    $stmt->close();

    // Mempersiapkan pernyataan SQL UPDATE
    $stmt = $konek->prepare("UPDATE siswa SET rfid = ?, nama = ?, nisn = ?, kelas = ?, no_siswa = ?, no_ortu = ? WHERE id = ?");
    // 's' untuk string, 'i' untuk integer. Sesuaikan dengan tipe kolom di database Anda.
    $stmt->bind_param("sssisss", $rfid, $nama, $nisn, $kelas, $no_siswa, $no_ortu, $id);

    if ($stmt->execute()) {
        $stmt->close();

        // --- Bagian Baru: Mengirim perintah ke ESP32 jika RFID berubah ---
        if ($rfid != $rfid_lama) {
            $esp32_ip = "192.168.20.123"; // Ganti dengan IP Address ESP32 Anda
            $esp32_endpoint = "/set_rfid";
            $url_esp32 = "http://$esp32_ip$esp32_endpoint?code=" . urlencode($rfid);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_esp32);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $esp32_response = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error) {
                echo "<script>
                alert('Data siswa berhasil disimpan, tetapi gagal mengirim RFID ke ESP32: " . addslashes($curl_error) . "');
                window.location.href = 'datasiswa.php';
                </script>";
            } else {
                echo "<script>
                alert('Data siswa berhasil disimpan dan RFID berhasil dikirim ke ESP32. Silakan tempelkan kartu pada alat RFID. Respon ESP32: " . addslashes($esp32_response) . "');
                window.location.href = 'datasiswa.php';
                </script>";
            }
        } else {
            // Jika RFID tidak berubah, cukup tampilkan pesan sukses
            echo "<script>
            alert('Data berhasil disimpan');
            location.replace('datasiswa.php');
            </script>";
        }

    } else {
        $stmt->close();
        echo "<script>
        alert('Gagal menyimpan data: " . $konek->error . "');
        window.location.href = 'edit.php?id=" . $id . "';
        </script>";
    }
}
$konek->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <title>Edit Data Siswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        html, body {
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
            box-sizing: border-box;
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
            <div class="header-title">Edit Data Siswa</div>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> LOG OUT
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <h4 class="headline">Edit Data Siswa</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="rfid">Kode RFID</label>
                    <input type="text" name="rfid" id="rfid" placeholder="Scan kartu RFID atau masukkan kode manual" value="<?php echo htmlspecialchars($hasil['rfid']); ?>" required maxlength="16">
                </div>
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" name="nama" id="nama" placeholder="Nama" value="<?php echo htmlspecialchars($hasil['nama']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <input type="text" name="nisn" id="nisn" placeholder="NISN" value="<?php echo htmlspecialchars($hasil['nisn']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="kelas">Kelas</label>
                    <select name="kelas" id="kelas" required>
                        <?php echo $kelas_options; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="no_siswa">Nomor Telepon Siswa</label>
                    <input type="tel" name="no_siswa" id="no_siswa" placeholder="Contoh: 081234567890 atau 6281234567890" value="<?php echo htmlspecialchars($hasil['no_siswa']); ?>" required
                           pattern="^(0|\+?62)\d{8,15}$"
                           title="Masukkan nomor telepon siswa (diawali 0 atau 62, 10-13 digit).">
                </div>
                <div class="form-group">
                    <label for="no_ortu">Nomor Telepon Orang Tua</label>
                    <input type="tel" name="no_ortu" id="no_ortu" placeholder="Contoh: 081234567890 atau 6281234567890" value="<?php echo htmlspecialchars($hasil['no_ortu']); ?>" required
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
