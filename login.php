<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/portal_repository.php';
startSafeSession();

if (!empty($_SESSION['portal_user']) && is_array($_SESSION['portal_user'])) {
    header('Location: portal.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$dbError = null;
$pdo = getDbConnection($dbError);

$errorMsg = '';
$infoMsg  = '';
$usernameValue = '';

if (($_GET['status'] ?? '') === 'logout') {
    $infoMsg = 'Anda telah berhasil keluar dari Portal SMK Bangun Nusa Bangsa.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['csrf_token'] ?? '');
    $usernameValue = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errorMsg = 'Sesi login tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } elseif ($usernameValue === '' || $password === '') {
        $errorMsg = 'Masukkan NIS / NIP / Username dan kata sandi Anda.';
    } else {
        $authenticatedUser = authenticatePortalUser($pdo, $usernameValue, $password);
        if ($authenticatedUser !== null) {
            session_regenerate_id(true);
            $_SESSION['portal_user'] = $authenticatedUser;
            header('Location: portal.php');
            exit;
        }
        $errorMsg = 'Username atau kata sandi tidak sesuai. Periksa kembali akun portal Anda.';
    }
}

$isHome    = false;
$isPortal  = true;
$pageTitle = 'Login Portal Akademik — SMK Bangun Nusa Bangsa';
$pageDesc  = 'Masuk ke Portal Siswa, Guru, dan Admin SMK Bangun Nusa Bangsa untuk mengakses jadwal, status sertifikasi BNSP, BKK, dan layanan akademik.';

require __DIR__ . '/includes/header.php';
?>

<?php if ($dbError !== null): ?>
<div class="db-notice" role="status">
    <div class="container db-notice-inner">
        <span><strong>Mode Cadangan Aktif:</strong> <?= e($dbError) ?> (Login akun demo tetap dapat digunakan).</span>
    </div>
</div>
<?php endif; ?>

<main class="portal-auth-main">
    <div class="container portal-auth-grid">
        <section class="portal-auth-info">
            <span class="section-kicker kicker-bright-green">Portal Terpadu Sekolah</span>
            <h1>Akses Portal <span class="text-bright-green">SMK Bangun Nusa Bangsa</span></h1>
            <p class="portal-auth-lead">
                Satu pintu layanan digital untuk <strong>Siswa</strong>, <strong>Guru</strong>, dan <strong>Administrator</strong>—terhubung dengan data jurusan TKJ, AKL, TKR, sertifikasi BNSP, Bursa Kerja Khusus (BKK), dan informasi PPDB.
            </p>

            <div class="portal-feature-list">
                <div class="portal-feature-item">
                    <span class="pf-icon">1</span>
                    <div>
                        <strong>Portal Siswa Vokasi</strong>
                        <p>Pantau jadwal praktikum lab/bengkel, status uji kompetensi BNSP, dan jadwal Klinik UTBK Top 10 PTN.</p>
                    </div>
                </div>
                <div class="portal-feature-item">
                    <span class="pf-icon">2</span>
                    <div>
                        <strong>Portal Guru &amp; Wali Kelas</strong>
                        <p>Kelola agenda bimbingan industri, pemantauan PKL mitra, dan pantau calon peserta didik PPDB.</p>
                    </div>
                </div>
                <div class="portal-feature-item">
                    <span class="pf-icon">3</span>
                    <div>
                        <strong>Portal Admin &amp; Panitia PPDB</strong>
                        <p>Tinjau data pendaftaran dan konsultasi jurusan yang masuk langsung dari halaman utama sekolah.</p>
                    </div>
                </div>
            </div>

            <div class="portal-demo-box">
                <div class="portal-demo-head">
                    <strong>Akun Demo Siap Uji Coba</strong>
                    <span>Klik untuk mengisi otomatis</span>
                </div>
                <div class="demo-account-pills">
                    <button type="button" class="demo-fill-btn" data-user="20261001" data-pass="siswa123" data-role="siswa">
                        Siswa TKJ (<code>20261001</code>)
                    </button>
                    <button type="button" class="demo-fill-btn" data-user="20261002" data-pass="siswa123" data-role="siswa">
                        Siswa AKL (<code>20261002</code>)
                    </button>
                    <button type="button" class="demo-fill-btn" data-user="guru.bnb" data-pass="guru123" data-role="guru">
                        Guru (<code>guru.bnb</code>)
                    </button>
                    <button type="button" class="demo-fill-btn" data-user="admin.bnb" data-pass="admin123" data-role="admin">
                        Admin (<code>admin.bnb</code>)
                    </button>
                </div>
            </div>
        </section>

        <section class="portal-auth-card" aria-label="Formulir Login Portal">
            <div class="portal-card-header">
                <span class="portal-card-badge">SSO Akademik BNB</span>
                <h2>Masuk ke Portal</h2>
                <p>Pilih peran Anda lalu masukkan kredensial akun sekolah.</p>
            </div>

            <div class="portal-role-tabs" role="tablist" aria-label="Pilih Peran Login">
                <button type="button" class="role-tab-btn active" data-role-tab="siswa" data-placeholder="Contoh NIS: 20261001">Siswa</button>
                <button type="button" class="role-tab-btn" data-role-tab="guru" data-placeholder="Contoh NIP/User: guru.bnb">Guru</button>
                <button type="button" class="role-tab-btn" data-role-tab="admin" data-placeholder="Contoh User: admin.bnb">Admin</button>
            </div>

            <?php if ($errorMsg !== ''): ?>
                <div class="form-feedback error portal-alert" role="alert"><?= e($errorMsg) ?></div>
            <?php endif; ?>

            <?php if ($infoMsg !== ''): ?>
                <div class="form-feedback success portal-alert" role="status"><?= e($infoMsg) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="portal-login-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="portalUsername">NIS / NIP / Username *</label>
                    <input
                        type="text"
                        id="portalUsername"
                        name="username"
                        value="<?= e($usernameValue) ?>"
                        required
                        autocomplete="username"
                        placeholder="Contoh NIS: 20261001"
                    >
                </div>

                <div class="form-group">
                    <label for="portalPassword">Kata Sandi *</label>
                    <div class="password-input-wrap">
                        <input
                            type="password"
                            id="portalPassword"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi portal"
                        >
                        <button type="button" class="toggle-password-btn" id="togglePasswordBtn" aria-label="Tampilkan atau sembunyikan kata sandi">
                            Tampilkan
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-submit-ppdb portal-submit-btn">
                    Masuk ke Portal Sekolah &rarr;
                </button>
            </form>

            <div class="portal-card-footer">
                <p>Belum terdaftar sebagai siswa? <a href="index.php#daftar">Daftar PPDB &amp; Konsultasi Jurusan</a></p>
                <a href="index.php" class="portal-back-home">&larr; Kembali ke Beranda Utama</a>
            </div>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
