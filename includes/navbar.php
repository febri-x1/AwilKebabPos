<!-- includes/navbar.php - Sidebar khusus untuk halaman Admin -->
<div class="sidebar-left shadow">
    <div class="sidebar-header">
        <h4 class="text-white fw-bold mb-0">
            <i class="fas fa-utensils"></i> AWIL KEBAB
        </h4>
        <small class="text-white-50">Admin Dashboard</small>
    </div>

    <ul class="nav flex-column mt-3 mb-auto">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="master_produk.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'master_produk.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Data Produk
            </a>
        </li>
        <li class="nav-item">
            <a href="master_topping.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'master_topping.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Data Topping
            </a>
        </li>
        <li class="nav-item">
            <a href="master_promo.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'master_promo.php' ? 'active' : '' ?>">
                <i class="fas fa-tags"></i> Data Promo
            </a>
        </li>
        <li class="nav-item">
            <a href="master_bundling.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'master_bundling.php' ? 'active' : '' ?>">
                <i class="fas fa-box"></i> Promo Bundling
            </a>
        </li>
        <li class="nav-item">
            <a href="pengeluaran.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'pengeluaran.php' ? 'active' : '' ?>">
                <i class="fas fa-receipt"></i> Pengeluaran
            </a>
        </li>
        <li class="nav-item">
            <a href="laporan.php" class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i> Laporan
            </a>
        </li>
    </ul>

    <div class="mt-auto p-3 border-top border-secondary">
        <?php
        // Coba ambil sesi per-tab jika helper tersedia
        $user_for_nav = null;
        if (function_exists('get_tab_session')) {
            $user_for_nav = get_tab_session();
            $tab_for_logout = get_tab_token();
        } else {
            $user_for_nav = $_SESSION['admin'] ?? null;
            $tab_for_logout = null;
        }
        $display_name = $user_for_nav['nama_lengkap'] ?? 'Admin';
        $role_for_logout = $user_for_nav['role'] ?? 'admin';
        $logout_url = '../auth/logout.php?role=' . $role_for_logout . ($tab_for_logout ? '&tab=' . $tab_for_logout : '');
        ?>
        <div class="d-flex align-items-center text-white mb-2">
            <i class="fas fa-user-circle fa-2x me-2"></i>
            <div>
                <small class="d-block text-white-50">Logged in as</small>
                <strong><?= $display_name ?></strong>
            </div>
        </div>
        <button type="button" class="btn btn-warning btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalUbahPassword">
            <i class="fas fa-lock"></i> Ubah Password
        </button>
        <a href="<?= $logout_url ?>" class="btn btn-outline-light btn-sm w-100" onclick="return confirm('Yakin ingin logout?')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<!-- Modal Ubah Password -->
<div class="modal fade" id="modalUbahPassword" tabindex="-1" aria-labelledby="modalUbahPasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalUbahPasswordLabel">Ubah Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUbahPassword" method="POST" action="../proses/ubah_password_aksi.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="passwordLama" class="form-label">Password Lama</label>
                        <input type="password" class="form-control" id="passwordLama" name="password_lama" required>
                        <small class="text-muted">Masukkan password lama Anda</small>
                    </div>
                    <div class="mb-3">
                        <label for="passwordBaru" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="passwordBaru" name="password_baru" required>
                        <small class="text-muted">Password minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label for="passwordKonfirmasi" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="passwordKonfirmasi" name="password_konfirmasi" required>
                        <small class="text-muted">Pastikan password sama</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>