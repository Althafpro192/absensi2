<?php 
include "koneksi.php";

// Ambil data kelas untuk dropdown
$kelas_query = "SELECT id, kelas FROM kelas";
$kelas_result = $konek->query($kelas_query);
$kelas_options = '';
while ($row = $kelas_result->fetch_assoc()) {
    $kelas_options .= "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['kelas']) . "</option>";
}

if (isset($_POST['btnSimpan'])) {
    // Mengambil dan membersihkan data dari form
    $nisn = htmlspecialchars(trim($_POST['nisn']));
    $kelas = htmlspecialchars(trim($_POST['kelas']));
    $dari = htmlspecialchars(trim($_POST['dari']));
    $sampai = htmlspecialchars(trim($_POST['sampai']));
    $keterangan = htmlspecialchars(trim($_POST['keterangan']));

    // Ambil nama dari NISN
    $stmt = $konek->prepare("SELECT nama FROM siswa WHERE nisn = ?");
    $stmt->bind_param("s", $nisn);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $nama = $data['nama'];
    $stmt->close();

    // Cek apakah NISN sudah ada di tabel ketidakhadiran dengan tanggal yang sama
    $stmt = $konek->prepare("SELECT COUNT(*) as count FROM ketidakhadiran WHERE nisn = ? AND (dari <= ? AND sampai >= ?)");
    $stmt->bind_param("sss", $nisn, $sampai, $dari);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    if ($data['count'] > 0) {
        echo "<script>
        alert('NISN sudah ada di tabel ketidakhadiran untuk rentang tanggal ini. Mohon gunakan NISN yang berbeda atau tanggal yang berbeda.');
        window.location.href = 'tambahabsen_user.php';
        </script>";
        exit;
    }
    $stmt->close();

    // Masukkan data ke dalam tabel ketidakhadiran
    $stmt = $konek->prepare("INSERT INTO ketidakhadiran (nama, nisn, kelas, dari, sampai, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nama, $nisn, $kelas, $dari, $sampai, $keterangan);

    if ($stmt->execute()) {
        // Redirect ke halaman ketidakhadiran jika berhasil
        echo "<script>
        alert('Data berhasil disimpan');
        window.location.href = 'ketidakhadiran_user.php';
        </script>";
    } else {
        echo "<script>
        alert('Gagal menyimpan data: " . $stmt->error . "');
        window.location.href = 'tambahabsen_user.php';
        </script>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#kelas').change(function(){
                var kelasId = $(this).val();
                
                if(kelasId != ''){
                    $.ajax({
                        url: "get_siswa.php",
                        method: "POST",
                        data: {kelas_id: kelasId},
                        success: function(data){
                            $('#nisn').html(data);
                        }
                    });
                } else {
                    $('#nisn').html('<option value="">Pilih kelas terlebih dahulu</option>'); 
                }
            });
        });
    </script>

    <title>Tambah Data Ketidakhadiran</title>
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
    <?php include "menu_user.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Tambah Data Ketidakhadiran</div>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <h4 class="headline">Tambah Data Ketidakhadiran</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="kelas">Kelas</label>
                    <select name="kelas" id="kelas" required>
                        <option value="">Pilih Kelas</option>
                        <?php echo $kelas_options; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nisn">NISN</label>
                    <select name="nisn" id="nisn" required>
                        <option value="">Pilih Siswa</option>
                        <!-- Data siswa akan dimuat dengan AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="dari">Dari</label>
                    <input type="date" name="dari" id="dari" required>
                </div>
                <div class="form-group">
                    <label for="sampai">Sampai</label>
                    <input type="date" name="sampai" id="sampai" required>
                </div>
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <select name="keterangan" id="keterangan" required>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="Alpa">Alpa</option>
                    </select>
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