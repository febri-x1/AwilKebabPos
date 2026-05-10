<?php
session_start();
require '../config/session_config.php';
// Wajib set header JSON karena JavaScript mengharapkan balasan berupa JSON
header('Content-Type: application/json');

// Cek keamanan memakai sesi per-tab
$user_session = get_tab_session();
if (!$user_session) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Akses ditolak. Silakan login kembali.']);
    exit;
}

require '../config/koneksi.php';

// Menangkap data mentah JSON dari request fetch API
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validasi jika keranjang kosong
if (!$data || empty($data['keranjang'])) {
    echo json_encode(['status' => 'gagal', 'pesan' => 'Data keranjang kosong atau format tidak valid.']);
    exit;
}

// Ekstrak data utama
$total_bayar = $data['total_bayar'];
$uang_masuk  = $data['uang_masuk'];
$kembalian   = $data['kembalian'];
$id_user     = $user_session['id_user'];

// Generate Nomor Struk Unik (Format: KBB-TahunBulanHari-JamMenitDetik)
$nomor_struk = 'KBB-' . date('Ymd-His');

// Memulai Transaksi Database
mysqli_begin_transaction($koneksi);

try {
    // 1. Simpan ke tabel penjualan terlebih dahulu
    $query_penjualan = "INSERT INTO penjualan (nomor_struk, total_bayar, uang_masuk, kembalian, id_user) 
                        VALUES ('$nomor_struk', '$total_bayar', '$uang_masuk', '$kembalian', '$id_user')";

    if (!mysqli_query($koneksi, $query_penjualan)) {
        throw new Exception('Gagal menyimpan data transaksi utama.');
    }

    // Ambil id_penjualan yang baru saja digenerate oleh MySQL
    $id_penjualan = mysqli_insert_id($koneksi);

    // 2. Looping keranjang dan simpan satu per satu ke detail_penjualan
    foreach ($data['keranjang'] as $item) {
        $id_produk    = $item['id_produk'];
        // Pastikan aman dari tanda kutip pada nama topping
        $topping      = mysqli_real_escape_string($koneksi, $item['topping']);
        $jumlah       = $item['jumlah'];
        $harga_satuan = $item['harga_satuan'];
        $subtotal     = $item['subtotal'];

        $query_detail = "INSERT INTO detail_penjualan (id_penjualan, id_produk, catatan_topping, jumlah, harga_satuan, subtotal) 
                         VALUES ('$id_penjualan', '$id_produk', '$topping', '$jumlah', '$harga_satuan', '$subtotal')";

        if (!mysqli_query($koneksi, $query_detail)) {
            throw new Exception('Gagal menyimpan detail produk.');
        }
    }

    // Jika kode sampai sini, artinya semua proses INSERT berhasil.
    // Commit/Patenkan perubahan ke database.
    mysqli_commit($koneksi);

    // Berikan respon sukses dan kembalikan nomor struk ke JavaScript
    echo json_encode([
        'status' => 'sukses',
        'nomor_struk' => $nomor_struk
    ]);
} catch (Exception $e) {
    // Jika ada salah satu yang gagal, batalkan semua perubahan (Rollback)
    mysqli_rollback($koneksi);

    // Berikan respon error ke JavaScript
    echo json_encode([
        'status' => 'gagal',
        'pesan' => $e->getMessage()
    ]);
}
