<?php
session_start();
require '../config/session_config.php';

$user = get_tab_session();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$tab_token = get_tab_token();

include '../includes/header.php';
include '../includes/navbar.php';
require '../config/koneksi.php';

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
    
    mysqli_query($koneksi, $create_table_sql);
}
?>

<script>
    (function(){
        var tab = '<?= $tab_token ?>'; if(!tab) return;
        document.addEventListener('DOMContentLoaded', function(){
            document.querySelectorAll('form[action^="../proses/"]').forEach(function(f){
                var i = document.createElement('input'); i.type='hidden'; i.name='tab'; i.value=tab; f.appendChild(i);
            });
        });
    })();
</script>

<!-- Penambahan Wrapper Content agar tidak tertutup sidebar -->
<div class="content-wrapper" style="margin-left: 250px; padding: 20px; min-height: 100vh; background-color: #f8f9fa;">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Kelola Promo Bundling</h3>
            <!-- Tombol Pemicu Modal Tambah -->
            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Bundling
            </button>
        </div>

        <!-- Alert Notifikasi -->
        <?php if (isset($_GET['pesan'])): ?>
            <?php if ($_GET['pesan'] == 'sukses'): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Data berhasil disimpan!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($_GET['pesan'] == 'gagal'): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> Gagal memproses data!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center" width="5%">No</th>
                                <th>Nama Bundling</th>
                                <th>Detail Paket</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Akhir</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "
                                SELECT 
                                    b.*,
                                    p1.nama_produk AS nama_produk1,
                                    p2.nama_produk AS nama_produk2,
                                    p3.nama_produk AS nama_produk_gratis
                                FROM promo_bundling b
                                LEFT JOIN produk p1 ON b.id_produk1 = p1.id_produk
                                LEFT JOIN produk p2 ON b.id_produk2 = p2.id_produk
                                LEFT JOIN produk p3 ON b.id_produk_gratis = p3.id_produk
                                ORDER BY b.id_bundling DESC
                            ");
                            
                            if (!$query) {
                                echo '<tr><td colspan="7" class="text-center text-danger py-4">
                                    <i class="fas fa-exclamation-circle"></i> Error: ' . mysqli_error($koneksi) . '
                                </td></tr>';
                            } elseif (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)):
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['nama_bundling']); ?></td>
                                    <td>
                                        <small>
                                            <?= $row['jumlah_produk1']; ?> x <?= htmlspecialchars($row['nama_produk1']); ?> +<br>
                                            <?= $row['jumlah_produk2']; ?> x <?= htmlspecialchars($row['nama_produk2']); ?> →<br>
                                            <strong class="text-success">Free <?= $row['jumlah_produk_gratis']; ?> x <?= htmlspecialchars($row['nama_produk_gratis']); ?></strong>
                                        </small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_mulai'])); ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_akhir'])); ?></td>
                                    <td class="text-center">
                                        <?php if ($row['status'] == 'aktif'): ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_bundling']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <form action="../proses/promo_bundling_aksi.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus bundling ini?');">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_bundling" value="<?= $row['id_bundling']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Bundling -->
                                <div class="modal fade" id="modalEdit<?= $row['id_bundling']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content border-0">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title fw-bold">Edit Bundling</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="../proses/promo_bundling_aksi.php" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="aksi" value="edit">
                                                    <input type="hidden" name="id_bundling" value="<?= $row['id_bundling']; ?>">

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Nama Bundling</label>
                                                        <input type="text" name="nama_bundling" class="form-control" value="<?= htmlspecialchars($row['nama_bundling']); ?>" placeholder="Contoh: Buy 2 Get 1" required>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Produk Pertama</label>
                                                            <select name="id_produk1" class="form-select" required>
                                                                <option value="">-- Pilih --</option>
                                                                <?php
                                                                $p_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia'");
                                                                while ($p = mysqli_fetch_assoc($p_query)):
                                                                ?>
                                                                    <option value="<?= $p['id_produk']; ?>" <?= ($p['id_produk'] == $row['id_produk1']) ? 'selected' : ''; ?>>
                                                                        <?= htmlspecialchars($p['nama_produk']); ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                            <input type="number" name="jumlah_produk1" class="form-control mt-2" value="<?= $row['jumlah_produk1']; ?>" min="1" placeholder="Jumlah" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Produk Kedua</label>
                                                            <select name="id_produk2" class="form-select" required>
                                                                <option value="">-- Pilih --</option>
                                                                <?php
                                                                $p_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia'");
                                                                while ($p = mysqli_fetch_assoc($p_query)):
                                                                ?>
                                                                    <option value="<?= $p['id_produk']; ?>" <?= ($p['id_produk'] == $row['id_produk2']) ? 'selected' : ''; ?>>
                                                                        <?= htmlspecialchars($p['nama_produk']); ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                            <input type="number" name="jumlah_produk2" class="form-control mt-2" value="<?= $row['jumlah_produk2']; ?>" min="1" placeholder="Jumlah" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label fw-bold">Produk Gratis</label>
                                                            <select name="id_produk_gratis" class="form-select" required>
                                                                <option value="">-- Pilih --</option>
                                                                <?php
                                                                $p_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia'");
                                                                while ($p = mysqli_fetch_assoc($p_query)):
                                                                ?>
                                                                    <option value="<?= $p['id_produk']; ?>" <?= ($p['id_produk'] == $row['id_produk_gratis']) ? 'selected' : ''; ?>>
                                                                        <?= htmlspecialchars($p['nama_produk']); ?>
                                                                    </option>
                                                                <?php endwhile; ?>
                                                            </select>
                                                            <input type="number" name="jumlah_produk_gratis" class="form-control mt-2" value="<?= $row['jumlah_produk_gratis']; ?>" min="1" placeholder="Jumlah" required>
                                                        </div>
                                                    </div>

                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Tanggal Mulai</label>
                                                            <input type="date" name="tanggal_mulai" class="form-control" value="<?= $row['tanggal_mulai']; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Tanggal Akhir</label>
                                                            <input type="date" name="tanggal_akhir" class="form-control" value="<?= $row['tanggal_akhir']; ?>" required>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3 mt-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="aktif" <?= ($row['status'] == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                                            <option value="nonaktif" <?= ($row['status'] == 'nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="submit" class="btn btn-warning fw-bold w-100 shadow-sm">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                endwhile; 
                            } else {
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                        Belum ada bundling promo
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Bundling -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Bundling Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../proses/promo_bundling_aksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Bundling</label>
                        <input type="text" name="nama_bundling" class="form-control" placeholder="Contoh: Buy 2 Get 1, Paket Hemat, dll" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Produk Pertama</label>
                            <select name="id_produk1" class="form-select" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <?php
                                $produk_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia'");
                                while ($produk = mysqli_fetch_assoc($produk_query)):
                                ?>
                                    <option value="<?= $produk['id_produk']; ?>">
                                        <?= htmlspecialchars($produk['nama_produk']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" name="jumlah_produk1" class="form-control mt-2" min="1" placeholder="Jumlah" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Produk Kedua</label>
                            <select name="id_produk2" class="form-select" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <?php
                                $produk_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia'");
                                while ($produk = mysqli_fetch_assoc($produk_query)):
                                ?>
                                    <option value="<?= $produk['id_produk']; ?>">
                                        <?= htmlspecialchars($produk['nama_produk']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" name="jumlah_produk2" class="form-control mt-2" min="1" placeholder="Jumlah" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Produk Gratis</label>
                            <select name="id_produk_gratis" class="form-select" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <?php
                                $produk_query = mysqli_query($koneksi, "SELECT * FROM produk WHERE status_stok = 'Tersedia'");
                                while ($produk = mysqli_fetch_assoc($produk_query)):
                                ?>
                                    <option value="<?= $produk['id_produk']; ?>">
                                        <?= htmlspecialchars($produk['nama_produk']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" name="jumlah_produk_gratis" class="form-control mt-2" min="1" placeholder="Jumlah" required>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="tanggal_akhir" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="aktif" selected>Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="fas fa-save"></i> Tambah Bundling
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
