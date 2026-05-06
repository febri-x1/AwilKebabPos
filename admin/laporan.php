<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/header.php';
// Sembunyikan navbar saat mode print
echo '<div class="d-print-none">';
include '../includes/navbar.php';
echo '</div>';

require '../config/koneksi.php';

// Menentukan rentang tanggal (Default: Awal bulan sampai akhir bulan ini)
$tgl_mulai   = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-t');

// 1. Menghitung Total Omzet (Pendapatan Kotor)
$query_omzet = mysqli_query($koneksi, "
    SELECT SUM(total_bayar) AS total_omzet, COUNT(id_penjualan) AS jumlah_transaksi 
    FROM penjualan 
    WHERE DATE(tanggal_transaksi) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
");
$data_omzet = mysqli_fetch_assoc($query_omzet);
$omzet = $data_omzet['total_omzet'] ? $data_omzet['total_omzet'] : 0;
$jumlah_trx = $data_omzet['jumlah_transaksi'] ? $data_omzet['jumlah_transaksi'] : 0;

// 2. Menghitung Total Pengeluaran (Modal Bahan & Operasional)
$query_pengeluaran = mysqli_query($koneksi, "
    SELECT SUM(jumlah_biaya) AS total_keluar 
    FROM pengeluaran 
    WHERE tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'
");
$data_pengeluaran = mysqli_fetch_assoc($query_pengeluaran);
$pengeluaran = $data_pengeluaran['total_keluar'] ? $data_pengeluaran['total_keluar'] : 0;

// 3. Menghitung Laba Bersih (Omzet - Pengeluaran)
$laba_bersih = $omzet - $pengeluaran;
?>

<style>
    /* Menyembunyikan elemen tertentu saat dicetak */
    @media print {
        .d-print-none { display: none !important; }
        body { background-color: white !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>

<div class="content-wrapper" style="margin-left: 250px; width: calc(100% - 250px); padding: 20px; min-height: 100vh; background-color: #f8f9fa;">
    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <h3 class="fw-bold">Laporan Keuangan</h3>
        <button onclick="window.print()" class="btn btn-secondary fw-bold">
            Cetak Laporan (Print)
        </button>
    </div>

    <!-- Header Surat (Hanya tampil saat di-print) -->
    <div class="text-center d-none d-print-block mb-4">
        <h2 class="fw-bold mb-0">POS KEBAB</h2>
        <p>Laporan Pendapatan dan Pengeluaran<br>
        Periode: <?= date('d/m/Y', strtotime($tgl_mulai)); ?> s.d <?= date('d/m/Y', strtotime($tgl_selesai)); ?></p>
        <hr style="border-top: 2px solid black;">
    </div>

    <!-- Form Filter Tanggal -->
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai; ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary fw-bold w-100">Tampilkan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan Kartu -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Pendapatan (Omzet)</h6>
                    <h3 class="fw-bold">Rp <?= number_format($omzet, 0, ',', '.'); ?></h3>
                    <small><?= $jumlah_trx; ?> Transaksi Penjualan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Total Pengeluaran (Modal)</h6>
                    <h3 class="fw-bold">Rp <?= number_format($pengeluaran, 0, ',', '.'); ?></h3>
                    <small>Belanja Bahan & Operasional</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-white <?= ($laba_bersih >= 0) ? 'bg-primary' : 'bg-warning text-dark'; ?> shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Laba / Rugi Bersih</h6>
                    <h3 class="fw-bold">Rp <?= number_format($laba_bersih, 0, ',', '.'); ?></h3>
                    <small><?= ($laba_bersih >= 0) ? 'Keuntungan' : 'Kerugian'; ?> Usaha</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Tabel Ringkasan Penjualan -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white fw-bold">Rincian Penjualan Terakhir</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Struk</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil 10 transaksi terakhir di rentang waktu tersebut
                            $q_list_penjualan = mysqli_query($koneksi, "
                                SELECT tanggal_transaksi, nomor_struk, total_bayar 
                                FROM penjualan 
                                WHERE DATE(tanggal_transaksi) BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
                                ORDER BY tanggal_transaksi DESC LIMIT 10
                            ");
                            
                            if(mysqli_num_rows($q_list_penjualan) > 0):
                                while($row_p = mysqli_fetch_assoc($q_list_penjualan)):
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row_p['tanggal_transaksi'])); ?></td>
                                <td><?= $row_p['nomor_struk']; ?></td>
                                <td class="text-end">Rp <?= number_format($row_p['total_bayar'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr><td colspan="3" class="text-center text-muted">Belum ada transaksi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Ringkasan Pengeluaran -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white fw-bold">Rincian Pengeluaran Terakhir</div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Item</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil 10 pengeluaran terakhir di rentang waktu tersebut
                            $q_list_pengeluaran = mysqli_query($koneksi, "
                                SELECT tanggal, nama_item_bahan, jumlah_biaya 
                                FROM pengeluaran 
                                WHERE tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
                                ORDER BY tanggal DESC LIMIT 10
                            ");

                            if(mysqli_num_rows($q_list_pengeluaran) > 0):
                                while($row_k = mysqli_fetch_assoc($q_list_pengeluaran)):
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row_k['tanggal'])); ?></td>
                                <td><?= $row_k['nama_item_bahan']; ?></td>
                                <td class="text-end text-danger">Rp <?= number_format($row_k['jumlah_biaya'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr><td colspan="3" class="text-center text-muted">Belum ada pengeluaran</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
echo '<div class="d-print-none">';
include '../includes/footer.php'; 
echo '</div>';
?>