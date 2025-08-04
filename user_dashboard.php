<?php
session_start();
if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include "koneksi.php";

// Fetch statistics from absensi table
$query = "SELECT 
            COUNT(*) AS total_presensi,
            SUM(CASE WHEN jam_masuk BETWEEN '06:00:00' AND '07:00:00' THEN 1 ELSE 0 END) AS total_hadir,
            SUM(CASE WHEN jam_masuk > '07:00:00' THEN 1 ELSE 0 END) AS total_telat,
            (SELECT COUNT(*) FROM ketidakhadiran) AS total_ketidakhadiran
          FROM absensi";

$result = mysqli_query($konek, $query);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Menu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General styles */
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            scroll-behavior: smooth;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .header-container {
            width: calc(100% - 269px);
            height: 70px;
            position: fixed;
            top: 0;
            left: 269px;
            background: #ffffff;
            border-bottom: 1px #e0e0e0 solid;
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
            border: 0;
            border-radius: 7px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #DC3545;
            font-size: 14.5px;
            font-weight: 700;
            text-transform: uppercase;
            transition: background 0.3s ease, color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            outline: none;
            box-shadow: none;
        }

        .logout-button:hover {
            background: #f9e3e1;
            color: #c82333;
            transform: scale(1.05) rotate(2deg);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .logout-button:active {
            background: #f3d0cd;
            color: #bd2130;
            transform: scale(0.98) rotate(-2deg);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }

        .logout-button i {
            margin-right: 8px;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-top: 40px;
            margin-left: 269px; /* Ensure this matches the left margin of the header-container */
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            display: flex;
            align-items: center;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            color: #333;
            position: relative;
            overflow: hidden;
            width: calc(25% - 20px); /* Four cards per row */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-icon {
            font-size: 2rem;
            margin-right: 15px;
            flex-shrink: 0;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .card-content {
            flex-grow: 1;
        }

        .card h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .card p {
            font-size: 1.5rem;
            margin: 5px 0;
            font-weight: bold;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card:hover .card-icon {
            color: #000; /* Change color on hover for more effect */
        }

        .total-presensi .card-icon {
            color: #4A90E2; /* Blue */
        }

        .total-hadir .card-icon {
            color: #50E3C2; /* Green */
        }

        .total-telat .card-icon {
            color: #F5A623; /* Orange */
        }

        .total-ketidakhadiran .card-icon {
    color: #D0021B; /* Merah atau warna lain yang sesuai */
}


        .footer {
            height: 60px;
            padding: 20px;
            background: #ffffff;
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

        .chart-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
        }

        .chart-card {
            flex: 1;
            min-width: 300px; /* Minimum width for better responsiveness */
            max-width: 600px; /* Maximum width to avoid being too large */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            color: #333;
            margin-bottom: 20px; /* Space between charts */
        }

        .chart-card canvas {
            width: 100% !important;
            height: auto; /* Adjust height based on width */
            max-height: 400px; /* Limit maximum height for pie chart */
        }

        @media (max-width: 768px) {
            .card {
                width: calc(100% - 20px); /* Full width on small screens */
            }

            .chart-container {
                flex-direction: column;
                gap: 20px;
            }

            .chart-card {
                max-width: none; /* Remove max-width on small screens */
            }

            .header-container {
                width: 100%;
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include "menu_user.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Dashboard</div>
            <button class="logout-button" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="card-container">
            <div class="card total-presensi">
                <div class="card-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="card-content">
                    <h3>Total Presensi</h3>
                    <p><?php echo $data['total_presensi']; ?></p>
                </div>
            </div>
            <div class="card total-hadir">
                <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                <div class="card-content">
                    <h3>Total Hadir</h3>
                    <p><?php echo $data['total_hadir']; ?></p>
                </div>
            </div>
            <div class="card total-telat">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-content">
                    <h3>Total Telat</h3>
                    <p><?php echo $data['total_telat']; ?></p>
                </div>
            </div>
            <div class="card total-ketidakhadiran">
    <div class="card-icon"><i class="fas fa-user-slash"></i></div> <!-- Ganti icon -->
    <div class="card-content">
        <h3>Total Ketidakhadiran</h3>
        <p><?php echo $data['total_ketidakhadiran']; ?></p>
    </div>
</div>

        </div>

        <div class="chart-container">
            <div class="chart-card">
                <!-- <h4>Diagram Lingkaran</h4> -->
                <canvas id="presensiChart"></canvas>
            </div>
            <div class="chart-card">
                <!-- <h4>Diagram Batang</h4> -->
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="left">2024 © van Derren</div>
        <div class="right">Design & Develop by van Derren</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Example charts (replace with actual data)
        const presensiCtx = document.getElementById('presensiChart').getContext('2d');
        const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');

        new Chart(presensiCtx, {
    type: 'pie',
    data: {
        labels: ['Hadir', 'Telat', 'Ketidakhadiran'],
        datasets: [{
            label: 'Presensi',
            data: [<?php echo $data['total_hadir']; ?>, <?php echo $data['total_telat']; ?>, <?php echo $data['total_ketidakhadiran']; ?>],
            backgroundColor: ['#50E3C2', '#F5A623', '#D0021B']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (context.parsed !== null) {
                            label += ': ' + context.parsed.toLocaleString();
                        }
                        return label;
                    }
                }
            }
        }
    }
});


new Chart(attendanceCtx, {
    type: 'bar',
    data: {
        labels: ['Hadir', 'Telat', 'Ketidakhadiran'],
        datasets: [{
            label: 'Attendance',
            data: [<?php echo $data['total_hadir']; ?>, <?php echo $data['total_telat']; ?>, <?php echo $data['total_ketidakhadiran']; ?>],
            backgroundColor: ['#50E3C2', '#F5A623', '#D0021B']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (context.parsed !== null) {
                            label += ': ' + context.parsed.toLocaleString();
                        }
                        return label;
                    }
                }
            }
        }
    }
});

    </script>
</body>
</html>
