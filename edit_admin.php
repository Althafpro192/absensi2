<?php
include "koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_POST['btnUpdate'])) {
    // Mengambil dan membersihkan data dari form
    $username = htmlspecialchars(trim($_POST['username']));
    $role = htmlspecialchars(trim($_POST['role']));
    $password = htmlspecialchars(trim($_POST['password'])); // Optional new password
     $id_kelas = (isset($_POST['id_kelas']) && $role === 'wali_kelas') ? htmlspecialchars(trim($_POST['id_kelas'])) : null;

 if ($role === 'wali_kelas' && empty($id_kelas)) {
        echo "<script>
        alert('Anda harus memilih kelas untuk Wali Kelas.');
        window.location.href = 'edit_admin.php?id=" . $id . "';
        </script>";
        exit;
    }

    // Prepare SQL for updating user details
    if (!empty($password)) {
        // Hash the new password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $konek->prepare("UPDATE users SET username = ?, password_hash = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $password_hash, $role, $id);
    } else {
        $stmt = $konek->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $role, $id);
    }

    if ($stmt->execute()) {
        echo "<script>
        alert('Data admin berhasil diperbarui');
        window.location.href = 'data_admin.php';
        </script>";
    } else {
        echo "<script>
        alert('Gagal memperbarui data: " . $stmt->error . "');
        window.location.href = 'edit_admin.php?id=" . $id . "';
        </script>";
    }

    $stmt->close();

}


// Fetch current data for the admin
$stmt = $konek->prepare("SELECT username, role, id_kelas FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// Jika data tidak ditemukan, alihkan pengguna
if (!$data) {
    echo "<script>alert('Data admin tidak ditemukan.'); window.location.href = 'data_admin.php';</script>";
    exit();
}

// Ambil semua data kelas untuk dropdown
$kelas_result = mysqli_query($konek, "SELECT id, kelas FROM kelas ORDER BY kelas");
$kelas_options = '';
if ($kelas_result) {
    while ($row = mysqli_fetch_assoc($kelas_result)) {
        $selected = ($data['id_kelas'] == $row['id']) ? 'selected' : '';
        $kelas_options .= "<option value=\"" . htmlspecialchars($row['id']) . "\" " . $selected . ">" . htmlspecialchars($row['kelas']) . "</option>";
    }
} else {
    $kelas_options = "<option value=\"\">Gagal memuat data kelas</option>";
}
$konek->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "header.php"; ?>

    <!-- Link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="text/javascript">
        function confirmLogout() {
            if (confirm('Anda yakin ingin logout?')) {
                window.location.href = 'logout.php';
            }
        }
        $(document).ready(function() {
            const roleSelect = $('#role');
            const kelasGroup = $('#kelas-group');

            function toggleKelasDropdown() {
                if (roleSelect.val() === 'wali_kelas') {
                    kelasGroup.show();
                } else {
                    kelasGroup.hide();
                    // Reset nilai kelas saat disembunyikan jika tidak dipilih sebagai wali kelas
                    // $('#id_kelas').val(''); // Menghapus baris ini agar data kelas yang sudah ada tidak terhapus saat berpindah peran
                }
            }

            // Panggil saat halaman dimuat untuk menetapkan status awal
            toggleKelasDropdown();

            // Panggil setiap kali pilihan role berubah
            roleSelect.on('change', toggleKelasDropdown);
        });
    </script>

    <title>Edit Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;400;700&display=swap" rel="stylesheet">
    <style>
        /* Include your styles here */
        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
        }

        body {
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        .header-container {
            width: calc(100% - 269px);
            height: 70px;
            position: fixed;
            top: 0;
            left: 269px;
            background: white;
            border-bottom: 1px #EFF3FF solid;
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
            border-radius: 7px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            border: none;
            cursor: pointer;
            font-size: 14.5px;
            font-weight: 700;
            color: #DC3545;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .logout-button i {
            margin-right: 8px;
            transition: color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #F8D7DA;
            transform: scale(1.05);
        }

        .logout-button:hover i {
            color: #C82333;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            margin-top: 32px;
            margin-left: 269px;
            background: #f9f9f9;
            padding-bottom: 60px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .headline {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            border-bottom: 3px solid #5D87FF;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #333;
            width: 100%;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #5D87FF;
            outline: none;
            box-shadow: 0 0 5px rgba(93, 135, 255, 0.2);
        }

        .form-group .optional-note {
            font-size: 14px;
            color: #999;
            font-style: italic;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            padding: 10px;
            margin-top: 30px;
        }

        .btn-save {
            background-color: #5D87FF;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 12px 24px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block;
            text-align: center;
        }

        .btn-save:hover {
            background-color: #4a6ee0;
        }

        .footer {
            height: 60px;
            padding: 20px;
            background: white;
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
    </style>
</head>
<body>
    <?php include "menu.php"; ?>

    <div class="header-container">
        <div class="header-content">
            <div class="header-title">Edit Admin</div>
            <button class="logout-button" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> LOG OUT
            </button>
        </div>
    </div>

    <div class="main-content">
        <div class="container">
            <h4 class="headline">Edit Admin</h4>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Username" value="<?php echo htmlspecialchars($data['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password Baru <span class="optional-note">(opsional)</span></label>
                    <input type="password" name="password" id="password" placeholder="Password Baru">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="">Pilih Role</option>
                        <option value="admin" <?php echo ($data['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="wali_kelas" <?php echo ($data['role'] == 'wali_kelas') ? 'selected' : ''; ?>>Wali Kelas</option>
                        <option value="user" <?php echo ($data['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    </select>
                </div>
                <div class="form-group" id="kelas-group" style="display: none;">
                    <label for="id_kelas">Pilih Kelas</label>
                    <select name="id_kelas" id="id_kelas">
                        <option value="">Pilih Kelas</option>
                        <?php echo $kelas_options; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save" name="btnUpdate" id="btnUpdate">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <div class="left">2024 © van Derren</div>
        <div class="right">Design & Develop by van Derren</div>
    </div>
</body>
</html>
