<?php
include "koneksi.php";

// Validasi ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>
    alert('ID tidak valid');
    location.replace('ketidakhadiran.php');
    </script>";
    exit;
}

// Menghapus data ketidakhadiran berdasarkan ID
$stmt = $konek->prepare("DELETE FROM ketidakhadiran WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>
    alert('Data berhasil dihapus');
    location.replace('ketidakhadiran.php');
    </script>";
} else {
    echo "<script>
    alert('Gagal menghapus data: " . $stmt->error . "');
    location.replace('ketidakhadiran.php');
    </script>";
}

$stmt->close();
?>
