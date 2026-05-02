<?php
session_start();
// Pastikan hanya admin yang bisa mengakses proses ini
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
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

    $query = "INSERT INTO produk (nama_produk, harga_jual, kategori, status_stok) 
              VALUES ('$nama_produk', '$harga_jual', '$kategori', '$status_stok')";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_produk.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_produk.php?pesan=gagal");
    }
} 
elseif ($aksi == 'edit') {
    // Tangkap data dari form modal edit
    $id_produk   = $_POST['id_produk'];
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $kategori    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga_jual  = $_POST['harga_jual'];
    $status_stok = mysqli_real_escape_string($koneksi, $_POST['status_stok']);

    $query = "UPDATE produk SET 
                nama_produk = '$nama_produk', 
                harga_jual = '$harga_jual', 
                kategori = '$kategori', 
                status_stok = '$status_stok' 
              WHERE id_produk = '$id_produk'";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_produk.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_produk.php?pesan=gagal");
    }
} 
elseif ($aksi == 'hapus') {
    // Tangkap ID produk yang akan dihapus
    $id_produk = $_POST['id_produk'];

    $query = "DELETE FROM produk WHERE id_produk = '$id_produk'";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_produk.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_produk.php?pesan=gagal");
    }
}
else {
    // Jika aksi tidak dikenali, kembalikan ke halaman master
    header("Location: ../admin/master_produk.php");
}
?>