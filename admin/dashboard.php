<?php
// admin/dashboard.php
session_start();

// Gembok Keamanan: Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, tendang ke halaman login
    header("Location: ../auth/login.php");
    exit;
}

// Memanggil header
include '../includes/header.php';
// Memanggil navbar
include '../includes/navbar.php';

// Menghubungkan ke database untuk mengambil ringkasan (query ini akan disempurnakan nanti)
require '../config/koneksi.php';

// Contoh Query untuk menghitung jumlah transaksi hari ini
$tanggal_hari_ini = date('Y-m-d');
$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(id_penjualan) as total_transaksi, SUM(total_bayar) as omzet_hari_ini FROM penjualan WHERE DATE(tanggal_transaksi) = '$tanggal_hari_ini'");
$data_hari_ini = mysqli_fetch_assoc($query_transaksi);

$total_trx = $data_hari_ini['total_transaksi'] ? $data_hari_ini['total_transaksi'] : 0;
$omzet_hari_ini = $data_hari_ini['omzet_hari_ini'] ? $data_hari_ini['omzet_hari_ini'] : 0;
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="fw-bold">Dashboard Admin</h2>
            <p class="text-muted">Ringkasan performa penjualan hari ini: <?php echo date('d F Y'); ?></p>
        </div>
    </div>

    <div class="row">
        <!-- Kartu Jumlah Transaksi -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Transaksi Hari Ini</h5>
                    <h2 class="display-5 fw-bold"><?php echo $total_trx; ?></h2>
                    <p class="card-text">Total struk tercetak</p>
                </div>
            </div>
        </div>

        <!-- Kartu Omzet Penjualan -->
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Omzet Hari Ini</h5>
                    <h2 class="display-6 fw-bold">Rp <?php echo number_format($omzet_hari_ini, 0, ',', '.'); ?></h2>
                    <p class="card-text">Total kotor pendapatan</p>
                </div>
            </div>
        </div>

        <!-- Kartu Kasir Aktif -->
        <div class="col-md-4 mb-3">
            <div class="card text-dark bg-warning shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Akses Cepat</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="master_produk.php" class="btn btn-dark">Kelola Menu Kebab</a>
                        <a href="pengeluaran.php" class="btn btn-outline-dark">Catat Pengeluaran</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Memanggil footer
include '../includes/footer.php'; 
?>