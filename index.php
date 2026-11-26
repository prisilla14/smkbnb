<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/data_repository.php';

$dbError = null;
$pdo     = getDbConnection($dbError);

$jurusanList   = getJurusanList($pdo);
$kelebihanList = getKelebihanList($pdo);
$artikelList   = getArtikelList($pdo, 'semua', '');
$alumniList    = getAlumniList($pdo);

$alumniPerusahaan = array_values(array_filter($alumniList, static fn(array $a): bool => $a['tipe'] === 'perusahaan'));
$alumniUniversitas = array_values(array_filter($alumniList, static fn(array $a): bool => $a['tipe'] === 'universitas'));

$isHome    = true;
$pageTitle = 'SMK Bangun Nusa Bangsa — Sekolah Vokasi Unggulan TKJ, AKL & TKR';

require __DIR__ . '/includes/header.php';

/**
 * Helper ikon SVG inline ringan agar tidak bergantung pada CDN ikon eksternal.
 */
function renderIconSvg(string $name): string
{
    return match ($name) {
        'briefcase' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M2 13h20"/></svg>',
        'award' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>',
        'cpu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/></svg>',
        'graduation-cap' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
        'building' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M16 10h.01M16 14h.01M8 10h.01M8 14h.01"/></svg>',
        default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
    };
}
?>

<?php if ($dbError !== null): ?>
<div class="db-notice" role="status">
    <div class="container db-notice-inner">
        <span><strong>Info Mode Database:</strong> <?= e($dbError) ?> (Menampilkan data cadangan).</span>
    </div>
</div>
<?php endif; ?>

<!-- ==================== HERO SECTION ==================== -->
<section class="hero" id="beranda">
    <div class="hero-bg-shapes" aria-hidden="true">
        <div class="blob blob-green"></div>
        <div class="blob blob-maroon"></div>
    </div>

    <div class="container hero-grid">
        <div class="hero-content">
            <div class="hero-pill">
                <span class="pill-dot"></span>
                SMK Pusat Keunggulan • Akreditasi A
            </div>
            <h1>
                Bangun Masa Depan Gemilang di
                <span class="text-highlight">SMK Bangun Nusa Bangsa</span>
            </h1>
            <p class="hero-subtitle">
                Sekolah kejuruan modern berkarakter juara dengan jurusan unggulan <strong>TKJ</strong>, <strong>AKL</strong>, dan <strong>TKR</strong>. Siap berkarier di perusahaan multinasional maupun menembus <strong>Top 10 Universitas Indonesia</strong>.
            </p>

            <div class="hero-actions">
                <a href="#artikel" class="btn btn-primary-green">
                    Baca Artikel &amp; Prestasi
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <a href="#jurusan" class="btn btn-outline-light">
                    Lihat Program Jurusan
                </a>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <strong>95%</strong>
                    <span>Terserap Kerja &amp; PTN</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <strong>50+</strong>
                    <span>Mitra Perusahaan</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <strong>Top 10</strong>
                    <span>Jalur Kampus PTN</span>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card-frame">
                <img src="assets/img/bnb.jpg" alt="Gedung Sekolah SMK Bangun Nusa Bangsa" class="hero-img">
                <div class="floating-badge badge-top">
                    <span class="fb-icon fb-green">&#10003;</span>
                    <div>
                        <strong>Sertifikasi BNSP</strong>
                        <small>Diakui Industri Nasional</small>
                    </div>
                </div>
                <div class="floating-badge badge-bottom">
                    <span class="fb-icon fb-maroon">&#9733;</span>
                    <div>
                        <strong>Alumni di Telkom, Astra &amp; UI/ITB</strong>
                        <small>Jejak Nyata Lulusan Berprestasi</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== KELEBIHAN SEKOLAH ==================== -->
