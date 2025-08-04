<?php 
    include "koneksi.php";

    // Ensure the 'id' parameter is provided in the URL
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        // Sanitize the ID to prevent SQL injection
        $id = mysqli_real_escape_string($konek, $id);

        // Execute the delete query
        $hapus = mysqli_query($konek, "DELETE FROM users WHERE id='$id'");

        if ($hapus) {
            echo "<script> 
            alert('Admin berhasil dihapus');
            location.replace('data_admin.php');
            </script>";
        } else {
            echo "<script> 
            alert('Gagal menghapus admin');
            location.replace('data_admin.php');
            </script>";
        }
    } else {
        // Redirect back to data_admin.php if 'id' parameter is missing
        echo "<script> 
        alert('ID admin tidak ditemukan');
        location.replace('data_admin.php');
        </script>";
    }
?>
