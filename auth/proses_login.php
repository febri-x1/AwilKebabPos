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
        if ($row['role'] == 'admin') {
            $_SESSION['admin'] = [
                'id_user'      => $row['id_user'],
                'username'     => $row['username'],
                'nama_lengkap' => $row['nama_lengkap'],
                'role'         => 'admin'
            ];
        } else if ($row['role'] == 'kasir') {
            $_SESSION['kasir'] = [
                'id_user'      => $row['id_user'],
                'username'     => $row['username'],
                'nama_lengkap' => $row['nama_lengkap'],
                'role'         => 'kasir'
            ];
        }

        // Juga set variabel session level-atas untuk kompatibilitas cek session lain
        $_SESSION['id_user']      = $row['id_user'];
        $_SESSION['username']     = $row['username'];
        $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
        $_SESSION['role']         = $row['role'];

        // Buat token per-tab agar tiap tab bisa menyimpan sesi role sendiri
        $tab_token = bin2hex(random_bytes(8));
        $_SESSION['tabs'][$tab_token] = [
            'id_user' => $row['id_user'],
            'username' => $row['username'],
            'nama_lengkap' => $row['nama_lengkap'],
            'role' => $row['role']
        ];

        // Arahkan ke halaman sesuai role, sertakan token tab di URL
        if ($row['role'] == 'admin') {
            header("Location: ../admin/dashboard.php?tab={$tab_token}");
        } else if ($row['role'] == 'kasir') {
            header("Location: ../kasir/transaksi.php?tab={$tab_token}");
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