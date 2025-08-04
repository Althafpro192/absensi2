<?php
// Mulai sesi
session_start();

// Cek apakah user sudah login dan memiliki role 'wali_kelas'. Jika tidak, alihkan ke halaman login.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'wali_kelas') {
    header("Location: ../login.php");
    exit();
}

// Sertakan file koneksi database
include "../koneksi.php";

// Ambil id user yang sedang login dari sesi.
$user_id = $_SESSION['user_id'];

// Ambil id_kelas wali kelas yang sedang login
// Asumsi: Ada kolom 'id_kelas' di tabel 'users' untuk mengaitkan wali kelas dengan kelasnya.
$stmt_kelas = $konek->prepare("SELECT id_kelas FROM users WHERE id = ?");
$stmt_kelas->bind_param("i", $user_id);
$stmt_kelas->execute();
$result_kelas = $stmt_kelas->get_result();
$wali_kelas_data = $result_kelas->fetch_assoc();
$stmt_kelas->close();

if (!$wali_kelas_data) {
    echo "Error: Data kelas untuk wali kelas tidak ditemukan.";
    exit();
}
$kelas_id = $wali_kelas_data['id_kelas'];

// Inisialisasi variabel pencarian
$search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";

// Query untuk mendapatkan data siswa dengan filter kelas dan pencarian
$query = "SELECT s.id, s.rfid, s.nama, s.nisn, s.no_siswa, s.no_ortu, k.kelas 
          FROM siswa s
          JOIN kelas k ON s.kelas = k.id
          WHERE s.kelas = ? AND s.nama LIKE ?
          ORDER BY s.nama";

$stmt = $konek->prepare($query);
$stmt->bind_param("is", $kelas_id, $search);
$stmt->execute();
$result = $stmt->get_result();

// Tutup koneksi database
$konek->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <title>Data Siswa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            margin: 0;
            background-color: #f7f9fc;
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

        .headline {
            margin-top: 0;
            margin-bottom: 30px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            margin-top: 70px;
            margin-left: 269px;
            color: #495057;
            font-size: 16px;
            font-weight: 500;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container form {
            display: flex;
            align-items: center;
        }

        .search-container input[type="text"] {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            flex: 1;
        }

        .search-container button {
            margin-left: 10px;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 5px;
            border: none;
            background-color: #13DEB9;
            color: #fff;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #12c8a3;
        }

        .btn {
            display: inline-block;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            color: #fff;
            border: 1px solid transparent;
            white-space: nowrap;
            max-width: 150px;
            text-overflow: ellipsis;
            overflow: hidden;
            box-sizing: border-box;
        }

        .btn-add {
            background-color: #5D87FF;
            border-color: #5D87FF;
        }

        .btn-edit {
            background-color: #FFAE1F;
            border-color: #FFAE1F;
        }

        .btn-delete {
            background-color: #F73164;
            border-color: #F73164;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            color: #2A3547;
        }

        thead {
            background-color: #F1F5F9;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #EBF1F6;
        }

        th {
            font-weight: 600;
            background-color: #EBF3FE;
        }

        td {
            font-weight: 400;
        }

        tbody tr:hover {
            background-color: #F9F9F9;
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
    <?php include "menu_walkel.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Data Siswa Kelas Anda</div>
            <button class="logout-button" onclick="window.location.href='../logout.php'">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
        </div>
    </div>

    <div class="main-content">
        <h4 class="headline">Data Siswa</h4>
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari data siswa..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="btn">Cari</button>
            </form>
        </div>
        <a href="tambah.php" class="btn btn-add">Tambah Data</a>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>RFID</th>
                        <th>Nama</th>
                        <th>NISN</th>
                        <th>Kelas</th>
                        <th>No siswa/i</th>
                        <th>No ortu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$no}</td>
                                    <td>" . htmlspecialchars($row['rfid']) . "</td>
                                    <td>" . htmlspecialchars($row['nama']) . "</td>
                                    <td>" . htmlspecialchars($row['nisn']) . "</td>
                                    <td>" . htmlspecialchars($row['kelas']) . "</td>
                                    <td>" . htmlspecialchars($row['no_siswa']) . "</td>
                                    <td>" . htmlspecialchars($row['no_ortu']) . "</td>
                                    <td>
                                        <a href='edit.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-edit'>Edit</a>
                                        <a href='hapus.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-delete' onclick='return confirm(\"Yakin ingin menghapus data?\")'>Hapus</a>
                                    </td>
                                  </tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='8'>Tidak ada data siswa ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <div class="left">2024 © van Derren</div>
        <div class="right">Design & Develop by van Derren</div>
    </div>
</body>
</html>
