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
        <h3 class="fw-bold">Catatan Pengeluaran Operasional</h3>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
            + Tambah Pengeluaran
        </button>
    </div>

    <!-- Alert Notifikasi -->
    <?php if(isset($_GET['pesan'])): ?>
        <?php if($_GET['pesan'] == 'sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Data pengeluaran berhasil disimpan!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif($_GET['pesan'] == 'gagal'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Gagal memproses data pengeluaran!
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
                        <th>Tanggal</th>
                        <th>Nama Item / Bahan</th>
                        <th>Kategori</th>
                        <th>Jumlah Biaya</th>
                        <th>Keterangan</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    // Mengurutkan dari tanggal terbaru, lalu id terbaru
                    $query = mysqli_query($koneksi, "SELECT * FROM pengeluaran ORDER BY tanggal DESC, id_pengeluaran DESC");
                    while($row = mysqli_fetch_assoc($query)):
                    ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                        <td><?= $row['nama_item_bahan']; ?></td>
                        <td><span class="badge bg-secondary"><?= $row['kategori_bahan']; ?></span></td>
                        <td class="fw-bold text-danger">Rp <?= number_format($row['jumlah_biaya'], 0, ',', '.'); ?></td>
                        <td><?= $row['keterangan']; ?></td>
                        <td class="text-center">
                            <button href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_pengeluaran']; ?>">Edit</button>
                            
                            <form action="../proses/pengeluaran_aksi.php" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus catatan pengeluaran ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id_pengeluaran" value="<?= $row['id_pengeluaran']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Edit Pengeluaran -->
                    <div class="modal fade" id="modalEdit<?= $row['id_pengeluaran']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-warning">
                                    <h5 class="modal-title fw-bold">Edit Pengeluaran</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="../proses/pengeluaran_aksi.php" method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="aksi" value="edit">
                                        <input type="hidden" name="id_pengeluaran" value="<?= $row['id_pengeluaran']; ?>">
                                        
                                        <div class="mb-3">
                                            <label>Tanggal</label>
                                            <input type="date" name="tanggal" class="form-control" value="<?= $row['tanggal']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Nama Item / Bahan</label>
                                            <input type="text" name="nama_item_bahan" class="form-control" value="<?= $row['nama_item_bahan']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Kategori</label>
                                            <select name="kategori_bahan" class="form-select" required>
                                                <option value="Bahan Baku" <?= ($row['kategori_bahan'] == 'Bahan Baku') ? 'selected' : ''; ?>>Bahan Baku (Daging, Sayur, dll)</option>
                                                <option value="Operasional" <?= ($row['kategori_bahan'] == 'Operasional') ? 'selected' : ''; ?>>Operasional (Gas, Listrik, Plastik)</option>
                                                <option value="Lainnya" <?= ($row['kategori_bahan'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label>Jumlah Biaya (Rp)</label>
                                            <input type="number" name="jumlah_biaya" class="form-control" value="<?= $row['jumlah_biaya']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Keterangan (Opsional)</label>
                                            <textarea name="keterangan" class="form-control" rows="2"><?= $row['keterangan']; ?></textarea>
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

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Input Pengeluaran Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../proses/pengeluaran_aksi.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="mb-3">
                        <label>Tanggal</label>
                        <!-- Set default value ke hari ini -->
                        <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama Item / Bahan</label>
                        <input type="text" name="nama_item_bahan" class="form-control" placeholder="Contoh: Daging Kebab 5kg, Gas 3kg" required>
                    </div>
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select name="kategori_bahan" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="Bahan Baku">Bahan Baku (Daging, Sayur, Tortilla)</option>
                            <option value="Operasional">Operasional (Gas, Listrik, Kantong Plastik)</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Jumlah Biaya (Rp)</label>
                        <input type="number" name="jumlah_biaya" class="form-control" placeholder="Contoh: 250000" required>
                    </div>
                    <div class="mb-3">
                        <label>Keterangan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tambahan jika ada..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold w-100">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>