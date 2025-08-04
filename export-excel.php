<?php
include "koneksi.php"; // Include your database connection file

// Set headers for Excel file download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekapitulasi-presensi-bulanan.xls");

// Get filter parameters for month, year, and search
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Escape search input to prevent SQL injection
$search = mysqli_real_escape_string($konek, $search);

// Query to fetch data filtered by the selected month and year
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
          WHERE MONTH(a.tanggal) = '$month' 
          AND YEAR(a.tanggal) = '$year' 
          AND a.jam_pulang IS NOT NULL";

if (!empty($search)) {
    $query .= " AND (b.nama LIKE '%$search%' 
                    OR k.kelas LIKE '%$search%' 
                    OR a.jam_masuk LIKE '%$search%' 
                    OR a.jam_pulang LIKE '%$search%')";
}

// Execute query
$result = mysqli_query($konek, $query);

// Check for query execution error
if (!$result) {
    die('Error executing query: ' . mysqli_error($konek));
}

// Start output buffering
ob_start();
?>

<table border="1" cellpadding="5" cellspacing="0">
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

<?php
// Output the content
echo ob_get_clean();
?>
