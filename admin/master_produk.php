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
include '../includes/navbar.php'; // Navbar sekarang berfungsi sebagai sidebar fixed di kiri
require '../config/koneksi.php';
?>

<script>
    // Otomatis tambahkan hidden input tab ke semua form yang submit ke proses/
    (function(){
        var tab = '<?= $tab_token ?>';
        if(!tab) return;
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
            <h3 class="fw-bold">Kelola Data Produk (Menu Kebab)</h3>
            <!-- Tombol Pemicu Modal Tambah -->
            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus"></i> Tambah Produk
            </button>
        </div>

        <!-- Alert Notifikasi[cite: 2] -->
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
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga Jual</th>
                                <th class="text-center">Status Stok</th>
                                <th class="text-center" width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Query data produk[cite: 2]
                            $query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
                            while ($row = mysqli_fetch_assoc($query)):
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $no++; ?></td>
                                    <td class="fw-bold"><?= $row['nama_produk']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $row['kategori']; ?></span></td>
                                    <td class="text-primary fw-bold">Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <?php if ($row['status_stok'] == 'Tersedia'): ?>
                                            <span class="badge bg-success">Tersedia</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Habis</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_produk']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <form action="../proses/produk_aksi.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Edit Produk[cite: 2] -->
                                <div class="modal fade" id="modalEdit<?= $row['id_produk']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title fw-bold">Edit Produk</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="../proses/produk_aksi.php" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="aksi" value="edit">
                                                    <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">

                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Produk</label>
                                                        <input type="text" name="nama_produk" class="form-control" value="<?= $row['nama_produk']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Kategori</label>
                                                        <select name="kategori" class="form-select" required>
                                                            <option value="Small" <?= ($row['kategori'] == 'Small') ? 'selected' : ''; ?>>Small</option>
                                                            <option value="Medium" <?= ($row['kategori'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                                            <option value="Super" <?= ($row['kategori'] == 'Super') ? 'selected' : ''; ?>>Super</option>
                                                            <option value="Jumbo" <?= ($row['kategori'] == 'Jumbo') ? 'selected' : ''; ?>>Jumbo</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Harga Jual (Rp)</label>
                                                        <input type="number" name="harga_jual" class="form-control" value="<?= $row['harga_jual']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status Stok</label>
                                                        <select name="status_stok" class="form-select" required>
                                                            <option value="Tersedia" <?= ($row['status_stok'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                                            <option value="Habis" <?= ($row['status_stok'] == 'Habis') ? 'selected' : ''; ?>>Habis</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Gunakan Topping?</label>
                                                        <select name="allow_topping" class="form-select" required>
                                                            <option value="ya" <?= (!isset($row['allow_topping']) || $row['allow_topping'] == 'ya') ? 'selected' : ''; ?>>Ya</option>
                                                            <option value="tidak" <?= (isset($row['allow_topping']) && $row['allow_topping'] == 'tidak') ? 'selected' : ''; ?>>Tidak</option>
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

<!-- Modal Tambah Produk[cite: 2] -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../proses/produk_aksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Kebab Sapi Spesial" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                            <option value="Super">Super</option>
                            <option value="Jumbo">Jumbo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="15000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Stok</label>
                        <select name="status_stok" class="form-select" required>
                            <option value="Tersedia" selected>Tersedia</option>
                            <option value="Habis">Habis</option>
                        </select>
                    </div>
                        <div class="mb-3">
                            <label class="form-label">Gunakan Topping?</label>
                            <select name="allow_topping" class="form-select" required>
                                <option value="ya" selected>Ya</option>
                                <option value="tidak">Tidak</option>
                            </select>
                        </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary fw-bold w-100 shadow-sm">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>