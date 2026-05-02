<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require '../config/koneksi.php';

$aksi = $_POST['aksi'];

if ($aksi == 'tambah') {
    $nama_topping   = mysqli_real_escape_string($koneksi, $_POST['nama_topping']);
    $harga_tambahan = $_POST['harga_tambahan'];

    $query = "INSERT INTO topping (nama_topping, harga_tambahan) VALUES ('$nama_topping', '$harga_tambahan')";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_topping.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_topping.php?pesan=gagal");
    }
} 
elseif ($aksi == 'edit') {
    $id_topping     = $_POST['id_topping'];
    $nama_topping   = mysqli_real_escape_string($koneksi, $_POST['nama_topping']);
    $harga_tambahan = $_POST['harga_tambahan'];

    $query = "UPDATE topping SET 
                nama_topping = '$nama_topping', 
                harga_tambahan = '$harga_tambahan'
              WHERE id_topping = '$id_topping'";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_topping.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_topping.php?pesan=gagal");
    }
} 
elseif ($aksi == 'hapus') {
    $id_topping = $_POST['id_topping'];

    $query = "DELETE FROM topping WHERE id_topping = '$id_topping'";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_topping.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_topping.php?pesan=gagal");
    }
}
else {
    header("Location: ../admin/master_topping.php");
}
?>