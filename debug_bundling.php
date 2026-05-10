<?php
session_start();
require './config/session_config.php';
require './config/koneksi.php';

// Cek user
$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    echo '<div class="alert alert-danger">❌ Hanya admin yang bisa akses halaman ini</div>';
    exit;
}

include './includes/header.php';
?>

<div style="margin: 30px; max-width: 1000px;">
    <h2>🔍 DEBUG: Cek Data Bundling</h2>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            Status Tabel
        </div>
        <div class="card-body">
            <?php
            $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'promo_bundling'");
            if (mysqli_num_rows($check_table) > 0) {
                echo '<p class="text-success">✅ Tabel <strong>promo_bundling</strong> sudah ada</p>';
            } else {
                echo '<p class="text-danger">❌ Tabel <strong>promo_bundling</strong> belum ada</p>';
            }
            ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-info text-white fw-bold">
            Data Bundling di Database
        </div>
        <div class="card-body">
            <?php
            $query = mysqli_query($koneksi, "SELECT * FROM promo_bundling");
            $count = mysqli_num_rows($query);
            
            if ($count > 0) {
                echo "<p class=\"text-success\">✅ Ditemukan <strong>$count</strong> data bundling</p>";
                echo '<table class="table table-sm table-bordered">';
                echo '<tr><th>ID</th><th>Nama</th><th>Produk1</th><th>Qty1</th><th>Produk2</th><th>Qty2</th><th>Gratis</th><th>QtyGratis</th><th>Status</th><th>Tgl Mulai</th><th>Tgl Akhir</th></tr>';
                
                while ($row = mysqli_fetch_assoc($query)) {
                    echo '<tr>';
                    echo '<td>' . $row['id_bundling'] . '</td>';
                    echo '<td>' . $row['nama_bundling'] . '</td>';
                    echo '<td>' . $row['id_produk1'] . '</td>';
                    echo '<td>' . $row['jumlah_produk1'] . '</td>';
                    echo '<td>' . $row['id_produk2'] . '</td>';
                    echo '<td>' . $row['jumlah_produk2'] . '</td>';
                    echo '<td>' . $row['id_produk_gratis'] . '</td>';
                    echo '<td>' . $row['jumlah_produk_gratis'] . '</td>';
                    echo '<td><span class="badge bg-' . ($row['status'] == 'aktif' ? 'success' : 'secondary') . '">' . $row['status'] . '</span></td>';
                    echo '<td>' . $row['tanggal_mulai'] . '</td>';
                    echo '<td>' . $row['tanggal_akhir'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
            } else {
                echo '<p class="text-danger">❌ Belum ada data bundling</p>';
            }
            ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark fw-bold">
            Cek Tanggal Promo
        </div>
        <div class="card-body">
            <?php
            $tgl_hari_ini = date('Y-m-d');
            $query_aktif = mysqli_query($koneksi, "
                SELECT * FROM promo_bundling 
                WHERE status = 'aktif' 
                AND '$tgl_hari_ini' >= tanggal_mulai 
                AND '$tgl_hari_ini' <= tanggal_akhir
            ");
            $count_aktif = mysqli_num_rows($query_aktif);
            
            echo "<p>Hari ini: <strong>$tgl_hari_ini</strong></p>";
            echo "<p>Bundling yang aktif hari ini: <strong>$count_aktif</strong></p>";
            
            if ($count_aktif > 0) {
                echo '<table class="table table-sm table-bordered">';
                echo '<tr><th>ID</th><th>Nama</th><th>Status</th><th>Mulai</th><th>Akhir</th></tr>';
                while ($row = mysqli_fetch_assoc($query_aktif)) {
                    echo '<tr>';
                    echo '<td>' . $row['id_bundling'] . '</td>';
                    echo '<td>' . $row['nama_bundling'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['tanggal_mulai'] . '</td>';
                    echo '<td>' . $row['tanggal_akhir'] . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white fw-bold">
            Detail Produk di Bundling
        </div>
        <div class="card-body">
            <?php
            $query = mysqli_query($koneksi, "
                SELECT 
                    b.id_bundling,
                    b.nama_bundling,
                    p1.nama_produk AS produk1,
                    p2.nama_produk AS produk2,
                    p3.nama_produk AS produk_gratis
                FROM promo_bundling b
                LEFT JOIN produk p1 ON b.id_produk1 = p1.id_produk
                LEFT JOIN produk p2 ON b.id_produk2 = p2.id_produk
                LEFT JOIN produk p3 ON b.id_produk_gratis = p3.id_produk
            ");
            
            if (mysqli_num_rows($query) > 0) {
                echo '<table class="table table-sm table-bordered">';
                echo '<tr><th>ID</th><th>Bundling</th><th>Produk 1</th><th>Produk 2</th><th>Produk Gratis</th></tr>';
                while ($row = mysqli_fetch_assoc($query)) {
                    echo '<tr>';
                    echo '<td>' . $row['id_bundling'] . '</td>';
                    echo '<td>' . $row['nama_bundling'] . '</td>';
                    echo '<td>' . ($row['produk1'] ?? '<span class="text-danger">❌ NOT FOUND</span>') . '</td>';
                    echo '<td>' . ($row['produk2'] ?? '<span class="text-danger">❌ NOT FOUND</span>') . '</td>';
                    echo '<td>' . ($row['produk_gratis'] ?? '<span class="text-danger">❌ NOT FOUND</span>') . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            ?>
        </div>
    </div>

    <div class="alert alert-info">
        <strong>💡 Tips Debugging:</strong>
        <ul>
            <li>Pastikan tanggal promo dalam range hari ini</li>
            <li>Pastikan status promo = "aktif"</li>
            <li>Pastikan produk yang dipilih ada di database produk</li>
            <li>Buka Console Browser (F12) untuk lihat JS errors</li>
        </ul>
    </div>

    <p>
        <a href="admin/master_bundling.php" class="btn btn-primary">← Kembali ke Master Bundling</a>
        <a href="kasir/transaksi.php" class="btn btn-success">Ke Kasir</a>
    </p>
</div>

<?php include './includes/footer.php'; ?>
