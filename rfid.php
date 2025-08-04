<?php 
include_once "koneksi.php"; // Menggunakan include_once untuk mencegah duplikasi

// Baca tabel tmprfid
$sql = mysqli_query($konek, "SELECT * FROM tmprfid");
$data = mysqli_fetch_array($sql);

// Cek jika $data adalah null
if ($data === null) {
    $rfid = ''; // Set RFID kosong jika tidak ada data
} else {
    $rfid = $data['rfid'];
}
?>

<div class="form-group">
    <label for="rfid">RFID</label>
    <input type="text" name="rfid" id="rfid" placeholder="Tap Kartu RFID" required value="<?php echo htmlspecialchars($rfid); ?>">
</div>
