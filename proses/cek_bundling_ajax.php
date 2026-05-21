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

// Ambil bundling yang aktif
$tgl_sekarang = date('Y-m-d');
$bundling_query = mysqli_query($koneksi, "
    SELECT * FROM promo_bundling 
    WHERE status = 'aktif' 
    AND '$tgl_sekarang' >= tanggal_mulai 
    AND '$tgl_sekarang' <= tanggal_akhir
");

$bundling_list = [];
while ($row = mysqli_fetch_assoc($bundling_query)) {
    $bundling_list[] = $row;
}

// Ambil data keranjang dari POST
$keranjang = json_decode($_POST['keranjang'] ?? '[]', true);

$bundling_terdeteksi = [];
$produk_gratis_ditambahkan = [];

// Deteksi bundling yang cocok
foreach ($bundling_list as $bundling) {
    $id_produk1 = $bundling['id_produk1'];
    $jumlah_produk1 = $bundling['jumlah_produk1'];
    $id_produk2 = $bundling['id_produk2'];
    $jumlah_produk2 = $bundling['jumlah_produk2'];
    $id_produk_gratis = $bundling['id_produk_gratis'];
    $jumlah_produk_gratis = $bundling['jumlah_produk_gratis'];

    // Hitung jumlah produk dalam keranjang
    $qty_produk1 = 0;
    $qty_produk2 = 0;

    foreach ($keranjang as $item) {
        if ($item['id_produk'] == $id_produk1) {
            $qty_produk1 += intval($item['jumlah']);
        }
        if ($item['id_produk'] == $id_produk2) {
            $qty_produk2 += intval($item['jumlah']);
        }
    }

    // Cek apakah bundling terpenuhi
    if ($qty_produk1 >= $jumlah_produk1 && $qty_produk2 >= $jumlah_produk2) {
        // Hitung berapa kali bundling terpenuhi
        $kali_bundling = min(
            intval($qty_produk1 / $jumlah_produk1),
            intval($qty_produk2 / $jumlah_produk2)
        );

        $total_produk_gratis = $kali_bundling * $jumlah_produk_gratis;

        // Ambil info produk gratis
        $produk_gratis_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE id_produk = '$id_produk_gratis'");
        $produk_gratis_data = mysqli_fetch_assoc($produk_gratis_query);

        $bundling_terdeteksi[] = [
            'id_bundling' => $bundling['id_bundling'],
            'nama_bundling' => $bundling['nama_bundling'],
            'kali_bundling' => $kali_bundling,
            'produk_gratis_id' => $id_produk_gratis,
            'produk_gratis_nama' => $produk_gratis_data['nama_produk'],
            'produk_gratis_qty' => $total_produk_gratis,
            'produk_gratis_harga' => $produk_gratis_data['harga']
        ];

        $produk_gratis_ditambahkan[] = [
            'id_produk' => $id_produk_gratis,
            'nama_produk' => $produk_gratis_data['nama_produk'],
            'jumlah' => $total_produk_gratis,
            'harga_satuan' => $produk_gratis_data['harga'],
            'subtotal' => 0, // Gratis
            'keterangan' => 'GRATIS - ' . $bundling['nama_bundling']
        ];
    }
}

echo json_encode([
    'status' => 'sukses',
    'bundling_terdeteksi' => $bundling_terdeteksi,
    'produk_gratis' => $produk_gratis_ditambahkan
]);
?>
