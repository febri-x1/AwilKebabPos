<?php
session_start();
require '../config/session_config.php';
header('Content-Type: application/json');

$user_session = get_tab_session();
if (!$user_session || ($user_session['role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Akses ditolak']);
    exit;
}

require '../config/koneksi.php';

$nomor = mysqli_real_escape_string($koneksi, $_POST['nomor_struk'] ?? '');
if (!$nomor) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Nomor struk belum diberikan']);
    exit;
}

// Ambil data penjualan
$q = mysqli_query($koneksi, "SELECT p.*, u.nama_lengkap AS petugas FROM penjualan p LEFT JOIN users u ON p.id_user = u.id_user WHERE p.nomor_struk = '$nomor' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Transaksi tidak ditemukan']);
    exit;
}
$pen = mysqli_fetch_assoc($q);

// Ambil detail
$qd = mysqli_query($koneksi, "SELECT dp.*, pr.nama_produk FROM detail_penjualan dp LEFT JOIN produk pr ON dp.id_produk = pr.id_produk WHERE dp.id_penjualan = '{$pen['id_penjualan']}'");
$details = [];
while($r = mysqli_fetch_assoc($qd)){
    $details[] = $r;
}

echo json_encode(['status' => 'sukses', 'penjualan' => $pen, 'detail' => $details]);
?>