<?php
// Mulai sesi
session_start();

// Sertakan file koneksi database
include "../koneksi.php";

// Ambil parameter filter dari URL
$search = $_GET['search'] ?? '';
$dari_filter = $_GET['dari'] ?? '';
$sampai_filter = $_GET['sampai'] ?? '';

// Ambil ID kelas dari sesi (ini adalah filter wajib untuk wali kelas)
// Jika tidak ada di sesi, anggap sebagai tidak ada kelas yang dipilih.
$id_kelas_wali_kelas = $_SESSION['id_kelas_user'] ?? '';

// Bangun query utama untuk mengambil data ketidakhadiran
$query = "SELECT k.id, k.nama, k.nisn, k.dari, k.sampai, k.keterangan, kl.kelas AS nama_kelas
          FROM ketidakhadiran k
          JOIN kelas kl ON k.kelas = kl.id
          WHERE 1=1";

$params = [];
$types = '';

// Tambahkan filter wajib berdasarkan ID kelas wali kelas
if (!empty($id_kelas_wali_kelas)) {
    $query .= " AND k.kelas = ?";
    $params[] = $id_kelas_wali_kelas;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (k.nama LIKE ? OR k.nisn LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

if (!empty($dari_filter)) {
    $query .= " AND k.dari >= ?";
    $params[] = $dari_filter;
    $types .= 's';
}

if (!empty($sampai_filter)) {
    $query .= " AND k.sampai <= ?";
    $params[] = $sampai_filter;
    $types .= 's';
}

$stmt = $konek->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
$konek->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../header.php"; ?>
    <title>Data Ketidakhadiran</title>
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

        .search-container select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            margin-left: 10px;
        }

        .search-container input[type="date"] {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            margin-left: 10px;
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

        .btn-export {
            background-color: #13DEB9;
            border-color: #13DEB9;
        }

        .btn-export:hover {
            background-color: #12c8a3;
            border-color: #12c8a3;
        }

        .button-container {
            margin-top: 8px;
            margin-bottom: 10px;
            display: flex;
            gap: 10px;
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
            <div class="header-title">Ketidakhadiran Page</div>
            <button class="logout-button" onclick="window.location.href='../logout.php'">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
        </div>
    </div>

    <div class="main-content">
        <h4 class="headline">Data Ketidakhadiran</h4>
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Cari data siswa..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <!-- Filter kelas di sini dinonaktifkan karena wali kelas hanya dapat melihat data dari kelasnya sendiri -->
                <!-- Kode untuk select kelas sudah dihapus dari sini -->
                <input type="date" name="dari" value="<?php echo htmlspecialchars($dari_filter, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Dari">
                <input type="date" name="sampai" value="<?php echo htmlspecialchars($sampai_filter, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Sampai">
                <button type="submit" class="btn">Cari</button>
            </form>
        </div>
        <div class="button-container">
            <a href="tambahabsen_user.php" class="btn btn-add">Tambah Data</a>
            <a href="export_excel.php?<?php echo http_build_query(['search' => $search, 'kelas' => $id_kelas_wali_kelas, 'dari' => $dari_filter, 'sampai' => $sampai_filter]); ?>" class="btn btn-export">Export Excel</a>
        </div>
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NISN</th>
                        <th>Kelas</th>
                        <th>Dari</th>
                        <th>Sampai</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $no = 0;
                        if ($result) {
                            while($data = mysqli_fetch_assoc($result)) {
                                $no++;
                    ?>
                    <tr>
                        <td><?php echo $no; ?></td>
                        <td><?php echo htmlspecialchars($data['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['nisn'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['dari'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['sampai'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['keterangan'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="editabsen_user.php?id=<?php echo urlencode($data['id'] ?? ''); ?>" class="btn btn-edit">Edit</a>
                            <a href="hapusabsen_user.php?id=<?php echo urlencode($data['id'] ?? ''); ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Delete</a>
                        </td>
                    </tr>
                    <?php
                            }
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
