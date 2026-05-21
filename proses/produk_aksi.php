<?php
session_start();
require '../config/session_config.php';

$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require '../config/koneksi.php';

// Cek aksi apa yang diminta oleh form (tambah, edit, hapus)
$aksi = $_POST['aksi'];

if ($aksi == 'tambah') {
    // Tangkap data dari form modal tambah
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $kategori    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga_jual  = $_POST['harga_jual'];
    $status_stok = mysqli_real_escape_string($koneksi, $_POST['status_stok']);
    $allow_topping = isset($_POST['allow_topping']) ? mysqli_real_escape_string($koneksi, $_POST['allow_topping']) : 'ya';

    // Pastikan kolom allow_topping ada di tabel produk, jika belum coba tambahkan
    $col_check = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'allow_topping'");
    if (mysqli_num_rows($col_check) == 0) {
        @mysqli_query($koneksi, "ALTER TABLE produk ADD COLUMN allow_topping ENUM('ya','tidak') DEFAULT 'ya'");
    }

    $query = "INSERT INTO produk (nama_produk, harga_jual, kategori, status_stok, allow_topping) 
              VALUES ('$nama_produk', '$harga_jual', '$kategori', '$status_stok', '$allow_topping')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_produk.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_produk.php?pesan=gagal");
    }
} elseif ($aksi == 'edit') {
    // Tangkap data dari form modal edit
    $id_produk   = $_POST['id_produk'];
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $kategori    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga_jual  = $_POST['harga_jual'];
    $status_stok = mysqli_real_escape_string($koneksi, $_POST['status_stok']);
    $allow_topping = isset($_POST['allow_topping']) ? mysqli_real_escape_string($koneksi, $_POST['allow_topping']) : 'ya';

    // Pastikan kolom allow_topping ada di tabel produk
    $col_check = mysqli_query($koneksi, "SHOW COLUMNS FROM produk LIKE 'allow_topping'");
    if (mysqli_num_rows($col_check) == 0) {
        @mysqli_query($koneksi, "ALTER TABLE produk ADD COLUMN allow_topping ENUM('ya','tidak') DEFAULT 'ya'");
    }

        $query = "UPDATE produk SET 
                                nama_produk = '$nama_produk', 
                                harga_jual = '$harga_jual', 
                                kategori = '$kategori', 
                                status_stok = '$status_stok', 
                                allow_topping = '$allow_topping' 
                            WHERE id_produk = '$id_produk'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_produk.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_produk.php?pesan=gagal");
    }
} elseif ($aksi == 'hapus') {
    // Tangkap ID produk yang akan dihapus
    $id_produk = $_POST['id_produk'];

    $query = "DELETE FROM produk WHERE id_produk = '$id_produk'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_produk.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_produk.php?pesan=gagal");
    }
} else {
    // Jika aksi tidak dikenali, kembalikan ke halaman master
    header("Location: ../admin/master_produk.php");
}
