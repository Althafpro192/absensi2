<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <title>Scan Kartu</title>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            setInterval(function(){
                $("#cekkartu").load('bacakartu.php');
            }, 1000);
        });

        function autoRefresh() {
            setTimeout(function() {
                location.reload();
            }, 5000); // Adjust the time as needed (5000ms = 5 seconds)
        }

        function triggerBounceAnimation() {
            $('.icon-container i').addClass('bounce');
            setTimeout(function() {
                $('.icon-container i').removeClass('bounce');
            }, 1000); // Duration of the bounce animation
        }
    </script>

    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: #495057;
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
            background: #ffffff;
            border-bottom: 1px solid #e0e0e0;
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
            flex: 1;
        }

        .logout-button {
            background: #FBF2EF;
            border: 0px solid #DC3545;
            border-radius: 7px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #DC3545;
            font-size: 14.5px;
            font-weight: 700;
            text-transform: uppercase;
            transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            outline: none;
            box-shadow: none;
            text-decoration: none;
        }

        .logout-button:hover {
            background: #f9e3e1;
            color: #c82333;
            border-color: #c82333;
            transform: scale(1.05);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .logout-button:active {
            background: #f3d0cd;
            color: #bd2130;
            border-color: #bd2130;
            transform: scale(0.98);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .logout-button i {
            margin-right: 8px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-top: 70px;
            margin-left: 269px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .headline {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .icon-container {
            font-size: 120px;
            color: #007bff;
            margin-bottom: 30px;
            cursor: pointer; /* Make the icon clickable */
        }

        .bubble-container {
            display: none; /* Hides the bubble container */
        }

        .logout-button:focus {
            outline: none;
        }

        .success-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #28a745;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .success-notification.show {
            opacity: 1;
        }

        .success-notification.celebrate {
            background: #ffc107; /* Golden color for celebration */
            color: #000;
            animation: celebrate 2s ease-out;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }

        @keyframes celebrate {
            0% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.8;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .bounce {
            animation: bounce 1s;
        }
    </style>
</head>
<body onload="autoRefresh()">
    <?php include "menu.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Scan RFID Page</div>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
        </div>
    </div>

    <div class="main-content">
        <?php
            // Set the default timezone to WIB (Western Indonesian Time)
            date_default_timezone_set('Asia/Jakarta');

            // Include the database connection file
            include 'koneksi.php';

            // --- Fungsi untuk mengirim SMS ---
            function sendSMS($target_phone, $message_text) {
                // URL API untuk mengirim pesan
                $sms_api_url = 'http://192.168.20.95:3212/send/message'; // Sesuaikan dengan URL API Anda

                // Data yang akan dikirim dalam format JSON
                $data = [
                    'phone' => $target_phone,
                    'message' => $message_text,
                ];

                $payload = json_encode($data);

                // Inisialisasi cURL
                $ch = curl_init($sms_api_url);

                // Set opsi cURL
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mengembalikan respons sebagai string
                curl_setopt($ch, CURLOPT_POST, true); // Mengatur metode ke POST
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Mengatur body request
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5 detik

                // Eksekusi cURL dan dapatkan respons
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curl_error = curl_error($ch);

                // Tutup cURL
                curl_close($ch);

                // Log atau tangani respons
                if ($curl_error) {
                    // Anda bisa menambahkan logging ke file jika diperlukan
                    // error_log("SMS send failed to $target_phone: " . $curl_error);
                    return "Error cURL: " . $curl_error;
                } elseif ($http_code != 200) {
                    // error_log("SMS API returned non-200 status for $target_phone: " . $http_code . " - " . $response);
                    return "API Error (HTTP " . $http_code . "): " . $response;
                } else {
                    // error_log("SMS sent successfully to $target_phone. Response: " . $response);
                    return "SMS sent: " . $response;
                }
            }
            // --- Akhir Fungsi untuk mengirim SMS ---


            // Fetch the RFID from the query parameters
            $rfid = isset($_GET['rfid']) ? $_GET['rfid'] : '';

            // Fetch the mode from bacakartu.php
            ob_start();
            include 'bacakartu.php';
            $bacakartuContent = ob_get_clean();
            // Extract mode from the content
            preg_match('/Mode: (.+?)<\/p>/', $bacakartuContent, $matches);
            $mode = isset($matches[1]) ? $matches[1] : 'Unknown';

            if ($rfid === "") {
                echo '<h4 class="headline">Presensi: ' . htmlspecialchars($mode) . '</h4>';
                echo '<p>Silahkan Tempelkan Kartu RFID Anda</p>';
                echo '<div class="icon-container" onclick="triggerBounceAnimation()">';
                echo '<i class="fas fa-id-badge"></i>'; // RFID Icon
                echo '</div>';
                echo '<div id="cekkartu"></div>';
            } else {
                // Ensure connection is open
                if ($konek instanceof mysqli && $konek->ping()) {
                    // Clean up tmprfid table
                    if (!$konek->query("DELETE FROM tmprfid")) {
                        echo "<script>
                        alert('Gagal menghapus data sementara: " . $konek->error . "');
                        </script>";
                    }

                    // Prepare and execute the queries securely
                    // --- MODIFIKASI: Ambil no_siswa dan no_ortu juga ---
                    $stmt = $konek->prepare("SELECT nama, no_siswa, no_ortu FROM siswa WHERE rfid = ?");
                    $stmt->bind_param("s", $rfid);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $jumlah_data = $result->num_rows;

                    if ($jumlah_data == 0) {
                        echo "Maaf Kartu Tidak Dikenali";
                        echo '<h1>Maaf Kartu Tidak Dikenali</h1>';
                    } else {
                        $data_siswa = $result->fetch_assoc();
                        $nama = $data_siswa['nama'];
                        $no_siswa = $data_siswa['no_siswa']; // Ambil nomor siswa
                        $no_ortu = $data_siswa['no_ortu'];   // Ambil nomor orang tua

                        $tanggal = date('Y-m-d');
                        $jam_sekarang = date('H:i:s');

                        $stmt_check = $konek->prepare("SELECT * FROM absensi WHERE rfid = ? AND tanggal = ?");
                        $stmt_check->bind_param("ss", $rfid, $tanggal);
                        $stmt_check->execute();
                        $data_check = $stmt_check->get_result()->fetch_assoc();

                        if ($data_check) {
                            if (is_null($data_check['jam_pulang'])) {
                                // Mark as 'Pulang' if the current time is after 15:00
                                $status = ($jam_sekarang > '15:00:00') ? 'Pulang' : $data_check['status_kehadiran'];
                                $stmt_update = $konek->prepare("UPDATE absensi SET jam_pulang = ?, status_kehadiran = ? WHERE rfid = ? AND tanggal = ?");
                                if ($stmt_update) {
                                    $stmt_update->bind_param("ssss", $jam_sekarang, $status, $rfid, $tanggal);
                                    if ($stmt_update->execute()) {
                                        $stmt_update->close();
                                        echo "Presensi Berhasil, " . htmlspecialchars($nama) ;
                                        echo '<div class="success-notification celebrate show">Presensi Berhasil, ' . htmlspecialchars($nama) . '!</div>';

                                        // --- NEW: Kirim SMS untuk presensi pulang ---
                                        $message_pulang = "Presensi pulang berhasil untuk " . $nama . " pada tanggal " . $tanggal . ".";
                                        if (!empty($no_siswa)) {
                                            sendSMS($no_siswa, $message_pulang);
                                        }
                                        if (!empty($no_ortu)) {
                                            sendSMS($no_ortu, $message_pulang);
                                        }
                                        // --- END NEW ---

                                    } else {
                                        echo '<h1>Gagal mengeksekusi pernyataan update</h1>';
                                    }
                                } else {
                                    echo '<h1>Gagal mempersiapkan pernyataan update</h1>';
                                }
                            } else {
                                echo '<h1>Presensi Sudah Tercatat</h1>';
                            }
                        } else {
                            // Determine the status based on the time of day
                            if ($jam_sekarang <= '07:00:00') {
                                $status = 'Hadir';
                            } elseif ($jam_sekarang <= '15:00:00') {
                                $status = 'Telat';
                            } else {
                                $status = 'Alpa';
                            }

                            // Insert new record
                            $stmt_insert = $konek->prepare("INSERT INTO absensi (rfid, nama, tanggal, jam_masuk, status_kehadiran) VALUES (?, ?, ?, ?, ?)");
                            if ($stmt_insert) {
                                $stmt_insert->bind_param("sssss", $rfid, $nama, $tanggal, $jam_sekarang, $status);
                                if ($stmt_insert->execute()) {
                                    $stmt_insert->close();
                                    echo "Presensi Berhasil, " . htmlspecialchars($nama) ;
                                    echo '<div class="success-notification celebrate show">Presensi Berhasil, ' . htmlspecialchars($nama) . '!</div>';

                                    // --- NEW: Kirim SMS untuk presensi masuk ---
                                    $message_masuk = "Presensi masuk berhasil untuk " . $nama . " pada tanggal " . $tanggal . " pukul " . $jam_sekarang . " dengan status: " . $status . ".";
                                    if (!empty($no_siswa)) {
                                        sendSMS($no_siswa, $message_masuk);
                                    }
                                    if (!empty($no_ortu)) {
                                        sendSMS($no_ortu, $message_masuk);
                                    }
                                    // --- END NEW ---

                                } else {
                                    echo '<h1>Gagal mengeksekusi pernyataan insert</h1>';
                                }
                            } else {
                                echo '<h1>Gagal mempersiapkan pernyataan insert</h1>';
                            }
                        }

                        $stmt_check->close();
                    }

                    $stmt->close();
                } else {
                    echo "<script>
                    alert('Koneksi ke database gagal.');
                    </script>";
                }
                $konek->close();
            }
        ?>
    </div>
</body>
</html>