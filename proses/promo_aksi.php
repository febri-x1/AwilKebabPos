<?php
session_start();
require '../config/session_config.php';

$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require '../config/koneksi.php';

$aksi = $_POST['aksi'];

if ($aksi == 'tambah') {
    $id_produk = $_POST['id_produk'];
    $jenis_diskon = $_POST['jenis_diskon'];
    $diskon_persen = ($jenis_diskon == 'persen') ? $_POST['diskon_persen'] : NULL;
    $diskon_nominal = ($jenis_diskon == 'nominal') ? $_POST['diskon_nominal'] : NULL;
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $status = $_POST['status'];

    $query = "INSERT INTO promo (id_produk, diskon_persen, diskon_nominal, tanggal_mulai, tanggal_akhir, status)
              VALUES ('$id_produk', " . ($diskon_persen ? "'$diskon_persen'" : "NULL") . ", " . ($diskon_nominal ? "'$diskon_nominal'" : "NULL") . ", '$tanggal_mulai', '$tanggal_akhir', '$status')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_promo.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_promo.php?pesan=gagal");
    }
} elseif ($aksi == 'edit') {
    $id_promo = $_POST['id_promo'];
    $id_produk = $_POST['id_produk'];
    $jenis_diskon = $_POST['jenis_diskon'];
    $diskon_persen = ($jenis_diskon == 'persen') ? $_POST['diskon_persen'] : NULL;
    $diskon_nominal = ($jenis_diskon == 'nominal') ? $_POST['diskon_nominal'] : NULL;
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_akhir = $_POST['tanggal_akhir'];
    $status = $_POST['status'];

    $query = "UPDATE promo SET
                id_produk = '$id_produk',
                diskon_persen = " . ($diskon_persen ? "'$diskon_persen'" : "NULL") . ",
                diskon_nominal = " . ($diskon_nominal ? "'$diskon_nominal'" : "NULL") . ",
                tanggal_mulai = '$tanggal_mulai',
                tanggal_akhir = '$tanggal_akhir',
                status = '$status'
              WHERE id_promo = '$id_promo'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_promo.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_promo.php?pesan=gagal");
    }
} elseif ($aksi == 'hapus') {
    $id_promo = $_POST['id_promo'];

    $query = "DELETE FROM promo WHERE id_promo = '$id_promo'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_promo.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_promo.php?pesan=gagal");
    }
} else {
    header("Location: ../admin/master_promo.php");
}
