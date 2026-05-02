<?php
session_start();
// Halaman ini bisa diakses jika user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

require '../config/koneksi.php';

// Menangkap parameter nota dari URL
if (!isset($_GET['nota'])) {
    die("Nomor struk tidak ditemukan.");
}

$nota = mysqli_real_escape_string($koneksi, $_GET['nota']);

// Mengambil data utama penjualan beserta nama kasirnya
$query_penjualan = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap 
    FROM penjualan p 
    JOIN users u ON p.id_user = u.id_user 
    WHERE p.nomor_struk = '$nota'
");

if (mysqli_num_rows($query_penjualan) == 0) {
    die("Data transaksi tidak ditemukan.");
}

$penjualan = mysqli_fetch_assoc($query_penjualan);
$id_penjualan = $penjualan['id_penjualan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= $nota; ?></title>
    <style>
        /* CSS Khusus untuk Print Thermal 58mm */
        @page { margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 12px; 
            width: 58mm; 
            margin: 0 auto; 
            padding: 10px;
            color: #000;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
        /* Tombol yang tidak akan ikut ter-print */
        .btn-kembali {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #0d6efd;
            color: white;
            text-align: center;
            text-decoration: none;
            font-family: Arial, sans-serif;
            border-radius: 5px;
            font-weight: bold;
            box-sizing: border-box;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<!-- Fungsi onload untuk otomatis memicu print saat halaman terbuka -->
<body onload="window.print()">

    <div class="center">
        <h3 style="margin-bottom: 2px;">AWIL KEBAB</h3>
        <p style="margin-top: 0; font-size: 10px;">
            Jl. Raya Serang Km. 21<br>
            Telp: 0812-3456-7890
        </p>
    </div>

    <div class="line"></div>
    <table>
        <tr>
            <td width="30%">Nota</td>
            <td width="5%">:</td>
            <td><?= $penjualan['nomor_struk']; ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td><?= date('d/m/Y H:i', strtotime($penjualan['tanggal_transaksi'])); ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td><?= $penjualan['nama_lengkap']; ?></td>
        </tr>
    </table>
    <div class="line"></div>

    <table>
        <?php
        // Mengambil detail item yang dibeli dari tabel detail_penjualan dan produk
        $query_detail = mysqli_query($koneksi, "
            SELECT d.*, p.nama_produk 
            FROM detail_penjualan d 
            JOIN produk p ON d.id_produk = p.id_produk 
            WHERE d.id_penjualan = '$id_penjualan'
        ");

        while ($detail = mysqli_fetch_assoc($query_detail)):
        ?>
        <tr>
            <!-- Nama produk memakan 3 kolom (satu baris penuh) -->
            <td colspan="3" class="bold"><?= $detail['nama_produk']; ?></td>
        </tr>
        
        <?php if (!empty($detail['catatan_topping'])): ?>
        <tr>
            <td colspan="3" style="font-size: 10px; padding-left: 10px;">
                + <?= $detail['catatan_topping']; ?>
            </td>
        </tr>
        <?php endif; ?>
        
        <tr>
            <td width="20%"><?= $detail['jumlah']; ?> x</td>
            <td width="40%"><?= number_format($detail['harga_satuan'], 0, ',', '.'); ?></td>
            <td width="40%" class="right"><?= number_format($detail['subtotal'], 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="line"></div>
    <table>
        <tr>
            <td class="bold">Total</td>
            <td class="right bold">Rp <?= number_format($penjualan['total_bayar'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Tunai</td>
            <td class="right">Rp <?= number_format($penjualan['uang_masuk'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">Rp <?= number_format($penjualan['kembalian'], 0, ',', '.'); ?></td>
        </tr>
    </table>
    <div class="line"></div>
    
    <div class="center" style="margin-top: 10px;">
        <p style="font-size: 10px;">
            Terima Kasih Atas Kunjungan Anda<br>
            *** Lunas ***
        </p>
    </div>

    <!-- Tombol navigasi setelah selesai mengeprint -->
    <div class="no-print">
        <a href="transaksi.php" class="btn-kembali">Kembali ke Transaksi</a>
    </div>

</body>
</html>