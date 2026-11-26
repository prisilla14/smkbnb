<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/data_repository.php';

$dbError = null;
$pdo     = getDbConnection($dbError);

$slug = trim((string) ($_GET['slug'] ?? ''));
$isHome = false;

if ($slug !== '') {
    $artikel = getArtikelBySlug($pdo, $slug);
    $related = getRelatedArtikel($pdo, $slug, 3);

    if (!$artikel) {
        http_response_code(404);
        $pageTitle = 'Artikel Tidak Ditemukan — SMK Bangun Nusa Bangsa';
        require __DIR__ . '/includes/header.php';
        ?>
        <main class="article-page-main">
            <div class="container">
                <div class="not-found-box">
                    <h1>Artikel Tidak Ditemukan</h1>
                    <p>Maaf, artikel yang Anda cari tidak tersedia atau database MySQL belum aktif.</p>
                    <a href="index.php#artikel" class="btn btn-maroon">&larr; Kembali ke Daftar Artikel</a>
                </div>
            </div>
        </main>
        <?php
        require __DIR__ . '/includes/footer.php';
        exit;
    }

    $pageTitle = $artikel['judul'] . ' — SMK Bangun Nusa Bangsa';
    $pageDesc  = $artikel['ringkasan'];
    require __DIR__ . '/includes/header.php';
    ?>
    <main class="article-page-main">
        <div class="article-hero-banner">
            <div class="container">
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <a href="index.php">Beranda</a>
                    <span>/</span>
                    <a href="artikel.php">Artikel</a>
                    <span>/</span>
                    <span><?= e($artikel['label_kategori']) ?></span>
                </nav>

                <span class="art-detail-badge"><?= e($artikel['label_kategori']) ?></span>
                <h1><?= e($artikel['judul']) ?></h1>

                <div class="art-detail-meta">
                    <span>Ditulis oleh <strong><?= e($artikel['penulis']) ?></strong></span>
                    <span>&bull;</span>
                    <span><?= e(formatTanggalIndo((string) $artikel['tanggal_publikasi'])) ?></span>
                    <span>&bull;</span>
                    <span><?= e($artikel['waktu_baca']) ?></span>
                    <span>&bull;</span>
                    <span><?= number_format((int) $artikel['views']) ?>x dibaca</span>
                </div>
            </div>
        </div>

        <div class="container article-layout">
            <article class="article-reader">
                <figure class="article-cover">
                    <img src="<?= e($artikel['gambar']) ?>" alt="<?= e($artikel['judul']) ?>">
                </figure>

                <div class="article-prose">
                    <?= $artikel['konten'] ?>
                </div>

                <div class="article-share-bar">
                    <a href="index.php#artikel" class="btn btn-outline-maroon">&larr; Kembali ke Semua Artikel</a>
                    <a href="index.php#daftar" class="btn btn-primary-green">Konsultasi &amp; Daftar PPDB &rarr;</a>
                </div>
            </article>

            <aside class="article-sidebar">
                <div class="sidebar-card sidebar-cta">
                    <span class="sidebar-kicker">PPDB 2026/2027</span>
                    <h3>Tertarik Masuk SMK Bangun Nusa Bangsa?</h3>
                    <p>Pilih jurusan TKJ, AKL, atau TKR dan raih peluang kerja di perusahaan ternama maupun kuliah di Top 10 PTN.</p>
                    <a href="index.php#daftar" class="btn btn-primary-green btn-block">Daftar Minat Sekarang</a>
                </div>

                <?php if (!empty($related)): ?>
                <div class="sidebar-card">
                    <h3>Artikel Pilihan Lainnya</h3>
                    <div class="related-list">
                        <?php foreach ($related as $rel): ?>
                        <a href="artikel.php?slug=<?= urlencode((string) $rel['slug']) ?>" class="related-item">
                            <img src="<?= e($rel['gambar']) ?>" alt="<?= e($rel['judul']) ?>">
                            <div>
                                <span class="rel-cat"><?= e($rel['label_kategori']) ?></span>
                                <h4><?= e($rel['judul']) ?></h4>
                                <small><?= e($rel['waktu_baca']) ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Mode Direktori Artikel (tanpa slug)
$kategoriFilter = trim((string) ($_GET['kategori'] ?? 'semua'));
$searchQuery    = trim((string) ($_GET['q'] ?? ''));
$artikelList    = getArtikelList($pdo, $kategoriFilter, $searchQuery);

$pageTitle = 'Direktori Artikel & Berita — SMK Bangun Nusa Bangsa';
require __DIR__ . '/includes/header.php';
?>
<main class="article-page-main">
    <div class="article-hero-banner">
        <div class="container">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <a href="index.php">Beranda</a>
                <span>/</span>
                <span>Semua Artikel</span>
            </nav>
            <h1>Artikel &amp; Wawasan <span class="text-bright-green">SMK Bangun Nusa Bangsa</span></h1>
            <p class="dir-subtitle">Kumpulan artikel jurusan TKJ, AKL, TKR, keunggulan sekolah, alumni perusahaan ternama, dan jalur Universitas Top 10.</p>
        </div>
    </div>

    <div class="container" style="padding: 3rem 1.25rem;">
        <form class="dir-search-bar" method="GET" action="artikel.php">
            <select name="kategori" aria-label="Kategori">
                <option value="semua" <?= $kategoriFilter === 'semua' ? 'selected' : '' ?>>Semua Kategori</option>
                <option value="jurusan" <?= $kategoriFilter === 'jurusan' ? 'selected' : '' ?>>Jurusan (TKJ, AKL, TKR)</option>
                <option value="keunggulan" <?= $kategoriFilter === 'keunggulan' ? 'selected' : '' ?>>Keunggulan Sekolah</option>
                <option value="alumni" <?= $kategoriFilter === 'alumni' ? 'selected' : '' ?>>Alumni Perusahaan Ternama</option>
                <option value="universitas" <?= $kategoriFilter === 'universitas' ? 'selected' : '' ?>>Universitas Top 10</option>
            </select>
            <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Cari judul atau topik artikel...">
            <button type="submit" class="btn btn-maroon">Cari Artikel</button>
        </form>

        <div class="artikel-grid">
            <?php foreach ($artikelList as $art): ?>
            <article class="artikel-card">
                <a href="artikel.php?slug=<?= urlencode((string) $art['slug']) ?>" class="artikel-thumb">
                    <img src="<?= e($art['gambar']) ?>" alt="<?= e($art['judul']) ?>" loading="lazy">
                    <span class="art-cat-badge cat-<?= e($art['kategori']) ?>"><?= e($art['label_kategori']) ?></span>
                </a>
                <div class="artikel-content">
                    <div class="art-meta">
                        <span><?= e(formatTanggalIndo((string) $art['tanggal_publikasi'])) ?></span>
                        <span>&bull;</span>
                        <span><?= e($art['waktu_baca']) ?></span>
                    </div>
                    <h3>
                        <a href="artikel.php?slug=<?= urlencode((string) $art['slug']) ?>">
                            <?= e($art['judul']) ?>
                        </a>
                    </h3>
                    <p><?= e($art['ringkasan']) ?></p>
                    <div class="art-footer">
                        <span class="art-author">Oleh <?= e($art['penulis']) ?></span>
                        <a href="artikel.php?slug=<?= urlencode((string) $art['slug']) ?>" class="read-more-link">Baca Selengkapnya &rarr;</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
