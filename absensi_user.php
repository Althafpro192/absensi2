<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>
    <title>Rekapitulasi Presensi</title>
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

        .filter-container {
            margin-bottom: 20px;
        }

        .filter-container form {
            display: flex;
            align-items: center;
            gap: 10px; /* Menambahkan jarak antar elemen */
        }

        .filter-container label {
            margin-right: 10px;
            font-weight: 500;
        }

        .filter-container input[type="text"],
        .filter-container input[type="date"],
        .filter-container button,
        .filter-container select {
            padding: 8px 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        .filter-container input[type="text"] {
            width: 100%; /* Menyesuaikan lebar input teks */
        }

        .filter-container input[type="date"] {
            width: 160px; /* Menyesuaikan lebar input tanggal */
        }

        .filter-container select {
            width: 180px; /* Menyesuaikan lebar select kelas */
        }

        .filter-container button {
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            background-color: #13DEB9;
            color: #fff;
            cursor: pointer;
        }

        .filter-container button:hover {
            background-color: #12c8a3;
        }

        .export-button {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 5px;
            border: none;
            background-color: #5D87FF; /* Warna biru */
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .export-button:hover {
            background-color: #4a6bff; /* Warna biru lebih gelap saat hover */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .export-button:active {
            background-color: #4162d0; /* Warna biru lebih gelap saat diklik */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
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
    <?php include "menu_user.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Rekapitulasi Page</div>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Log out
            </button>
        </div>
    </div>

    <div class="main-content">
        <h4 class="headline">Rekapitulasi Presensi</h4>

        <div class="filter-container">
            <form method="GET" action="">
                <input type="text" id="search" name="search" placeholder="Cari Data" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <input type="date" id="tanggal" name="tanggal" value="<?php echo isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal'], ENT_QUOTES, 'UTF-8') : date('Y-m-d'); ?>">
                <select name="kelas">
                    <option value="">Pilih Kelas</option>
                    <?php
                    include "koneksi.php";
                    // Fetch distinct kelas options from kelas table
                    $kelas_query = "SELECT id, kelas FROM kelas ORDER BY kelas";
                    $kelas_result = mysqli_query($konek, $kelas_query);
                    while ($kelas_row = mysqli_fetch_assoc($kelas_result)) {
                        $selected = (isset($_GET['kelas']) && $_GET['kelas'] == $kelas_row['id']) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($kelas_row['id']) . "\" $selected>" . htmlspecialchars($kelas_row['kelas']) . "</option>";
                    }
                    ?>
                </select>
                <button type="submit">Cari</button>
            </form>
        </div>

        <p>
            <button class="export-button" onclick="window.location.href='export-excel.php?<?php echo http_build_query($_GET); ?>'">
                Export Excel
            </button>
        </p>

        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        include "koneksi.php";

                        $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
                        $search = isset($_GET['search']) ? $_GET['search'] : '';
                        $kelas = isset($_GET['kelas']) ? $_GET['kelas'] : '';

                        $query = "SELECT b.nama, k.kelas, a.tanggal, a.jam_masuk, a.jam_pulang,
                                     CASE
                                         WHEN a.jam_masuk IS NOT NULL AND a.jam_masuk BETWEEN '06:00:00' AND '07:00:00' THEN 'Hadir'
                                         WHEN a.jam_masuk IS NOT NULL AND a.jam_masuk > '07:00:00' THEN 'Telat'
                                         WHEN a.jam_masuk IS NULL AND CURTIME() > '15:00:00' THEN 'Alpa'
                                         ELSE 'Unknown'
                                     END AS status
                              FROM absensi a
                              JOIN siswa b ON a.rfid = b.rfid
                              JOIN kelas k ON b.kelas = k.id
                              WHERE a.tanggal = '$tanggal' 
                              AND a.jam_pulang IS NOT NULL";

                        if (!empty($search)) {
                            $search = mysqli_real_escape_string($konek, $search);
                            $query .= " AND (b.nama LIKE '%$search%' 
                                             OR k.kelas LIKE '%$search%' 
                                             OR a.jam_masuk LIKE '%$search%' 
                                             OR a.jam_pulang LIKE '%$search%')";
                        }

                        if (!empty($kelas)) {
                            $kelas = mysqli_real_escape_string($konek, $kelas);
                            $query .= " AND k.id = '$kelas'";
                        }

                        $result = mysqli_query($konek, $query);
                        $no = 1;
                        while ($data = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($data['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['tanggal'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['jam_masuk'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['jam_pulang'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($data['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php } ?>
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
