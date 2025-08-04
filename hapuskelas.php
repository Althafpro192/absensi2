<?php
include "koneksi.php";

// Validasi ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>
    alert('ID tidak valid');
    location.replace('data_kelas.php');
    </script>";
    exit;
}

// Menghapus data kelas berdasarkan ID
$stmt = $konek->prepare("DELETE FROM kelas WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "<script>
    alert('Data berhasil dihapus');
    location.replace('data_kelas.php');
    </script>";
} else {
    echo "<script>
    alert('Gagal menghapus data: " . $stmt->error . "');
    location.replace('data_kelas.php');
    </script>";
}

$stmt->close();
?>
