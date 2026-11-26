<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/portal_repository.php';
require_once __DIR__ . '/includes/data_repository.php';
require_once __DIR__ . '/includes/cms_repository.php';

startSafeSession();

if (empty($_SESSION['portal_user']) || !is_array($_SESSION['portal_user'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$dbError = null;
$pdo = getDbConnection($dbError);

$user = $_SESSION['portal_user'];
$role = (string) ($user['role'] ?? 'siswa');
$jurusanKode = isset($user['jurusan_kode']) ? (string) $user['jurusan_kode'] : null;

$canManageArticles = in_array($role, ['guru', 'admin'], true);
$canManageSiteData = ($role === 'admin');

$availableTabs = ['ringkasan'];
if ($canManageArticles) {
    $availableTabs[] = 'artikel';
    $availableTabs[] = 'ppdb';
}
if ($canManageSiteData) {
    $availableTabs[] = 'jurusan';
    $availableTabs[] = 'kelebihan';
    $availableTabs[] = 'alumni';
}

$activeTab = trim((string) ($_GET['tab'] ?? 'ringkasan'));
if (!in_array($activeTab, $availableTabs, true)) {
    $activeTab = 'ringkasan';
}

// Tangani aksi POST (Tambah / Edit / Hapus konten portal)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = trim((string) ($_POST['cms_action'] ?? ''));
    $redirectTab = trim((string) ($_POST['redirect_tab'] ?? 'ringkasan'));
    if (!in_array($redirectTab, $availableTabs, true)) {
        $redirectTab = 'ringkasan';
    }

    $token = (string) ($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $_SESSION['portal_flash'] = ['type' => 'error', 'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.'];
        header('Location: portal.php?tab=' . urlencode($redirectTab));
        exit;
    }

    if (!($pdo instanceof PDO)) {
        $_SESSION['portal_flash'] = ['type' => 'error', 'message' => 'Database MySQL belum aktif. Aktifkan MySQL di XAMPP agar perubahan data dapat disimpan.'];
        header('Location: portal.php?tab=' . urlencode($redirectTab));
        exit;
    }

    try {
        $msg = match ($action) {
            'save_artikel' => (function () use ($pdo, $canManageArticles): string {
                if (!$canManageArticles) {
                    throw new RuntimeException('Hanya akun Guru atau Admin yang dapat mengelola artikel.');
                }
                $editId = (int) ($_POST['id'] ?? 0);
                return saveArtikelRecord($pdo, $_POST, $editId > 0 ? $editId : null);
            })(),
            'delete_artikel' => (function () use ($pdo, $canManageArticles): string {
                if (!$canManageArticles) {
                    throw new RuntimeException('Hanya akun Guru atau Admin yang dapat menghapus artikel.');
                }
                return deleteArtikelRecord($pdo, (int) ($_POST['id'] ?? 0));
            })(),
            'update_jurusan' => (function () use ($pdo, $canManageSiteData): string {
                if (!$canManageSiteData) {
                    throw new RuntimeException('Hanya Admin yang dapat mengubah data jurusan.');
                }
                return updateJurusanRecord($pdo, (int) ($_POST['id'] ?? 0), $_POST);
            })(),
            'save_kelebihan' => (function () use ($pdo, $canManageSiteData): string {
                if (!$canManageSiteData) {
                    throw new RuntimeException('Hanya Admin yang dapat mengelola keunggulan sekolah.');
                }
                $editId = (int) ($_POST['id'] ?? 0);
                return saveKelebihanRecord($pdo, $_POST, $editId > 0 ? $editId : null);
            })(),
            'delete_kelebihan' => (function () use ($pdo, $canManageSiteData): string {
                if (!$canManageSiteData) {
                    throw new RuntimeException('Hanya Admin yang dapat menghapus keunggulan sekolah.');
                }
                return deleteKelebihanRecord($pdo, (int) ($_POST['id'] ?? 0));
            })(),
            'save_alumni' => (function () use ($pdo, $canManageSiteData): string {
                if (!$canManageSiteData) {
                    throw new RuntimeException('Hanya Admin yang dapat mengelola data alumni.');
                }
                $editId = (int) ($_POST['id'] ?? 0);
                return saveAlumniRecord($pdo, $_POST, $editId > 0 ? $editId : null);
            })(),
            'delete_alumni' => (function () use ($pdo, $canManageSiteData): string {
                if (!$canManageSiteData) {
                    throw new RuntimeException('Hanya Admin yang dapat menghapus data alumni.');
                }
                return deleteAlumniRecord($pdo, (int) ($_POST['id'] ?? 0));
            })(),
            'delete_ppdb' => (function () use ($pdo, $canManageSiteData): string {
                if (!$canManageSiteData) {
                    throw new RuntimeException('Hanya Admin yang dapat menghapus data pendaftar PPDB.');
                }
                return deletePpdbRecord($pdo, (int) ($_POST['id'] ?? 0));
            })(),
            default => throw new RuntimeException('Aksi pengelolaan tidak dikenali.'),
        };

        $_SESSION['portal_flash'] = ['type' => 'success', 'message' => $msg];
    } catch (Throwable $ex) {
        $_SESSION['portal_flash'] = ['type' => 'error', 'message' => $ex->getMessage()];
    }

    header('Location: portal.php?tab=' . urlencode($redirectTab));
    exit;
}

$flash = $_SESSION['portal_flash'] ?? null;
unset($_SESSION['portal_flash']);

$roleSummary = getPortalRoleSummary($role, $jurusanKode);
$ppdbSubmissions = $canManageArticles ? getRecentPpdbSubmissions($pdo, 20) : [];
$artikelList = $canManageArticles ? getArtikelList($pdo, 'semua', '') : [];
$jurusanList = $canManageSiteData ? getJurusanList($pdo) : [];
$kelebihanList = $canManageSiteData ? getKelebihanList($pdo) : [];
$alumniList = $canManageSiteData ? getAlumniList($pdo) : [];
$assetImages = getAvailableAssetImages();

// Data item yang sedang diedit berdasarkan query string
$editArtikel = null;
if ($activeTab === 'artikel' && isset($_GET['edit_artikel'])) {
    $targetId = (int) $_GET['edit_artikel'];
    foreach ($artikelList as $art) {
        if ((int) ($art['id'] ?? 0) === $targetId) {
            $editArtikel = $art;
            break;
        }
    }
}

$editJurusan = null;
if ($activeTab === 'jurusan' && isset($_GET['edit_jurusan'])) {
    $targetId = (int) $_GET['edit_jurusan'];
    foreach ($jurusanList as $jur) {
        if ((int) ($jur['id'] ?? 0) === $targetId) {
            $editJurusan = $jur;
            break;
        }
    }
}

$editKelebihan = null;
if ($activeTab === 'kelebihan' && isset($_GET['edit_kelebihan'])) {
    $targetId = (int) $_GET['edit_kelebihan'];
    foreach ($kelebihanList as $kel) {
        if ((int) ($kel['id'] ?? 0) === $targetId) {
            $editKelebihan = $kel;
            break;
        }
    }
}

$editAlumni = null;
if ($activeTab === 'alumni' && isset($_GET['edit_alumni'])) {
    $targetId = (int) $_GET['edit_alumni'];
    foreach ($alumniList as $al) {
        if ((int) ($al['id'] ?? 0) === $targetId) {
            $editAlumni = $al;
            break;
        }
    }
}

$roleLabelMap = [
    'siswa' => 'Siswa Aktif',
    'guru' => 'Guru & Pendidik',
    'admin' => 'Administrator',
];
$roleBadgeText = $roleLabelMap[$role] ?? 'Pengguna Portal';

$isHome    = false;
$isPortal  = true;
$pageTitle = $roleSummary['label'] . ' — SMK Bangun Nusa Bangsa';
$pageDesc  = 'Dashboard Portal Akademik SMK Bangun Nusa Bangsa.';

require __DIR__ . '/includes/header.php';
?>

<main class="portal-dashboard-main">
    <section class="portal-hero-banner">
        <div class="container portal-hero-inner">
            <div>
                <div class="portal-user-badges">
                    <span class="portal-role-pill"><?= e($roleBadgeText) ?></span>
                    <?php if ($jurusanKode !== null && $jurusanKode !== ''): ?>
                        <span class="portal-jurusan-pill">Jurusan <?= e($jurusanKode) ?></span>
                    <?php endif; ?>
                </div>
                <h1>Selamat Datang, <span class="text-bright-green"><?= e((string) $user['nama_lengkap']) ?></span></h1>
                <p class="portal-user-meta">
                    ID Pengguna: <strong><?= e((string) $user['username']) ?></strong>
                    <span>&bull;</span>
                    <span><?= e((string) $user['kelas_atau_jabatan']) ?></span>
                </p>
            </div>

            <div class="portal-hero-actions">
                <a href="index.php" class="btn btn-outline-light">Lihat Website</a>
                <form action="logout.php" method="POST" class="inline-logout-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <button type="submit" class="btn btn-logout-portal">Keluar Portal</button>
                </form>
            </div>
        </div>
    </section>

    <div class="container portal-content-wrap">
        <?php if ($dbError !== null): ?>
            <div class="form-feedback error portal-alert" role="status">
                <strong>Info Mode Cadangan:</strong> <?= e($dbError) ?> (Untuk menyimpan pembaruan artikel/data ke database, aktifkan layanan MySQL di XAMPP).
            </div>
        <?php endif; ?>

        <?php if (is_array($flash) && !empty($flash['message'])): ?>
            <div class="form-feedback <?= $flash['type'] === 'success' ? 'success' : 'error' ?> portal-alert" role="alert">
                <?= e((string) $flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Navigasi Tab Portal -->
        <nav class="portal-nav-tabs" aria-label="Menu Portal">
            <a href="portal.php?tab=ringkasan" class="portal-tab-link<?= $activeTab === 'ringkasan' ? ' active' : '' ?>">Ringkasan</a>
            <?php if ($canManageArticles): ?>
                <a href="portal.php?tab=artikel" class="portal-tab-link<?= $activeTab === 'artikel' ? ' active' : '' ?>">Kelola Artikel (<?= count($artikelList) ?>)</a>
            <?php endif; ?>
            <?php if ($canManageSiteData): ?>
                <a href="portal.php?tab=jurusan" class="portal-tab-link<?= $activeTab === 'jurusan' ? ' active' : '' ?>">Kelola Jurusan</a>
                <a href="portal.php?tab=kelebihan" class="portal-tab-link<?= $activeTab === 'kelebihan' ? ' active' : '' ?>">Kelola Keunggulan</a>
                <a href="portal.php?tab=alumni" class="portal-tab-link<?= $activeTab === 'alumni' ? ' active' : '' ?>">Kelola Alumni &amp; PTN</a>
            <?php endif; ?>
            <?php if ($canManageArticles): ?>
                <a href="portal.php?tab=ppdb" class="portal-tab-link<?= $activeTab === 'ppdb' ? ' active' : '' ?>">Data PPDB (<?= count($ppdbSubmissions) ?>)</a>
            <?php endif; ?>
        </nav>

        <?php if ($activeTab === 'ringkasan'): ?>
            <div class="portal-summary-grid">
                <article class="portal-stat-card">
                    <span class="ps-label">Status Portal</span>
                    <strong><?= e($roleSummary['label']) ?></strong>
                    <small>Tahun Ajaran 2026/2027 • Aktif</small>
                </article>
                <article class="portal-stat-card">
                    <span class="ps-label">Sertifikasi &amp; Mutu</span>
                    <strong>LSP P1 BNSP RI</strong>
                    <small>Akreditasi A (Unggul) • Link &amp; Match Industri</small>
                </article>
                <article class="portal-stat-card">
                    <span class="ps-label">Layanan Karier &amp; PTN</span>
                    <strong>BKK &amp; Top 10 PTN</strong>
                    <small>50+ Mitra Perusahaan &amp; Klinik SNBT-UTBK</small>
                </article>
            </div>

            <?php if ($canManageArticles): ?>
            <section class="portal-panel-card portal-cms-shortcut">
                <div class="panel-card-head">
                    <div>
                        <h2>Pusat Pembaruan Konten Website</h2>
                        <p class="panel-sub">Perubahan yang Anda simpan langsung memperbarui tampilan halaman utama dan halaman artikel.</p>
                    </div>
                </div>
                <div class="portal-shortcut-actions">
                    <a href="portal.php?tab=artikel" class="btn btn-primary-green">Kelola &amp; Update Artikel</a>
                    <?php if ($canManageSiteData): ?>
                        <a href="portal.php?tab=jurusan" class="btn btn-maroon">Update Data Jurusan</a>
                        <a href="portal.php?tab=kelebihan" class="btn btn-outline-maroon">Update Keunggulan</a>
                        <a href="portal.php?tab=alumni" class="btn btn-outline-maroon">Update Alumni &amp; PTN</a>
                    <?php endif; ?>
                </div>
            </section>
            <?php else: ?>
            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <div>
                        <h2>Hak Akses Akun Siswa</h2>
                        <p class="panel-sub">Akun siswa hanya memiliki akses lihat jadwal dan informasi akademik. Untuk memperbarui artikel atau data website, silakan keluar lalu login menggunakan akun <strong>Guru</strong> (<code>guru.bnb</code>) atau <strong>Admin</strong> (<code>admin.bnb</code>).</p>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <div class="portal-panels-grid">
                <section class="portal-panel-card">
                    <div class="panel-card-head">
                        <h2>Jadwal &amp; Aktivitas Terkini</h2>
                        <span class="panel-tag">Pekan Ini</span>
                    </div>
                    <div class="portal-schedule-list">
                        <?php foreach ($roleSummary['jadwal'] as $item): ?>
                        <div class="schedule-row">
                            <div class="schedule-day">
                                <strong><?= e($item['hari']) ?></strong>
                                <span><?= e($item['waktu']) ?></span>
                            </div>
                            <p><?= e($item['kegiatan']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="portal-panel-card">
                    <div class="panel-card-head">
                        <h2>Agenda &amp; Status Layanan</h2>
                        <span class="panel-tag panel-tag-green">Terbaru</span>
                    </div>
                    <div class="portal-agenda-list">
                        <?php foreach ($roleSummary['agenda'] as $ag): ?>
                        <div class="agenda-row">
                            <div class="agenda-top">
                                <strong><?= e($ag['judul']) ?></strong>
                                <span class="agenda-status"><?= e($ag['status']) ?></span>
                            </div>
                            <p><?= e($ag['catatan']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

        <?php elseif ($activeTab === 'artikel' && $canManageArticles): ?>
            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <div>
                        <h2><?= $editArtikel ? 'Edit Artikel Website' : 'Tambah Artikel Baru' ?></h2>
                        <p class="panel-sub">Artikel yang disimpan akan langsung tampil di beranda dan direktori artikel.</p>
                    </div>
                    <?php if ($editArtikel): ?>
                        <a href="portal.php?tab=artikel" class="btn btn-sm-outline">Batal Edit</a>
                    <?php endif; ?>
                </div>

                <form action="portal.php?tab=artikel" method="POST" enctype="multipart/form-data" class="portal-cms-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="cms_action" value="save_artikel">
                    <input type="hidden" name="redirect_tab" value="artikel">
                    <input type="hidden" name="id" value="<?= e((string) ($editArtikel['id'] ?? 0)) ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="artJudul">Judul Artikel *</label>
                            <input type="text" id="artJudul" name="judul" required value="<?= e((string) ($editArtikel['judul'] ?? '')) ?>" placeholder="Contoh: Prestasi Tim TKJ di LKS Nasional 2026">
                        </div>
                        <div class="form-group">
                            <label for="artSlug">Slug URL (opsional, otomatis dari judul bila kosong)</label>
                            <input type="text" id="artSlug" name="slug" value="<?= e((string) ($editArtikel['slug'] ?? '')) ?>" placeholder="prestasi-tim-tkj-lks-2026">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="artKategori">Kategori *</label>
                            <?php $katVal = (string) ($editArtikel['kategori'] ?? 'berita'); ?>
                            <select id="artKategori" name="kategori">
                                <option value="berita" <?= $katVal === 'berita' ? 'selected' : '' ?>>Berita Sekolah</option>
                                <option value="jurusan" <?= $katVal === 'jurusan' ? 'selected' : '' ?>>Jurusan (TKJ / AKL / TKR)</option>
                                <option value="keunggulan" <?= $katVal === 'keunggulan' ? 'selected' : '' ?>>Keunggulan Sekolah</option>
                                <option value="alumni" <?= $katVal === 'alumni' ? 'selected' : '' ?>>Alumni Perusahaan</option>
                                <option value="universitas" <?= $katVal === 'universitas' ? 'selected' : '' ?>>Universitas Top 10</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="artLabelKategori">Badge Label Kategori</label>
                            <input type="text" id="artLabelKategori" name="label_kategori" value="<?= e((string) ($editArtikel['label_kategori'] ?? '')) ?>" placeholder="Contoh: Berita Sekolah / Jurusan TKJ">
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="artPenulis">Penulis</label>
                            <input type="text" id="artPenulis" name="penulis" value="<?= e((string) ($editArtikel['penulis'] ?? $user['nama_lengkap'])) ?>">
                        </div>
                        <div class="form-group">
                            <label for="artWaktuBaca">Estimasi Waktu Baca</label>
                            <input type="text" id="artWaktuBaca" name="waktu_baca" value="<?= e((string) ($editArtikel['waktu_baca'] ?? '5 Menit Baca')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="artTanggal">Tanggal Publikasi</label>
                            <input type="date" id="artTanggal" name="tanggal_publikasi" value="<?= e((string) ($editArtikel['tanggal_publikasi'] ?? date('Y-m-d'))) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="artGambar">Pilih Gambar Sampul</label>
                            <?php $imgVal = (string) ($editArtikel['gambar'] ?? 'assets/img/bnb.jpg'); ?>
                            <select id="artGambar" name="gambar">
                                <?php foreach ($assetImages as $imgPath => $imgLabel): ?>
                                    <option value="<?= e($imgPath) ?>" <?= $imgVal === $imgPath ? 'selected' : '' ?>><?= e($imgLabel) ?></option>
                                <?php endforeach; ?>
                                <?php if ($imgVal !== '' && !array_key_exists($imgVal, $assetImages)): ?>
                                    <option value="<?= e($imgVal) ?>" selected><?= e($imgVal) ?> (Gambar saat ini)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="artGambarUpload">Atau Unggah Gambar Baru (JPG/PNG/WEBP, maks 3 MB)</label>
                            <input type="file" id="artGambarUpload" name="gambar_upload" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="artRingkasan">Ringkasan Artikel *</label>
                        <textarea id="artRingkasan" name="ringkasan" rows="2" required placeholder="Ringkasan singkat yang muncul di kartu artikel beranda..."><?= e((string) ($editArtikel['ringkasan'] ?? '')) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="artKonten">Isi Konten Artikel * (mendukung teks paragraf biasa atau HTML seperti &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;)</label>
                        <textarea id="artKonten" name="konten" rows="7" required placeholder="Tulis isi lengkap artikel di sini..."><?= e((string) ($editArtikel['konten'] ?? '')) ?></textarea>
                    </div>

                    <div class="form-check-row">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="unggulan" value="1" <?= !isset($editArtikel) || !empty($editArtikel['unggulan']) ? 'checked' : '' ?>>
                            <span>Tampilkan sebagai artikel unggulan (prioritas di halaman utama)</span>
                        </label>
                        <button type="submit" class="btn btn-primary-green" <?= !($pdo instanceof PDO) ? 'disabled' : '' ?>>
                            <?= $editArtikel ? 'Simpan Perubahan Artikel' : 'Publikasikan Artikel Baru' ?>
                        </button>
                    </div>
                </form>
            </section>

            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <h2>Daftar Artikel Website</h2>
                    <span class="panel-tag"><?= count($artikelList) ?> Artikel</span>
                </div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Judul &amp; Ringkasan</th>
                                <th>Kategori</th>
                                <th>Tanggal / Views</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($artikelList as $art): ?>
                            <tr>
                                <td>
                                    <strong><?= e((string) $art['judul']) ?></strong>
                                    <div class="table-subtext"><?= e(mb_strimwidth((string) $art['ringkasan'], 0, 100, '...')) ?></div>
                                </td>
                                <td><span class="table-badge"><?= e((string) $art['label_kategori']) ?></span></td>
                                <td>
                                    <div><?= e((string) $art['tanggal_publikasi']) ?></div>
                                    <small><?= number_format((int) ($art['views'] ?? 0)) ?>x dibaca</small>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="artikel.php?slug=<?= urlencode((string) $art['slug']) ?>" target="_blank" class="btn-table btn-table-view">Lihat</a>
                                        <a href="portal.php?tab=artikel&edit_artikel=<?= (int) ($art['id'] ?? 0) ?>" class="btn-table btn-table-edit">Edit</a>
                                        <?php if ($pdo instanceof PDO): ?>
                                        <form action="portal.php?tab=artikel" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="cms_action" value="delete_artikel">
                                            <input type="hidden" name="redirect_tab" value="artikel">
                                            <input type="hidden" name="id" value="<?= (int) ($art['id'] ?? 0) ?>">
                                            <button type="submit" class="btn-table btn-table-delete">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($activeTab === 'jurusan' && $canManageSiteData): ?>
            <?php if ($editJurusan): ?>
            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <div>
                        <h2>Edit Program Jurusan: <?= e((string) $editJurusan['kode']) ?></h2>
                        <p class="panel-sub">Perbarui informasi jurusan yang tampil di beranda website.</p>
                    </div>
                    <a href="portal.php?tab=jurusan" class="btn btn-sm-outline">Batal Edit</a>
                </div>
                <form action="portal.php?tab=jurusan" method="POST" class="portal-cms-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="cms_action" value="update_jurusan">
                    <input type="hidden" name="redirect_tab" value="jurusan">
                    <input type="hidden" name="id" value="<?= (int) ($editJurusan['id'] ?? 0) ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="jurNama">Nama Jurusan *</label>
                            <input type="text" id="jurNama" name="nama" required value="<?= e((string) $editJurusan['nama']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="jurTagline">Tagline *</label>
                            <input type="text" id="jurTagline" name="tagline" required value="<?= e((string) $editJurusan['tagline']) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="jurDeskripsi">Deskripsi *</label>
                        <textarea id="jurDeskripsi" name="deskripsi" rows="3" required><?= e((string) $editJurusan['deskripsi']) ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jurFasilitas">Fasilitas Utama</label>
                            <textarea id="jurFasilitas" name="fasilitas" rows="2"><?= e((string) $editJurusan['fasilitas']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="jurProspek">Prospek Karir</label>
                            <textarea id="jurProspek" name="prospek_karir" rows="2"><?= e((string) $editJurusan['prospek_karir']) ?></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jurSertifikasi">Sertifikasi</label>
                            <input type="text" id="jurSertifikasi" name="sertifikasi" value="<?= e((string) $editJurusan['sertifikasi']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="jurSlug">Slug Artikel Terkait</label>
                            <input type="text" id="jurSlug" name="artikel_slug" value="<?= e((string) $editJurusan['artikel_slug']) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-green" <?= !($pdo instanceof PDO) ? 'disabled' : '' ?>>Simpan Perubahan Jurusan</button>
                </form>
            </section>
            <?php endif; ?>

            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <h2>Daftar Program Keahlian (TKJ, AKL, TKR)</h2>
                    <span class="panel-tag"><?= count($jurusanList) ?> Jurusan</span>
                </div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama &amp; Tagline</th>
                                <th>Sertifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jurusanList as $jur): ?>
                            <tr>
                                <td><span class="table-badge"><?= e((string) $jur['kode']) ?></span></td>
                                <td>
                                    <strong><?= e((string) $jur['nama']) ?></strong>
                                    <div class="table-subtext"><?= e((string) $jur['tagline']) ?></div>
                                </td>
                                <td><?= e((string) $jur['sertifikasi']) ?></td>
                                <td>
                                    <a href="portal.php?tab=jurusan&edit_jurusan=<?= (int) ($jur['id'] ?? 0) ?>" class="btn-table btn-table-edit">Edit</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($activeTab === 'kelebihan' && $canManageSiteData): ?>
            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <div>
                        <h2><?= $editKelebihan ? 'Edit Keunggulan Sekolah' : 'Tambah Keunggulan Sekolah' ?></h2>
                        <p class="panel-sub">Kartu ini tampil pada bagian "Kelebihan Utama SMK Bangun Nusa Bangsa".</p>
                    </div>
                    <?php if ($editKelebihan): ?>
                        <a href="portal.php?tab=kelebihan" class="btn btn-sm-outline">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <form action="portal.php?tab=kelebihan" method="POST" class="portal-cms-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="cms_action" value="save_kelebihan">
                    <input type="hidden" name="redirect_tab" value="kelebihan">
                    <input type="hidden" name="id" value="<?= e((string) ($editKelebihan['id'] ?? 0)) ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="kelJudul">Judul Keunggulan *</label>
                            <input type="text" id="kelJudul" name="judul" required value="<?= e((string) ($editKelebihan['judul'] ?? '')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="kelBadge">Badge Sorotan</label>
                            <input type="text" id="kelBadge" name="highlight_badge" value="<?= e((string) ($editKelebihan['highlight_badge'] ?? 'Unggulan')) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="kelIkon">Ikon</label>
                            <?php $ikonVal = (string) ($editKelebihan['ikon'] ?? 'shield-check'); ?>
                            <select id="kelIkon" name="ikon">
                                <option value="briefcase" <?= $ikonVal === 'briefcase' ? 'selected' : '' ?>>Briefcase (Industri)</option>
                                <option value="award" <?= $ikonVal === 'award' ? 'selected' : '' ?>>Award (Sertifikasi)</option>
                                <option value="cpu" <?= $ikonVal === 'cpu' ? 'selected' : '' ?>>CPU (Lab Modern)</option>
                                <option value="graduation-cap" <?= $ikonVal === 'graduation-cap' ? 'selected' : '' ?>>Topi Wisuda (PTN)</option>
                                <option value="building" <?= $ikonVal === 'building' ? 'selected' : '' ?>>Gedung (BKK)</option>
                                <option value="shield-check" <?= $ikonVal === 'shield-check' ? 'selected' : '' ?>>Perisai (Karakter)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kelUrutan">Urutan Tampil</label>
                            <input type="number" id="kelUrutan" name="urutan" min="1" max="99" value="<?= e((string) ($editKelebihan['urutan'] ?? (count($kelebihanList) + 1))) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="kelRingkasan">Ringkasan *</label>
                        <textarea id="kelRingkasan" name="ringkasan" rows="2" required><?= e((string) ($editKelebihan['ringkasan'] ?? '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary-green" <?= !($pdo instanceof PDO) ? 'disabled' : '' ?>>
                        <?= $editKelebihan ? 'Simpan Perubahan Keunggulan' : 'Tambah Keunggulan' ?>
                    </button>
                </form>
            </section>

            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <h2>Daftar Keunggulan Sekolah</h2>
                    <span class="panel-tag"><?= count($kelebihanList) ?> Item</span>
                </div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Urutan</th>
                                <th>Judul &amp; Ringkasan</th>
                                <th>Badge</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kelebihanList as $kel): ?>
                            <tr>
                                <td><?= (int) ($kel['urutan'] ?? 1) ?></td>
                                <td>
                                    <strong><?= e((string) $kel['judul']) ?></strong>
                                    <div class="table-subtext"><?= e((string) $kel['ringkasan']) ?></div>
                                </td>
                                <td><span class="table-badge"><?= e((string) $kel['highlight_badge']) ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="portal.php?tab=kelebihan&edit_kelebihan=<?= (int) ($kel['id'] ?? 0) ?>" class="btn-table btn-table-edit">Edit</a>
                                        <?php if ($pdo instanceof PDO): ?>
                                        <form action="portal.php?tab=kelebihan" method="POST" onsubmit="return confirm('Hapus item keunggulan ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="cms_action" value="delete_kelebihan">
                                            <input type="hidden" name="redirect_tab" value="kelebihan">
                                            <input type="hidden" name="id" value="<?= (int) ($kel['id'] ?? 0) ?>">
                                            <button type="submit" class="btn-table btn-table-delete">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($activeTab === 'alumni' && $canManageSiteData): ?>
            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <div>
                        <h2><?= $editAlumni ? 'Edit Data Prestasi Alumni' : 'Tambah Prestasi Alumni Baru' ?></h2>
                        <p class="panel-sub">Tampil pada bagian Alumni Perusahaan Ternama &amp; Universitas Top 10.</p>
                    </div>
                    <?php if ($editAlumni): ?>
                        <a href="portal.php?tab=alumni" class="btn btn-sm-outline">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <form action="portal.php?tab=alumni" method="POST" class="portal-cms-form">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="cms_action" value="save_alumni">
                    <input type="hidden" name="redirect_tab" value="alumni">
                    <input type="hidden" name="id" value="<?= e((string) ($editAlumni['id'] ?? 0)) ?>">

                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="alNama">Nama Alumni *</label>
                            <input type="text" id="alNama" name="nama" required value="<?= e((string) ($editAlumni['nama'] ?? '')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="alAngkatan">Angkatan (Tahun)</label>
                            <input type="number" id="alAngkatan" name="angkatan" min="2015" max="2035" value="<?= e((string) ($editAlumni['angkatan'] ?? date('Y'))) ?>">
                        </div>
                        <div class="form-group">
                            <label for="alJurusan">Jurusan</label>
                            <?php $alJur = (string) ($editAlumni['jurusan_kode'] ?? 'TKJ'); ?>
                            <select id="alJurusan" name="jurusan_kode">
                                <option value="TKJ" <?= $alJur === 'TKJ' ? 'selected' : '' ?>>TKJ</option>
                                <option value="AKL" <?= $alJur === 'AKL' ? 'selected' : '' ?>>AKL</option>
                                <option value="TKR" <?= $alJur === 'TKR' ? 'selected' : '' ?>>TKR</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row-3">
                        <div class="form-group">
                            <label for="alTipe">Kategori Prestasi</label>
                            <?php $alTipe = (string) ($editAlumni['tipe'] ?? 'perusahaan'); ?>
                            <select id="alTipe" name="tipe">
                                <option value="perusahaan" <?= $alTipe === 'perusahaan' ? 'selected' : '' ?>>Perusahaan Ternama</option>
                                <option value="universitas" <?= $alTipe === 'universitas' ? 'selected' : '' ?>>Universitas Top 10</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="alInstansi">Nama Perusahaan / Universitas *</label>
                            <input type="text" id="alInstansi" name="instansi" required value="<?= e((string) ($editAlumni['instansi'] ?? '')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="alPosisi">Posisi Jabatan / Prodi *</label>
                            <input type="text" id="alPosisi" name="posisi_atau_prodi" required value="<?= e((string) ($editAlumni['posisi_atau_prodi'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="alPeringkat">Badge Keterangan</label>
                            <input type="text" id="alPeringkat" name="peringkat_info" value="<?= e((string) ($editAlumni['peringkat_info'] ?? '')) ?>" placeholder="Contoh: Top 10 PTN / BUMN Nasional">
                        </div>
                        <div class="form-group">
                            <label for="alUrutan">Urutan Tampil</label>
                            <input type="number" id="alUrutan" name="urutan" min="1" max="99" value="<?= e((string) ($editAlumni['urutan'] ?? 1)) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="alKutipan">Testimoni / Kutipan Alumni *</label>
                        <textarea id="alKutipan" name="kutipan" rows="2" required><?= e((string) ($editAlumni['kutipan'] ?? '')) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-green" <?= !($pdo instanceof PDO) ? 'disabled' : '' ?>>
                        <?= $editAlumni ? 'Simpan Perubahan Alumni' : 'Tambah Data Alumni' ?>
                    </button>
                </form>
            </section>

            <section class="portal-panel-card">
                <div class="panel-card-head">
                    <h2>Daftar Alumni Berprestasi</h2>
                    <span class="panel-tag"><?= count($alumniList) ?> Alumni</span>
                </div>
                <div class="portal-table-wrap">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Nama &amp; Jurusan</th>
                                <th>Kategori</th>
                                <th>Instansi &amp; Posisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumniList as $al): ?>
                            <tr>
                                <td>
                                    <strong><?= e((string) $al['nama']) ?></strong>
                                    <div class="table-subtext"><?= e((string) $al['jurusan_kode']) ?> &bull; Angkatan <?= e((string) $al['angkatan']) ?></div>
                                </td>
                                <td><span class="table-badge"><?= $al['tipe'] === 'universitas' ? 'Universitas Top 10' : 'Perusahaan Ternama' ?></span></td>
                                <td>
                                    <strong><?= e((string) $al['instansi']) ?></strong>
                                    <div class="table-subtext"><?= e((string) $al['posisi_atau_prodi']) ?></div>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="portal.php?tab=alumni&edit_alumni=<?= (int) ($al['id'] ?? 0) ?>" class="btn-table btn-table-edit">Edit</a>
                                        <?php if ($pdo instanceof PDO): ?>
                                        <form action="portal.php?tab=alumni" method="POST" onsubmit="return confirm('Hapus data alumni ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="cms_action" value="delete_alumni">
                                            <input type="hidden" name="redirect_tab" value="alumni">
                                            <input type="hidden" name="id" value="<?= (int) ($al['id'] ?? 0) ?>">
                                            <button type="submit" class="btn-table btn-table-delete">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        <?php elseif ($activeTab === 'ppdb' && $canManageArticles): ?>
            <section class="portal-panel-card portal-ppdb-card">
                <div class="panel-card-head">
                    <div>
                        <h2>Data Pendaftar &amp; Konsultasi PPDB Terbaru</h2>
                        <p class="panel-sub">Data masuk langsung dari formulir PPDB pada halaman utama website.</p>
                    </div>
                    <span class="panel-tag"><?= count($ppdbSubmissions) ?> Data</span>
                </div>

                <?php if (empty($ppdbSubmissions)): ?>
                    <div class="empty-state">
                        <p>Belum ada data pendaftar PPDB tersimpan di database (atau MySQL belum aktif).</p>
                    </div>
                <?php else: ?>
                    <div class="portal-table-wrap">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>Nama Calon Siswa</th>
                                    <th>Asal Sekolah</th>
                                    <th>Jurusan Minat</th>
                                    <th>WhatsApp / Email</th>
                                    <th>Pesan</th>
                                    <?php if ($canManageSiteData): ?>
                                        <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ppdbSubmissions as $row): ?>
                                <tr>
                                    <td><strong><?= e((string) $row['nama_lengkap']) ?></strong></td>
                                    <td><?= e((string) $row['asal_sekolah']) ?></td>
                                    <td><span class="table-badge"><?= e((string) $row['jurusan_minat']) ?></span></td>
                                    <td>
                                        <div><?= e((string) $row['no_whatsapp']) ?></div>
                                        <small><?= e((string) $row['email']) ?></small>
                                    </td>
                                    <td><?= e((string) ($row['pesan'] !== '' ? $row['pesan'] : '-')) ?></td>
                                    <?php if ($canManageSiteData): ?>
                                    <td>
                                        <form action="portal.php?tab=ppdb" method="POST" onsubmit="return confirm('Hapus data pendaftar PPDB ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="cms_action" value="delete_ppdb">
                                            <input type="hidden" name="redirect_tab" value="ppdb">
                                            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                            <button type="submit" class="btn-table btn-table-delete">Hapus</button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="portal-quick-links">
            <a href="index.php#jurusan" class="quick-link-tile">
                <strong>Program Keahlian</strong>
                <span>Lihat kurikulum TKJ, AKL &amp; TKR</span>
            </a>
            <a href="artikel.php" class="quick-link-tile">
                <strong>Direktori Artikel</strong>
                <span>Berita prestasi &amp; panduan PTN Top 10</span>
            </a>
            <a href="index.php#alumni" class="quick-link-tile">
                <strong>Jejak Alumni</strong>
                <span>Alumni di perusahaan ternama &amp; PTN</span>
            </a>
        </section>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
