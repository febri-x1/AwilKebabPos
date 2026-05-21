<?php
session_start();
require '../config/session_config.php';

$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require '../config/koneksi.php';
require_once '../config/promo_helper.php';

nonaktifkan_promo_produk_kadaluarsa($koneksi);
pastikan_promo_produk_unik($koneksi);

$aksi = $_POST['aksi'];

if ($aksi == 'tambah') {
    $id_produk = intval($_POST['id_produk']);
    $jenis_diskon = mysqli_real_escape_string($koneksi, $_POST['jenis_diskon']);
    $diskon_persen = ($jenis_diskon == 'persen') ? mysqli_real_escape_string($koneksi, $_POST['diskon_persen']) : NULL;
    $diskon_nominal = ($jenis_diskon == 'nominal') ? mysqli_real_escape_string($koneksi, $_POST['diskon_nominal']) : NULL;
    $tanggal_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_akhir = mysqli_real_escape_string($koneksi, $_POST['tanggal_akhir']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Satu produk hanya boleh punya satu data promo.
    // Jika ingin promo lagi, perbarui tanggal/status dari data promo yang sudah ada.
    $check_q = "SELECT id_promo FROM promo WHERE id_produk = '$id_produk' LIMIT 1";
    $check_r = mysqli_query($koneksi, $check_q);
    if (!$check_r || mysqli_num_rows($check_r) > 0) {
        header("Location: ../admin/master_promo.php?pesan=duplikat");
        exit;
    }

    $query = "INSERT INTO promo (id_produk, diskon_persen, diskon_nominal, tanggal_mulai, tanggal_akhir, status)
              VALUES ('$id_produk', " . ($diskon_persen ? "'$diskon_persen'" : "NULL") . ", " . ($diskon_nominal ? "'$diskon_nominal'" : "NULL") . ", '$tanggal_mulai', '$tanggal_akhir', '$status')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_promo.php?pesan=sukses");
    } elseif (mysqli_errno($koneksi) == 1062) {
        header("Location: ../admin/master_promo.php?pesan=duplikat");
    } else {
        header("Location: ../admin/master_promo.php?pesan=gagal");
    }
} elseif ($aksi == 'edit') {
    $id_promo = mysqli_real_escape_string($koneksi, $_POST['id_promo']);
    $id_produk = intval($_POST['id_produk']);
    $jenis_diskon = mysqli_real_escape_string($koneksi, $_POST['jenis_diskon']);
    $diskon_persen = ($jenis_diskon == 'persen') ? mysqli_real_escape_string($koneksi, $_POST['diskon_persen']) : NULL;
    $diskon_nominal = ($jenis_diskon == 'nominal') ? mysqli_real_escape_string($koneksi, $_POST['diskon_nominal']) : NULL;
    $tanggal_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_akhir = mysqli_real_escape_string($koneksi, $_POST['tanggal_akhir']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Cegah edit promo dipindahkan ke produk yang sudah punya data promo lain.
    $check_q = "SELECT id_promo FROM promo WHERE id_produk = '$id_produk' AND id_promo != '$id_promo' LIMIT 1";
    $check_r = mysqli_query($koneksi, $check_q);
    if (!$check_r || mysqli_num_rows($check_r) > 0) {
        header("Location: ../admin/master_promo.php?pesan=duplikat");
        exit;
    }

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
    } elseif (mysqli_errno($koneksi) == 1062) {
        header("Location: ../admin/master_promo.php?pesan=duplikat");
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
