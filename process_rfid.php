<?php
// Include the database connection
include 'koneksi.php';

// Check if the RFID is provided in the GET request
if (isset($_GET['rfid'])) {
    $rfid = $_GET['rfid'];

    // Prepare and execute a query to check if the RFID exists in the 'siswa' table
    $stmt = $konek->prepare("SELECT nama FROM siswa WHERE rfid = ?");
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the student's name
        $data_siswa = $result->fetch_assoc();
        $nama = $data_siswa['nama'];

        // Set timezone
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d');
        $jam = date('H:i:s');

        // Retrieve the current mode (1 = Masuk, 2 = Pulang) from the status table
        $stmt_mode = $konek->prepare("SELECT mode FROM status LIMIT 1");
        $stmt_mode->execute();
        $result_mode = $stmt_mode->get_result();
        $mode_absen = $result_mode->fetch_assoc()['mode'];

        // Check if the student has already been recorded for today
        $stmt_absen = $konek->prepare("SELECT * FROM absensi WHERE rfid = ? AND tanggal = ?");
        $stmt_absen->bind_param("ss", $rfid, $tanggal);
        $stmt_absen->execute();
        $result_absen = $stmt_absen->get_result();

        if ($result_absen->num_rows == 0) {
            // First time attendance for today
            echo "Selamat Datang, {$nama}. Presensi berhasil untuk hari ini.";
            $stmt_insert = $konek->prepare("INSERT INTO absensi (rfid, tanggal, jam_masuk) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $rfid, $tanggal, $jam);
            $stmt_insert->execute();
        } else {
            // If the student is already recorded, update the departure time if the mode is "Pulang"
            if ($mode_absen == 2) {
                echo "Selamat Jalan, {$nama}. Waktu pulang berhasil dicatat.";
                $stmt_update = $konek->prepare("UPDATE absensi SET jam_pulang = ? WHERE rfid = ? AND tanggal = ?");
                $stmt_update->bind_param("sss", $jam, $rfid, $tanggal);
                $stmt_update->execute();
            } else {
                echo "Anda sudah melakukan presensi masuk hari ini.";
            }
        }

        // Clear the temporary RFID data
        $konek->query("DELETE FROM tmprfid");

    } else {
        // If the RFID is not recognized
        echo "Maaf, kartu RFID tidak dikenali.";
    }

} else {
    // If no RFID is provided
    echo "Tidak ada RFID yang dipindai.";
}

// Close the database connection
$konek->close();
?>
