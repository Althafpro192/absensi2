<?php
include "koneksi.php"; // Include your database connection file

// Set headers for Excel file download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekapitulasi-ketidakhadiran.xls");

// Get filter parameters from the URL
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kelas_filter = isset($_GET['kelas']) ? $_GET['kelas'] : '';
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : ''; // Month filter
$tahun_filter = isset($_GET['tahun']) ? $_GET['tahun'] : ''; // Year filter

// Escape inputs to prevent SQL injection
$search = mysqli_real_escape_string($konek, $search);
$kelas_filter = mysqli_real_escape_string($konek, $kelas_filter);
$bulan_filter = mysqli_real_escape_string($konek, $bulan_filter);
$tahun_filter = mysqli_real_escape_string($konek, $tahun_filter);

// Query to fetch data from ketidakhadiran, siswa, and kelas tables
$query = "SELECT b.nama, b.nisn, k.kelas, a.dari, a.sampai, a.keterangan
          FROM ketidakhadiran a
          JOIN siswa b ON a.nisn = b.nisn
          JOIN kelas k ON b.kelas = k.id
          WHERE 1=1";

if ($search) {
    $query .= " AND (b.nama LIKE '%$search%' 
                    OR b.nisn LIKE '%$search%' 
                    OR k.kelas LIKE '%$search%' 
                    OR a.keterangan LIKE '%$search%')";
}

if ($kelas_filter) {
    $query .= " AND b.kelas = '$kelas_filter'";
}

// Filter by month and year
if ($bulan_filter && $tahun_filter) {
    $query .= " AND MONTH(a.dari) = '$bulan_filter' AND YEAR(a.dari) = '$tahun_filter'";
} elseif ($bulan_filter) {
    $query .= " AND MONTH(a.dari) = '$bulan_filter'";
} elseif ($tahun_filter) {
    $query .= " AND YEAR(a.dari) = '$tahun_filter'";
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
            <th>NISN</th>
            <th>Kelas</th>
            <th>Dari</th>
            <th>Sampai</th>
            <th>Keterangan</th>
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
            <td><?php echo htmlspecialchars($data['nisn'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($data['kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($data['dari'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($data['sampai'], ENT_QUOTES, 'UTF-8'); ?></td>
            <td><?php echo htmlspecialchars($data['keterangan'], ENT_QUOTES, 'UTF-8'); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php
// Output the content
echo ob_get_clean();
?>
