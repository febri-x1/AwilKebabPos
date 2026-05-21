<?php
session_start();
require '../config/session_config.php';
require '../config/koneksi.php';
require_once '../config/promo_bundling_helper.php';

$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// AUTO: Buat tabel promo_bundling jika belum ada
$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'promo_bundling'");
if (mysqli_num_rows($check_table) === 0) {
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `promo_bundling` (
        `id_bundling` int(11) NOT NULL AUTO_INCREMENT,
        `nama_bundling` varchar(100) NOT NULL,
        `id_produk1` int(11) NOT NULL,
        `jumlah_produk1` int(11) NOT NULL DEFAULT 2,
        `id_produk2` int(11) NOT NULL,
        `jumlah_produk2` int(11) NOT NULL DEFAULT 2,
        `id_produk_gratis` int(11) NOT NULL,
        `jumlah_produk_gratis` int(11) NOT NULL DEFAULT 1,
        `tanggal_mulai` date NOT NULL,
        `tanggal_akhir` date NOT NULL,
        `status` enum('aktif','nonaktif') DEFAULT 'aktif',
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_bundling`),
        KEY `fk_produk1` (`id_produk1`),
        KEY `fk_produk2` (`id_produk2`),
        KEY `fk_produk_gratis` (`id_produk_gratis`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    @mysqli_query($koneksi, $create_table_sql);
}

nonaktifkan_promo_bundling_kadaluarsa($koneksi);

$aksi = $_POST['aksi'] ?? '';

if ($aksi == 'tambah') {
    $nama_bundling = mysqli_real_escape_string($koneksi, $_POST['nama_bundling']);
    $id_produk1 = intval($_POST['id_produk1']);
    $jumlah_produk1 = intval($_POST['jumlah_produk1']);
    $id_produk2 = intval($_POST['id_produk2']);
    $jumlah_produk2 = intval($_POST['jumlah_produk2']);
    $id_produk_gratis = intval($_POST['id_produk_gratis']);
    $jumlah_produk_gratis = intval($_POST['jumlah_produk_gratis']);
    $tanggal_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_akhir = mysqli_real_escape_string($koneksi, $_POST['tanggal_akhir']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    if ($tanggal_akhir < date('Y-m-d')) {
        $status = 'nonaktif';
    }

    if (promo_bundling_sudah_ada($koneksi, $nama_bundling, $id_produk1, $jumlah_produk1, $id_produk2, $jumlah_produk2, $id_produk_gratis, $jumlah_produk_gratis)) {
        header("Location: ../admin/master_bundling.php?pesan=duplikat");
        exit;
    }

    $query = "INSERT INTO promo_bundling 
              (nama_bundling, id_produk1, jumlah_produk1, id_produk2, jumlah_produk2, id_produk_gratis, jumlah_produk_gratis, tanggal_mulai, tanggal_akhir, status) 
              VALUES ('$nama_bundling', '$id_produk1', '$jumlah_produk1', '$id_produk2', '$jumlah_produk2', '$id_produk_gratis', '$jumlah_produk_gratis', '$tanggal_mulai', '$tanggal_akhir', '$status')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_bundling.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_bundling.php?pesan=gagal");
    }
} 
elseif ($aksi == 'edit') {
    $id_bundling = intval($_POST['id_bundling']);
    $nama_bundling = mysqli_real_escape_string($koneksi, $_POST['nama_bundling']);
    $id_produk1 = intval($_POST['id_produk1']);
    $jumlah_produk1 = intval($_POST['jumlah_produk1']);
    $id_produk2 = intval($_POST['id_produk2']);
    $jumlah_produk2 = intval($_POST['jumlah_produk2']);
    $id_produk_gratis = intval($_POST['id_produk_gratis']);
    $jumlah_produk_gratis = intval($_POST['jumlah_produk_gratis']);
    $tanggal_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tanggal_akhir = mysqli_real_escape_string($koneksi, $_POST['tanggal_akhir']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    if ($tanggal_akhir < date('Y-m-d')) {
        $status = 'nonaktif';
    }

    if (promo_bundling_sudah_ada($koneksi, $nama_bundling, $id_produk1, $jumlah_produk1, $id_produk2, $jumlah_produk2, $id_produk_gratis, $jumlah_produk_gratis, $id_bundling)) {
        header("Location: ../admin/master_bundling.php?pesan=duplikat");
        exit;
    }

    $query = "UPDATE promo_bundling 
              SET nama_bundling='$nama_bundling', id_produk1='$id_produk1', jumlah_produk1='$jumlah_produk1', 
                  id_produk2='$id_produk2', jumlah_produk2='$jumlah_produk2', id_produk_gratis='$id_produk_gratis', 
                  jumlah_produk_gratis='$jumlah_produk_gratis', tanggal_mulai='$tanggal_mulai', 
                  tanggal_akhir='$tanggal_akhir', status='$status'
              WHERE id_bundling='$id_bundling'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_bundling.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_bundling.php?pesan=gagal");
    }
} 
elseif ($aksi == 'hapus') {
    $id_bundling = $_POST['id_bundling'];

    $query = "DELETE FROM promo_bundling WHERE id_bundling='$id_bundling'";

    if (mysqli_query($koneksi, $query)) {
        header("Location: ../admin/master_bundling.php?pesan=sukses");
    } else {
        header("Location: ../admin/master_bundling.php?pesan=gagal");
    }
}
?>
