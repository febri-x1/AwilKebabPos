<?php
// config/koneksi.php
$host = "localhost";
$user = "root";
$pass = ""; // Biarkan kosong jika default XAMPP
$db   = "pos_kebab";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>