<section class="section section-keunggulan" id="keunggulan">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker kicker-maroon">Mengapa Memilih Kami</span>
            <h2>Kelebihan Utama <span class="accent-green">SMK Bangun Nusa Bangsa</span></h2>
            <p>Ekosistem pendidikan vokasi yang memadukan praktik industri nyata, sertifikasi profesi resmi, dan pembinaan akademik intensif.</p>
        </div>

        <div class="keunggulan-grid">
            <?php foreach ($kelebihanList as $item): ?>
            <article class="keunggulan-card">
                <div class="k-card-top">
                    <div class="k-icon-box">
                        <?= renderIconSvg((string) $item['ikon']) ?>
                    </div>
                    <span class="k-badge"><?= e($item['highlight_badge']) ?></span>
                </div>
                <h3><?= e($item['judul']) ?></h3>
                <p><?= e($item['ringkasan']) ?></p>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="section-cta-center">
            <a href="artikel.php?slug=keunggulan-smk-bangun-nusa-bangsa-siap-kerja-kuliah-wirausaha" class="btn btn-maroon">
                Baca Artikel Lengkap: 7 Keunggulan SMK Bangun Nusa Bangsa &rarr;
            </a>
        </div>
    </div>
</section>

<!-- ==================== PROGRAM JURUSAN (TKJ, AKL, TKR) ==================== -->
<section class="section section-jurusan" id="jurusan">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker kicker-green">Kompetensi Keahlian</span>
            <h2>3 Jurusan Favorit <span class="accent-maroon">Siap Industri</span></h2>
            <p>Pilih bidang keahlian sesuai passion Anda—didukung laboratorium mutakhir dan instruktur praktisi berpengalaman.</p>
        </div>

        <div class="jurusan-grid">
            <?php foreach ($jurusanList as $jurusan): ?>
            <article class="jurusan-card">
                <div class="jurusan-media">
                    <img src="<?= e($jurusan['gambar']) ?>" alt="<?= e($jurusan['nama']) ?>" loading="lazy">
                    <span class="jurusan-kode-pill"><?= e($jurusan['kode']) ?></span>
                </div>
                <div class="jurusan-body">
                    <h3><?= e($jurusan['nama']) ?></h3>
                    <p class="jurusan-tagline"><?= e($jurusan['tagline']) ?></p>
                    <p class="jurusan-desc"><?= e($jurusan['deskripsi']) ?></p>

                    <div class="jurusan-meta-box">
                        <div>
                            <span class="meta-label">Fasilitas Utama:</span>
                            <p><?= e($jurusan['fasilitas']) ?></p>
                        </div>
                        <div>
                            <span class="meta-label">Prospek Karir:</span>
                            <p><?= e($jurusan['prospek_karir']) ?></p>
                        </div>
                        <div>
                            <span class="meta-label">Sertifikasi:</span>
                            <span class="cert-tag"><?= e($jurusan['sertifikasi']) ?></span>
                        </div>
                    </div>

                    <div class="jurusan-footer">
                        <a href="artikel.php?slug=<?= urlencode((string) $jurusan['artikel_slug']) ?>" class="btn btn-jurusan-read">
                            Baca Artikel Jurusan <?= e($jurusan['kode']) ?> &rarr;
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== ARTIKEL UNGGULAN & TERBARU ==================== -->
<section class="section section-artikel" id="artikel">
    <div class="container">
        <div class="section-header">
            <span class="section-kicker kicker-maroon">Wawasan &amp; Inspirasi</span>
            <h2>Artikel Pilihan <span class="accent-green">SMK Bangun Nusa Bangsa</span></h2>
            <p>Jelajahi kisah mendalam seputar jurusan TKJ, AKL, TKR, keunggulan sekolah, kiprah alumni perusahaan ternama, dan rahasia tembus Top 10 Universitas.</p>
        </div>

        <!-- Filter & Pencarian Interaktif -->
        <div class="artikel-controls">
            <div class="filter-pills" role="tablist" aria-label="Filter Kategori Artikel">
                <button type="button" class="filter-btn active" data-filter="semua">Semua Artikel</button>
                <button type="button" class="filter-btn" data-filter="jurusan">Jurusan (TKJ/AKL/TKR)</button>
                <button type="button" class="filter-btn" data-filter="keunggulan">Keunggulan Sekolah</button>
                <button type="button" class="filter-btn" data-filter="alumni">Alumni Perusahaan</button>
                <button type="button" class="filter-btn" data-filter="universitas">Universitas Top 10</button>
            </div>

            <div class="search-box">
                <input type="search" id="articleSearch" placeholder="Cari artikel (mis. TKJ, Telkom, UI, TKR)..." aria-label="Cari artikel">
            </div>
        </div>

        <?php if (empty($artikelList)): ?>
            <div class="empty-state">
                <p>Belum ada artikel di database. Pastikan layanan MySQL di XAMPP aktif lalu muat ulang halaman ini.</p>
            </div>
        <?php else: ?>
            <div class="artikel-grid" id="artikelGrid">
                <?php foreach ($artikelList as $art): ?>
                <article class="artikel-card"
                         data-category="<?= e($art['kategori']) ?>"
                         data-search="<?= e(mb_strtolower($art['judul'] . ' ' . $art['ringkasan'] . ' ' . $art['label_kategori'])) ?>">
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
                            <a href="artikel.php?slug=<?= urlencode((string) $art['slug']) ?>" class="read-more-link">
                                Baca Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <p class="no-filter-results" id="noFilterResults" hidden>Tidak ada artikel yang cocok dengan kata kunci pencarian Anda.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ==================== ALUMNI PERUSAHAAN TERNAMA & UNIVERSITAS TOP 10 ==================== -->
