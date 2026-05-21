<?php
// Jalankan script ini via browser atau CLI untuk menambahkan kolom `allow_topping` pada tabel `produk`.
// Akses: http://localhost/AppsAwilKebab/scripts/add_allow_topping.php

require __DIR__ . '/../config/koneksi.php';

// Cek apakah kolom sudah ada
$check = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'allow_topping'");
if (!$check) {
    echo "Gagal mengecek kolom: " . mysqli_error($koneksi);
    exit;
}

if (mysqli_num_rows($check) > 0) {
    echo "Kolom `allow_topping` sudah ada di tabel `produk`.\n";
    exit;
}

$sql = "ALTER TABLE produk ADD COLUMN allow_topping ENUM('ya','tidak') DEFAULT 'ya'";
if (mysqli_query($koneksi, $sql)) {
    echo "Berhasil menambahkan kolom `allow_topping` pada tabel `produk`.\n";
} else {
    echo "Gagal menambahkan kolom: " . mysqli_error($koneksi) . "\n";
}

?>