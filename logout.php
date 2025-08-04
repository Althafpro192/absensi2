<?php
session_start();
// Hancurkan semua data sesi
$_SESSION = array();
if (session_id() != "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

// Arahkan ke halaman login
header("Location: login.php");
exit();
?>
