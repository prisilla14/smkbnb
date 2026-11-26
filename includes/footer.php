<?php
declare(strict_types=1);
$navPrefix = ($isHome ?? false) ? '' : 'index.php';
?>
    <footer class="site-footer">
        <div class="footer-glow"></div>
        <div class="container footer-grid">
            <div class="footer-brand-col">
                <div class="brand footer-brand">
                    <div class="brand-emblem">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect width="40" height="40" rx="11" fill="url(#bnbFooterGrad)"/>
                            <path d="M20 7L32 13V21C32 27.9 26.8 33.4 20 35C13.2 33.4 8 27.9 8 21V13L20 7Z" stroke="white" stroke-width="2.2" stroke-linejoin="round"/>
                            <path d="M15 21L18.5 24.5L25.5 16.5" stroke="#4ADE80" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <defs>
                                <linearGradient id="bnbFooterGrad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#991B38"/>
                                    <stop offset="1" stop-color="#22C55E"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div class="brand-text">
                        <span class="brand-name">SMK BANGUN NUSA BANGSA</span>
                        <span class="brand-sub">Siap Kerja • Siap Kuliah • Siap Wirausaha</span>
                    </div>
                </div>
                <p class="footer-desc">
                    Lembaga pendidikan vokasi unggulan berakreditasi A yang memadukan kurikulum industri, sertifikasi BNSP, dan pembinaan intensif menuju perusahaan ternama serta Top 10 Universitas di Indonesia.
                </p>
                <div class="footer-badges">
                    <span class="f-badge">Akreditasi A</span>
                    <span class="f-badge f-badge-green">Sekolah Adiwiyata</span>
                    <span class="f-badge">LSP P1 BNSP</span>
                </div>
            </div>

            <div class="footer-col">
                <h4>Kompetensi Keahlian</h4>
                <ul>
                    <li><a href="artikel.php?slug=menguasai-jaringan-dan-cloud-jurusan-tkj-smk-bangun-nusa-bangsa">Teknik Komputer &amp; Jaringan (TKJ)</a></li>
                    <li><a href="artikel.php?slug=cerdas-finansial-pajak-digital-jurusan-akuntansi-akl-era-modern">Akuntansi &amp; Keuangan Lembaga (AKL)</a></li>
                    <li><a href="artikel.php?slug=dari-mesin-konvensional-ke-kendaraan-listrik-bengkel-modern-tkr">Teknik Kendaraan Ringan (TKR)</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Artikel &amp; Informasi</h4>
                <ul>
                    <li><a href="artikel.php?slug=keunggulan-smk-bangun-nusa-bangsa-siap-kerja-kuliah-wirausaha">7 Keunggulan Sekolah</a></li>
                    <li><a href="artikel.php?slug=jejak-emas-alumni-smk-bangun-nusa-bangsa-perusahaan-ternama">Alumni di Perusahaan Ternama</a></li>
                    <li><a href="artikel.php?slug=lulusan-smk-tembus-top-10-universitas-indonesia-ui-itb-ugm-its">Jalur Universitas Top 10</a></li>
                    <li><a href="artikel.php">Semua Artikel Sekolah</a></li>
                    <li><a href="<?= !empty($_SESSION['portal_user']) ? 'portal.php' : 'login.php' ?>"><?= !empty($_SESSION['portal_user']) ? 'Dashboard Portal Sekolah' : 'Login Portal Siswa &amp; Guru' ?></a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Hubungi Kampus</h4>
                <ul class="contact-list">
                    <li>Jl. Pendidikan Vokasi No. 88, Kawasan Edukasi Bangun Nusa</li>
                    <li>Telp: (021) 8890-2026</li>
                    <li>Email: info@smkbangunnusabangsa.sch.id</li>
                    <li>Senin - Sabtu: 07.00 - 16.00 WIB</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <p>&copy; <?= date('Y') ?> <strong>SMK Bangun Nusa Bangsa</strong>. Seluruh hak cipta dilindungi.</p>
                <p class="footer-tech">Dibangun dengan PHP 8 &amp; MySQL • Tema Hijau Terang &amp; Merah Hati</p>
            </div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop" aria-label="Kembali ke atas">&#8593;</button>
    <script src="assets/js/main.js?v=1.1"></script>
</body>
</html>
