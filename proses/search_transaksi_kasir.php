<?php
session_start();
require '../config/session_config.php';
header('Content-Type: application/json');

$user_session = get_tab_session();
if (!$user_session || !in_array(($user_session['role'] ?? ''), ['kasir', 'admin'])) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}

require '../config/koneksi.php';

$keyword = trim($_GET['q'] ?? '');
$keyword_sql = mysqli_real_escape_string($koneksi, $keyword);
$like = '%' . $keyword_sql . '%';

$where = '';
if ($keyword !== '') {
    $where = "
        WHERE p.nomor_struk LIKE '$like'
        OR DATE_FORMAT(p.tanggal_transaksi, '%d/%m/%Y %H:%i') LIKE '$like'
        OR DATE_FORMAT(p.tanggal_transaksi, '%Y-%m-%d') LIKE '$like'
        OR u.nama_lengkap LIKE '$like'
        OR EXISTS (
            SELECT 1
            FROM detail_penjualan dp2
            JOIN produk pr2 ON dp2.id_produk = pr2.id_produk
            WHERE dp2.id_penjualan = p.id_penjualan
            AND pr2.nama_produk LIKE '$like'
        )
    ";
}

$query = mysqli_query($koneksi, "
    SELECT
        p.id_penjualan,
        p.nomor_struk,
        p.tanggal_transaksi,
        p.total_bayar,
        u.nama_lengkap AS nama_kasir,
        COALESCE(SUM(dp.jumlah), 0) AS jumlah_item
    FROM penjualan p
    LEFT JOIN users u ON p.id_user = u.id_user
    LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
    $where
    GROUP BY p.id_penjualan, p.nomor_struk, p.tanggal_transaksi, p.total_bayar, u.nama_lengkap
    ORDER BY p.tanggal_transaksi DESC
    LIMIT 30
");

if (!$query) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Gagal mencari transaksi.']);
    exit;
}

$transaksi = [];
while ($row = mysqli_fetch_assoc($query)) {
    $transaksi[] = $row;
}

echo json_encode([
    'status' => 'sukses',
    'transaksi' => $transaksi
]);
