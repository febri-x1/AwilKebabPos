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
        <div class="d-flex align-items-center text-white mb-2">
            <i class="fas fa-user-circle fa-2x me-2"></i>
            <div>
                <small class="d-block text-white-50">Logged in as</small>
                <strong><?= $_SESSION['nama_lengkap'] ?? 'Admin' ?></strong>
            </div>
        </div>
        <a href="../auth/logout.php" class="btn btn-outline-light btn-sm w-100" onclick="return confirm('Yakin ingin logout?')">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>