<section class="section section-alumni" id="alumni">
    <div class="container">
        <div class="section-header section-header-light">
            <span class="section-kicker kicker-bright-green">Jejak Prestasi Lulusan</span>
            <h2>Alumni di <span class="text-bright-green">Perusahaan Ternama</span> &amp; <span class="text-bright-green">Universitas Top 10</span></h2>
            <p>Bukti nyata kualitas lulusan SMK Bangun Nusa Bangsa: berkarier profesional di BUMN &amp; korporasi top sekaligus menembus kampus terbaik Indonesia.</p>
        </div>

        <div class="alumni-tabs">
            <button type="button" class="alumni-tab-btn active" data-tab="tab-perusahaan">
                Perusahaan Ternama
            </button>
            <button type="button" class="alumni-tab-btn" data-tab="tab-universitas">
                Universitas Top 10 Indonesia
            </button>
        </div>

        <!-- Tab 1: Perusahaan Ternama -->
        <div class="alumni-panel active" id="tab-perusahaan">
            <div class="alumni-spotlight">
                <img src="assets/img/alumni-perusahaan.jpg" alt="Alumni SMK Bangun Nusa Bangsa di Perusahaan Ternama">
                <div class="spotlight-info">
                    <span class="spotlight-tag">Karier Korporasi &amp; BUMN</span>
                    <h3>Menembus Telkom Indonesia, Astra, BCA, Bank Mandiri &amp; GoTo</h3>
                    <p>Melalui Bursa Kerja Khusus (BKK) dan sertifikasi kompetensi industri, alumni kami direkrut langsung sebelum kelulusan.</p>
                    <a href="artikel.php?slug=jejak-emas-alumni-smk-bangun-nusa-bangsa-perusahaan-ternama" class="btn btn-primary-green">
                        Baca Kisah Lengkap Alumni Perusahaan &rarr;
                    </a>
                </div>
            </div>

            <div class="alumni-cards-grid">
                <?php foreach ($alumniPerusahaan as $al): ?>
                <article class="alumni-card">
                    <div class="alumni-card-head">
                        <span class="alumni-jurusan-tag"><?= e($al['jurusan_kode']) ?> &bull; Angkatan <?= e((string) $al['angkatan']) ?></span>
                        <span class="alumni-rank-tag"><?= e($al['peringkat_info']) ?></span>
                    </div>
                    <h4><?= e($al['nama']) ?></h4>
                    <p class="alumni-instansi"><?= e($al['instansi']) ?></p>
                    <p class="alumni-posisi"><?= e($al['posisi_atau_prodi']) ?></p>
                    <blockquote>&ldquo;<?= e($al['kutipan']) ?>&rdquo;</blockquote>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tab 2: Universitas Top 10 -->
        <div class="alumni-panel" id="tab-universitas">
            <div class="alumni-spotlight">
                <img src="assets/img/alumni-universitas.svg" alt="Alumni SMK Bangun Nusa Bangsa di Universitas Top 10 Indonesia">
                <div class="spotlight-info">
                    <span class="spotlight-tag">Program Bimbingan SNBP &amp; SNBT-UTBK</span>
                    <h3>Lulusan SMK Tembus UI, ITB, UGM, ITS, IPB, Unair, Undip, Unpad &amp; UB</h3>
                    <p>Program Klinik Penalaran &amp; Kurasi Portofolio Prestasi membuktikan lulusan vokasi mampu bersaing di Top 10 PTN terbaik Indonesia.</p>
                    <a href="artikel.php?slug=lulusan-smk-tembus-top-10-universitas-indonesia-ui-itb-ugm-its" class="btn btn-primary-green">
                        Baca Strategi Tembus Top 10 Universitas &rarr;
                    </a>
                </div>
            </div>

            <div class="alumni-cards-grid">
                <?php foreach ($alumniUniversitas as $al): ?>
                <article class="alumni-card alumni-card-uni">
                    <div class="alumni-card-head">
                        <span class="alumni-jurusan-tag"><?= e($al['jurusan_kode']) ?> &bull; Angkatan <?= e((string) $al['angkatan']) ?></span>
                        <span class="alumni-rank-tag rank-green"><?= e($al['peringkat_info']) ?></span>
                    </div>
                    <h4><?= e($al['nama']) ?></h4>
                    <p class="alumni-instansi"><?= e($al['instansi']) ?></p>
                    <p class="alumni-posisi"><?= e($al['posisi_atau_prodi']) ?></p>
                    <blockquote>&ldquo;<?= e($al['kutipan']) ?>&rdquo;</blockquote>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ==================== FORMULIR PPDB & KONSULTASI ==================== -->
