<?php
require __DIR__ . '/../config/koneksi.php';

$username = 'testkasir';
$password_plain = 'test123';
$nama_lengkap = 'Test Kasir';
$role = 'kasir';

$hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Cek apakah user sudah ada
$check = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
if (!$check) {
    echo "Query error: " . mysqli_error($koneksi);
    exit;
}

if (mysqli_num_rows($check) > 0) {
    // Update password dan role
    $q = "UPDATE users SET password='$hash', nama_lengkap='$nama_lengkap', role='$role' WHERE username='$username'";
    if (mysqli_query($koneksi, $q)) {
        echo "User '$username' updated.\n";
    } else {
        echo "Gagal update user: " . mysqli_error($koneksi) . "\n";
    }
} else {
    // Insert baru
    $q = "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('$username', '$hash', '$nama_lengkap', '$role')";
    if (mysqli_query($koneksi, $q)) {
        echo "User '$username' created.\n";
    } else {
        echo "Gagal create user: " . mysqli_error($koneksi) . "\n";
    }
}

?>