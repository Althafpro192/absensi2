<?php
// Mendapatkan nama file PHP saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Utama</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            height: 100vh;
            width: 269px;
            position: fixed;
            top: 0;
            left: 0;
            background: white;
            border-right: 1px solid #EBF1F6;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 30px 20px; /* Increased padding for better spacing */
        }

        .sidebar .brand {
            width: 100%;
            height: 80px; /* Increased height */
            color: #5D87FF; /* Color of the text */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 28px; /* Larger font size */
            font-weight: bold;
            margin-bottom: 30px; /* Increased margin for spacing below brand */
            text-align: center;
            border-bottom: 2px solid #EBF1F6; /* Optional: Border below the brand */
        }

        .sidebar .brand span {
            font-size: 24px; /* Adjust font size */
        }

        .sidebar a {
            width: 100%;
            padding: 15px;
            text-decoration: none;
            font-size: 16px; /* Slightly larger font size */
            color: #2A3547;
            display: flex;
            align-items: center;
            border-radius: 7px;
            margin-bottom: 10px;
            transition: background 0.3s, color 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #5D87FF;
            color: white;
            font-weight: 700;
        }

        .sidebar .icon {
            margin-right: 15px; /* Increased margin for better spacing */
            font-size: 20px; /* Larger icon size */
        }

        .content {
            margin-left: 269px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px;
                border-right: none;
            }
            .sidebar .brand {
                margin-bottom: 10px;
                height: auto; /* Adjust height for smaller screens */
                font-size: 24px; /* Adjust font size for smaller screens */
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">
            <span>E-PRESENSI</span>
        </div>
        <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">
            <div class="icon">🏠</div> Home
        </a>
        <a href="datasiswa.php" class="<?= $current_page == 'datasiswa.php' ? 'active' : '' ?>">
            <div class="icon">📋</div> Data Siswa
        </a>
        <a href="data_kelas.php" class="<?= $current_page == 'data_kelas.php' ? 'active' : '' ?>">
            <div class="icon">🏫</div> Data Kelas
        </a>
        <a href="scan.php" class="<?= $current_page == 'scan.php' ? 'active' : '' ?>">
            <div class="icon">📱</div> Scan RFID
        </a>
        <a href="absensi.php" class="<?= $current_page == 'absensi.php' ? 'active' : '' ?>">
            <div class="icon">📊</div> Rekapitulasi
        </a>
        <a href="ketidakhadiran.php" class="<?= $current_page == 'ketidakhadiran.php' ? 'active' : '' ?>">
            <div class="icon">📬</div> Ketidakhadiran
        </a>
        <a href="data_admin.php" class="<?= $current_page == 'data_admin.php' ? 'active' : '' ?>">
            <div class="icon">👥</div> Data Admin
        </a>
    </div>

    <div class="content">
        <!-- Konten utama Anda di sini -->
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</body>
</html>
