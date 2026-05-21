<?php
session_start();
require '../config/session_config.php';
header('Content-Type: application/json');

require '../config/koneksi.php';
require_once '../config/promo_bundling_helper.php';

$user_session = get_tab_session();
if (!$user_session) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Akses ditolak']);
    exit;
}

nonaktifkan_promo_bundling_kadaluarsa($koneksi);

$id_bundling = intval($_POST['id_bundling'] ?? 0);
if ($id_bundling <= 0) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'ID bundling tidak valid']);
    exit;
}

$query = mysqli_query($koneksi, "
    SELECT 
        b.*, 
        p1.id_produk AS id_produk1, p1.nama_produk AS nama_produk1, p1.harga_jual AS harga_produk1,
        p2.id_produk AS id_produk2, p2.nama_produk AS nama_produk2, p2.harga_jual AS harga_produk2,
        p3.id_produk AS id_produk_gratis, p3.nama_produk AS nama_produk_gratis, p3.harga_jual AS harga_produk_gratis
    FROM promo_bundling b
    LEFT JOIN produk p1 ON b.id_produk1 = p1.id_produk
    LEFT JOIN produk p2 ON b.id_produk2 = p2.id_produk
    LEFT JOIN produk p3 ON b.id_produk_gratis = p3.id_produk
    WHERE b.id_bundling = '$id_bundling'
    AND b.status = 'aktif'
    AND CURDATE() >= b.tanggal_mulai
    AND CURDATE() <= b.tanggal_akhir
    LIMIT 1
");

if (!$query || mysqli_num_rows($query) === 0) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Bundling tidak ditemukan atau sudah tidak aktif']);
    exit;
}

$b = mysqli_fetch_assoc($query);

$bundling = [
    'id_bundling' => (int)$b['id_bundling'],
    'nama_bundling' => $b['nama_bundling'],
    'id_produk1' => (int)$b['id_produk1'],
    'nama_produk1' => $b['nama_produk1'],
    'harga_produk1' => (int)$b['harga_produk1'],
    'jumlah_produk1' => (int)$b['jumlah_produk1'],
    'id_produk2' => (int)$b['id_produk2'],
    'nama_produk2' => $b['nama_produk2'],
    'harga_produk2' => (int)$b['harga_produk2'],
    'jumlah_produk2' => (int)$b['jumlah_produk2'],
    'id_produk_gratis' => (int)$b['id_produk_gratis'],
    'nama_produk_gratis' => $b['nama_produk_gratis'],
    'harga_produk_gratis' => (int)$b['harga_produk_gratis'],
    'jumlah_produk_gratis' => (int)$b['jumlah_produk_gratis']
];

echo json_encode(['status' => 'sukses', 'bundling' => $bundling]);
