<?php
// config/session_config.php - Konfigurasi session untuk keamanan
// Atur parameter cookie session dan mulai session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, // Cookie berakhir saat browser ditutup
        'path' => '/',
        'domain' => '', // Kosongkan untuk domain saat ini
        'secure' => false, // Set true jika menggunakan HTTPS
        'httponly' => true, // Mencegah akses via JavaScript
        'samesite' => 'Strict' // Mencegah CSRF
    ]);

    // Mulai session hanya bila belum aktif
    session_start();
}

// Timeout inaktivitas (detik): 30 menit
$inactive = 30 * 60;

// Jika user tidak aktif lebih dari $inactive, hentikan session dan arahkan ke login
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactive)) {
    session_unset();
    session_destroy();
    // Arahkan ke halaman login aplikasi (sesuaikan path jika perlu)
    header('Location: /AppsAwilKebab/auth/login.php?pesan=timeout');
    exit;
}

// Perbarui penanda aktivitas terakhir
$_SESSION['last_activity'] = time();

// Regenerasi session ID secara periodik untuk keamanan
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 menit
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Helper: Mendukung sesi "per-tab" agar role berbeda di tab berbeda
if (!function_exists('get_tab_token')) {
    function get_tab_token() {
        if (isset($_GET['tab'])) return $_GET['tab'];
        if (isset($_POST['tab'])) return $_POST['tab'];
        if (isset($_SERVER['HTTP_X_TAB_TOKEN'])) return $_SERVER['HTTP_X_TAB_TOKEN'];
        return null;
    }
}

if (!function_exists('get_tab_session')) {
    function get_tab_session() {
        $token = get_tab_token();
        if ($token && isset($_SESSION['tabs'][$token])) {
            return $_SESSION['tabs'][$token];
        }

        // Fallback ke pola lama (top-level atau nested)
        if (isset($_SESSION['role'])) {
            return [
                'id_user' => $_SESSION['id_user'] ?? null,
                'username' => $_SESSION['username'] ?? null,
                'nama_lengkap' => $_SESSION['nama_lengkap'] ?? null,
                'role' => $_SESSION['role']
            ];
        }
        if (isset($_SESSION['admin'])) return array_merge($_SESSION['admin'], ['role' => 'admin']);
        if (isset($_SESSION['kasir'])) return array_merge($_SESSION['kasir'], ['role' => 'kasir']);
        return null;
    }
}
