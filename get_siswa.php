<?php
include "koneksi.php";

if (isset($_POST['kelas_id'])) {
    $kelas_id = intval($_POST['kelas_id']); // Gunakan intval untuk memastikan nilai integer

    // Ambil data siswa berdasarkan kelas_id
    $siswa_query = "SELECT nisn, nama FROM siswa WHERE kelas = ?";
    $stmt = $konek->prepare($siswa_query);
    
    // Gunakan 'i' jika kelas_id adalah integer
    $stmt->bind_param("i", $kelas_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = "<option value=''>Pilih NISN</option>";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='" . htmlspecialchars($row['nisn']) . "'>" . htmlspecialchars($row['nama']) . " (" . htmlspecialchars($row['nisn']) . ")</option>";
    }

    echo $options;
}
?>
