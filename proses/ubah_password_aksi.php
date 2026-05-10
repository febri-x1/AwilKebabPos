<?php
session_start();
require '../config/session_config.php';
require '../config/koneksi.php';

// Cek user sudah login
$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Ambil data dari form
$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$password_konfirmasi = $_POST['password_konfirmasi'] ?? '';
$id_user = $user['id_user'];

// Validasi input
if (empty($password_lama) || empty($password_baru) || empty($password_konfirmasi)) {
    $_SESSION['pesan_error'] = "Semua field harus diisi!";
    header("Location: ../admin/dashboard.php");
    exit;
}

// Validasi panjang password baru
if (strlen($password_baru) < 6) {
    $_SESSION['pesan_error'] = "Password baru minimal 6 karakter!";
    header("Location: ../admin/dashboard.php");
    exit;
}

// Validasi password baru dan konfirmasi sama
if ($password_baru !== $password_konfirmasi) {
    $_SESSION['pesan_error'] = "Password baru dan konfirmasi tidak cocok!";
    header("Location: ../admin/dashboard.php");
    exit;
}

// Ambil password hash dari database
$query = mysqli_query($koneksi, "SELECT password FROM users WHERE id_user = '$id_user'");
if (mysqli_num_rows($query) === 0) {
    $_SESSION['pesan_error'] = "User tidak ditemukan!";
    header("Location: ../admin/dashboard.php");
    exit;
}

$row = mysqli_fetch_assoc($query);

// Verifikasi password lama
if (!password_verify($password_lama, $row['password'])) {
    $_SESSION['pesan_error'] = "Password lama tidak sesuai!";
    header("Location: ../admin/dashboard.php");
    exit;
}

// Hash password baru
$password_baru_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// Update password di database
$update_query = mysqli_query($koneksi, "
    UPDATE users 
    SET password = '$password_baru_hash' 
    WHERE id_user = '$id_user'
");

if ($update_query) {
    $_SESSION['pesan_sukses'] = "Password berhasil diubah! Silakan login kembali.";
    // Logout user setelah ubah password
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php?pesan=password_berhasil_diubah");
    exit;
} else {
    $_SESSION['pesan_error'] = "Gagal mengubah password: " . mysqli_error($koneksi);
    header("Location: ../admin/dashboard.php");
    exit;
}
?>
