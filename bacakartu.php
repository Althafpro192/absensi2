<?php
// Include the database connection
include "koneksi.php";

// Initialize variables
$mode = "Unknown"; // Default mode
$rfidStatus = 'No RFID detected'; // Default RFID status

// Query to get the mode from the status table
$sql = mysqli_query($konek, "SELECT mode FROM status LIMIT 1");
if ($sql && $data = mysqli_fetch_array($sql)) {
    switch ($data['mode']) {
        case 1:
            $mode = "Masuk";
            break;
        case 2:
            $mode = "Pulang";
            break;
        default:
            $mode = "Unknown";
    }
} else {
    $mode = "Error retrieving status"; // Error handling
}

// Query to read the RFID from the tmprfid table
$baca_kartu = mysqli_query($konek, "SELECT rfid FROM tmprfid LIMIT 1");
if ($baca_kartu && $data_kartu = mysqli_fetch_array($baca_kartu)) {
    $rfid = $data_kartu['rfid'];
    if (!empty($rfid)) {
        $rfidStatus = 'RFID detected: ' . htmlspecialchars($rfid);
    } else {
        $rfidStatus = 'No RFID detected'; // If RFID is empty
    }
} else {
    $rfidStatus = 'Error reading RFID data'; // Error handling
}

// Set the content type to HTML
header('Content-Type: text/html; charset=utf-8');

// Output the RFID status and mode
echo '<p>' . htmlspecialchars($rfidStatus) . '</p>';
echo '<p>Mode: ' . htmlspecialchars($mode) . '</p>';

?>