<section class="section section-daftar" id="daftar">
    <div class="container">
        <div class="daftar-wrapper">
            <div class="daftar-info">
                <span class="section-kicker kicker-bright-green">PPDB &amp; Konsultasi Jurusan</span>
                <h2>Siap Bergabung Bersama Keluarga Besar SMK Bangun Nusa Bangsa?</h2>
                <p>Daftarkan minat jurusan Anda atau konsultasikan pilihan karir TKJ, AKL, dan TKR bersama tim akademik dan Bursa Kerja Khusus kami.</p>

                <ul class="daftar-checklist">
                    <li>Gratis biaya konsultasi minat &amp; bakat jurusan</li>
                    <li>Informasi beasiswa prestasi &amp; jalur Top 10 PTN</li>
                    <li>Jadwal kunjungan langsung ke Lab TKJ, Mini Bank AKL &amp; Bengkel TKR</li>
                </ul>
            </div>

            <form class="daftar-form" id="ppdbForm" action="api/submit_kontak.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap Siswa *</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" required placeholder="Contoh: Pratama Putra">
                    </div>
                    <div class="form-group">
                        <label for="asal_sekolah">Asal Sekolah (SMP/MTs) *</label>
                        <input type="text" id="asal_sekolah" name="asal_sekolah" required placeholder="Contoh: SMPN 1 Nusantara">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Alamat Email *</label>
                        <input type="email" id="email" name="email" required placeholder="nama@email.com">
                    </div>
                    <div class="form-group">
                        <label for="no_whatsapp">Nomor WhatsApp Aktif *</label>
                        <input type="tel" id="no_whatsapp" name="no_whatsapp" required placeholder="0812xxxxxxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label for="jurusan_minat">Pilihan Jurusan Minat *</label>
                    <select id="jurusan_minat" name="jurusan_minat" required>
                        <option value="TKJ - Teknik Komputer & Jaringan">TKJ - Teknik Komputer &amp; Jaringan</option>
                        <option value="AKL - Akuntansi & Keuangan Lembaga">AKL - Akuntansi &amp; Keuangan Lembaga</option>
                        <option value="TKR - Teknik Kendaraan Ringan">TKR - Teknik Kendaraan Ringan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pesan">Pertanyaan / Rencana Masa Depan</label>
                    <textarea id="pesan" name="pesan" rows="3" placeholder="Tanyakan seputar program magang, beasiswa, atau persiapan universitas..."></textarea>
                </div>

                <button type="submit" class="btn btn-submit-ppdb" id="ppdbSubmitBtn">
                    Kirim Pendaftaran &amp; Konsultasi
                </button>
                <div class="form-feedback" id="ppdbFeedback" role="alert" hidden></div>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
