<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require '../config/koneksi.php';

$aksi = $_POST['aksi'];

if ($aksi == 'tambah') {
    $tanggal         = $_POST['tanggal'];
    $nama_item_bahan = mysqli_real_escape_string($koneksi, $_POST['nama_item_bahan']);
    $kategori_bahan  = mysqli_real_escape_string($koneksi, $_POST['kategori_bahan']);
    $jumlah_biaya    = $_POST['jumlah_biaya'];
    $keterangan      = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $query = "INSERT INTO pengeluaran (tanggal, nama_item_bahan, kategori_bahan, jumlah_biaya, keterangan) 
              VALUES ('$tanggal', '$nama_item_bahan', '$kategori_bahan', '$jumlah_biaya', '$keterangan')";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/pengeluaran.php?pesan=sukses");
    } else {
        header("Location: ../admin/pengeluaran.php?pesan=gagal");
    }
} 
elseif ($aksi == 'edit') {
    $id_pengeluaran  = $_POST['id_pengeluaran'];
    $tanggal         = $_POST['tanggal'];
    $nama_item_bahan = mysqli_real_escape_string($koneksi, $_POST['nama_item_bahan']);
    $kategori_bahan  = mysqli_real_escape_string($koneksi, $_POST['kategori_bahan']);
    $jumlah_biaya    = $_POST['jumlah_biaya'];
    $keterangan      = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $query = "UPDATE pengeluaran SET 
                tanggal = '$tanggal',
                nama_item_bahan = '$nama_item_bahan', 
                kategori_bahan = '$kategori_bahan', 
                jumlah_biaya = '$jumlah_biaya',
                keterangan = '$keterangan'
              WHERE id_pengeluaran = '$id_pengeluaran'";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/pengeluaran.php?pesan=sukses");
    } else {
        header("Location: ../admin/pengeluaran.php?pesan=gagal");
    }
} 
elseif ($aksi == 'hapus') {
    $id_pengeluaran = $_POST['id_pengeluaran'];

    $query = "DELETE FROM pengeluaran WHERE id_pengeluaran = '$id_pengeluaran'";
              
    if(mysqli_query($koneksi, $query)) {
        header("Location: ../admin/pengeluaran.php?pesan=sukses");
    } else {
        header("Location: ../admin/pengeluaran.php?pesan=gagal");
    }
}
else {
    header("Location: ../admin/pengeluaran.php");
}
?>