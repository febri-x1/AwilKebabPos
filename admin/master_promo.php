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
?>

<!-- Penambahan Wrapper Content agar tidak tertutup sidebar -->
<div class="content-wrapper" style="margin-left: 250px; padding: 20px; min-height: 100vh; background-color: #f8f9fa;">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">Kelola Promo Produk</h3>
            <!-- Tombol Pemicu Modal Tambah -->
            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Promo
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
                                <th>Produk</th>
                                <th>Diskon</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Akhir</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $query = mysqli_query($koneksi, "SELECT p.*, pr.nama_produk FROM promo p JOIN produk pr ON p.id_produk = pr.id_produk ORDER BY p.id_promo DESC");
                            while ($row = mysqli_fetch_assoc($query)):
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold"><?= $row['nama_produk']; ?></td>
                                    <td>
                                        <?php if ($row['diskon_persen']): ?>
                                            <?= $row['diskon_persen']; ?>%
                                        <?php else: ?>
                                            Rp <?= number_format($row['diskon_nominal'], 0, ',', '.'); ?>
                                        <?php endif; ?>
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
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_promo']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <form action="../proses/promo_aksi.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus promo ini?');">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_promo" value="<?= $row['id_promo']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Promo -->
                                <div class="modal fade" id="modalEdit<?= $row['id_promo']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title fw-bold">Edit Promo</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="../proses/promo_aksi.php" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="aksi" value="edit">
                                                    <input type="hidden" name="id_promo" value="<?= $row['id_promo']; ?>">

                                                    <div class="mb-3">
                                                        <label class="form-label">Produk</label>
                                                        <select name="id_produk" class="form-select" required>
                                                            <?php
                                                            $produk_query = mysqli_query($koneksi, "SELECT * FROM produk");
                                                            while ($produk = mysqli_fetch_assoc($produk_query)):
                                                            ?>
                                                                <option value="<?= $produk['id_produk']; ?>" <?= ($produk['id_produk'] == $row['id_produk']) ? 'selected' : ''; ?>>
                                                                    <?= $produk['nama_produk']; ?>
                                                                </option>
                                                            <?php endwhile; ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Jenis Diskon</label>
                                                        <select name="jenis_diskon" class="form-select" required onchange="toggleDiskon(this)">
                                                            <option value="persen" <?= $row['diskon_persen'] ? 'selected' : ''; ?>>Persen (%)</option>
                                                            <option value="nominal" <?= $row['diskon_nominal'] ? 'selected' : ''; ?>>Nominal (Rp)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3" id="diskon_persen_div" style="display: <?= $row['diskon_persen'] ? 'block' : 'none'; ?>">
                                                        <label class="form-label">Diskon Persen (%)</label>
                                                        <input type="number" name="diskon_persen" class="form-control" value="<?= $row['diskon_persen']; ?>" min="0" max="100">
                                                    </div>
                                                    <div class="mb-3" id="diskon_nominal_div" style="display: <?= $row['diskon_nominal'] ? 'block' : 'none'; ?>">
                                                        <label class="form-label">Diskon Nominal (Rp)</label>
                                                        <input type="number" name="diskon_nominal" class="form-control" value="<?= $row['diskon_nominal']; ?>" min="0">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Mulai</label>
                                                        <input type="date" name="tanggal_mulai" class="form-control" value="<?= $row['tanggal_mulai']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Tanggal Akhir</label>
                                                        <input type="date" name="tanggal_akhir" class="form-control" value="<?= $row['tanggal_akhir']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
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
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Promo -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Promo Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../proses/promo_aksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label">Produk</label>
                        <select name="id_produk" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Produk --</option>
                            <?php
                            $produk_query = mysqli_query($koneksi, "SELECT * FROM produk");
                            while ($produk = mysqli_fetch_assoc($produk_query)):
                            ?>
                                <option value="<?= $produk['id_produk']; ?>"><?= $produk['nama_produk']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Diskon</label>
                        <select name="jenis_diskon" class="form-select" required onchange="toggleDiskon(this)">
                            <option value="persen">Persen (%)</option>
                            <option value="nominal">Nominal (Rp)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="diskon_persen_div" style="display: none;">
                        <label class="form-label">Diskon Persen (%)</label>
                        <input type="number" name="diskon_persen" class="form-control" min="0" max="100">
                    </div>
                    <div class="mb-3" id="diskon_nominal_div" style="display: none;">
                        <label class="form-label">Diskon Nominal (Rp)</label>
                        <input type="number" name="diskon_nominal" class="form-control" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="tanggal_akhir" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="aktif" selected>Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary fw-bold w-100 shadow-sm">Simpan Promo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleDiskon(select) {
        const jenis = select.value;
        document.getElementById('diskon_persen_div').style.display = jenis === 'persen' ? 'block' : 'none';
        document.getElementById('diskon_nominal_div').style.display = jenis === 'nominal' ? 'block' : 'none';
    }
</script>

<?php include '../includes/footer.php'; ?>