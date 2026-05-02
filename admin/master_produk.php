<?php
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';
require '../config/koneksi.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Kelola Data Produk (Menu Kebab)</h3>
        <!-- Tombol Pemicu Modal Tambah -->
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
            + Tambah Produk
        </button>
    </div>

    <!-- Alert Notifikasi -->
    <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] == 'sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif ($_GET['pesan'] == 'gagal'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Gagal memproses data!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover align-middle">
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
                    $query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");
                    while ($row = mysqli_fetch_assoc($query)):
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td><?= $row['nama_produk']; ?></td>
                            <td><?= $row['kategori']; ?></td>
                            <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <?php if ($row['status_stok'] == 'Tersedia'): ?>
                                    <span class="badge bg-success">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <!-- Tombol Edit memicu modal spesifik berdasarkan ID -->
                                <button href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_produk']; ?>">Edit</button>

                                <!-- Tombol Hapus memanggil form POST (Lebih aman dari GET) -->
                                <form action="../proses/produk_aksi.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Produk (Di-generate untuk setiap baris data) -->
                        <div class="modal fade" id="modalEdit<?= $row['id_produk']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h5 class="modal-title fw-bold">Edit Produk</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="../proses/produk_aksi.php" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="aksi" value="edit">
                                            <input type="hidden" name="id_produk" value="<?= $row['id_produk']; ?>">

                                            <div class="mb-3">
                                                <label>Nama Produk</label>
                                                <input type="text" name="nama_produk" class="form-control" value="<?= $row['nama_produk']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Kategori</label>
                                                <select name="kategori" class="form-select" required>
                                                    <option value="Small" <?= ($row['kategori'] == 'Small') ? 'selected' : ''; ?>>Small</option>
                                                    <option value="Medium" <?= ($row['kategori'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                                    <option value="Super" <?= ($row['kategori'] == 'Super') ? 'selected' : ''; ?>>Super</option>
                                                    <option value="Jumbo" <?= ($row['kategori'] == 'Jumbo') ? 'selected' : ''; ?>>Jumbo</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Harga Jual (Rp)</label>
                                                <input type="number" name="harga_jual" class="form-control" value="<?= $row['harga_jual']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Status Stok</label>
                                                <select name="status_stok" class="form-select" required>
                                                    <option value="Tersedia" <?= ($row['status_stok'] == 'Tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                                    <option value="Habis" <?= ($row['status_stok'] == 'Habis') ? 'selected' : ''; ?>>Habis</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-warning fw-bold w-100">Simpan Perubahan</button>
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

<!-- Modal Tambah Produk -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Tambah Produk Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../proses/produk_aksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label>Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Kebab Sapi Spesial" required>
                    </div>
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="Small">Small</option>
                            <option value="Medium">Medium</option>
                            <option value="Super">Super</option>
                            <option value="Jumbo">Jumbo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" placeholder="Contoh: 15000" required>
                    </div>
                    <div class="mb-3">
                        <label>Status Stok</label>
                        <select name="status_stok" class="form-select" required>
                            <option value="Tersedia" selected>Tersedia</option>
                            <option value="Habis">Habis</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold w-100">Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>