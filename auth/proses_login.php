<?php
session_start();
// Wajib pakai ../ untuk mengakses koneksi.php dari dalam folder auth
require '../config/koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

$query = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");

if (mysqli_num_rows($query) === 1) {
    $row = mysqli_fetch_assoc($query);
    
    // Verifikasi password hash
    if (password_verify($password, $row['password'])) {
        
        // Daftarkan session
        $_SESSION['id_user']      = $row['id_user'];
        $_SESSION['username']     = $row['username'];
        $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
        $_SESSION['role']         = $row['role'];

        // Arahkan ke halaman sesuai role
        if ($row['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else if ($row['role'] == 'kasir') {
            header("Location: ../kasir/transaksi.php");
        }
        exit;
    } else {
        // Password salah
        header("Location: login.php?pesan=gagal");
        exit;
    }
} else {
    // Username tidak ditemukan
    header("Location: login.php?pesan=gagal");
    exit;
}
?>