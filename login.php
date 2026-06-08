<?php
session_start();

// Koneksi ke database
$host = "192.168.20.95";     // Ganti dengan host database Anda
$user = "root";          // Ganti dengan username database Anda
$pass = "tefa2025bisa";              // Ganti dengan password database Anda
$dbname = "absenrfid";   // Ganti dengan nama database Anda

// $host = "localhost";     // Ganti dengan host database Anda
// $user = "althaf";          // Ganti dengan username database Anda
// $pass = "160907";              // Ganti dengan password database Anda
// $dbname = "absenrfid";

$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Ambil data user berdasarkan username
    $stmt = $conn->prepare("SELECT id, password_hash, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $input_username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $password_hash, $role);
    $stmt->fetch();
    
    if ($stmt->num_rows > 0 && password_verify($input_password, $password_hash)) {
        // Password benar
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;

        // Arahkan ke halaman berdasarkan role
        if ($role == 'admin') {
            header("Location: index.php"); // Halaman dashboard admin
        } elseif ($role == 'user') {
            header("Location: user_dashboard.php"); // Halaman dashboard user
        } elseif ($role == 'wali_kelas') {
        header("Location: walikelas/datasiswa_walkel.php"); // Halaman dashboard wali kelas
    }
        exit();
    } else {
        $error = 'Username atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #0e0e0e;
            color: #c7c7c7;
            background-image: url('https://i.ytimg.com/vi/YiCKV57NBQw/maxresdefault.jpg');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }
        .login-container {
            background: rgba(0, 0, 0, 0.85);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
            max-width: 400px;
            width: 100%;
            position: relative;
            z-index: 1;
            opacity: 0;
            transition: opacity 1s;
        }
        .login-container h2 {
            margin: 0 0 20px;
            font-size: 28px;
            color: #05c0d4;
            text-align: center;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 1px solid #444;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #1c1c1c;
            color: #c7c7c7;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .login-container input:focus {
            border-color: #05c0d4;
            box-shadow: 0 0 8px rgba(5, 192, 212, 0.4);
            outline: none;
        }
        .login-container button {
            width: 100%;
            padding: 14px;
            background-color: #05c0d4;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        .login-container button:hover {
            background-color: #048a9b;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.6);
        }
        .error {
            color: #ff4757;
            margin: 10px 0;
            text-align: center;
        }
        .welcome-message {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 36px;
            color: #FFFFFF;
            font-weight: 900;
            text-align: center;
            opacity: 1;
            animation: fadeInSlide 1.5s forwards, glow 1.5s infinite alternate;
        }
        @keyframes glow {
            from {
                text-shadow: 0 0 10px rgba(5, 192, 212, 0.8), 0 0 20px rgba(5, 192, 212, 0.6);
            }
            to {
                text-shadow: 0 0 20px rgba(5, 192, 212, 1), 0 0 30px rgba(5, 192, 212, 0.8);
            }
        }
        @keyframes fadeInSlide {
            from { opacity: 0; transform: translate(-50%, -20%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            }
            .login-container h2 {
                font-size: 22px;
            }
            .login-container button {
                font-size: 14px;
            }
            .welcome-message {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-message" id="welcome-message">Welcome Back!</div>
    <div class="login-container" id="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('welcome-message').style.opacity = 0;
                setTimeout(function() {
                    document.getElementById('welcome-message').style.display = 'none';
                    document.getElementById('login-container').style.opacity = 1;
                }, 1000); // Adjust this delay to control how long the welcome message is shown
            }, 2000); // Adjust this delay to control how long the welcome message is shown before disappearing
        });
    </script>
</body>
</html>
