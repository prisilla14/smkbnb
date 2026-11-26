-- Database Schema & Seed Data for SMK Bangun Nusa Bangsa
CREATE DATABASE IF NOT EXISTS `smk_bangun_nusa_bangsa`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `smk_bangun_nusa_bangsa`;

-- 1. Tabel Jurusan (TKJ, AKL, TKR)
CREATE TABLE IF NOT EXISTS `jurusan` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode` VARCHAR(16) NOT NULL UNIQUE,
  `nama` VARCHAR(120) NOT NULL,
  `tagline` VARCHAR(200) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `prospek_karir` TEXT NOT NULL,
  `fasilitas` TEXT NOT NULL,
  `sertifikasi` VARCHAR(200) NOT NULL,
  `gambar` VARCHAR(255) NOT NULL,
  `artikel_slug` VARCHAR(160) NOT NULL,
  `urutan` TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Kelebihan Sekolah
CREATE TABLE IF NOT EXISTS `kelebihan` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `ringkasan` TEXT NOT NULL,
  `ikon` VARCHAR(40) NOT NULL,
  `highlight_badge` VARCHAR(60) NOT NULL,
  `urutan` TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Artikel
CREATE TABLE IF NOT EXISTS `artikel` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `judul` VARCHAR(220) NOT NULL,
  `kategori` ENUM('jurusan', 'keunggulan', 'alumni', 'universitas', 'berita') NOT NULL DEFAULT 'berita',
  `label_kategori` VARCHAR(60) NOT NULL,
  `ringkasan` TEXT NOT NULL,
  `konten` LONGTEXT NOT NULL,
  `gambar` VARCHAR(255) NOT NULL,
  `penulis` VARCHAR(100) NOT NULL DEFAULT 'Tim Redaksi SMK BNB',
  `waktu_baca` VARCHAR(30) NOT NULL DEFAULT '5 Menit Baca',
  `unggulan` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `tanggal_publikasi` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Alumni & Prestasi (Perusahaan Ternama & Universitas Top 10)
CREATE TABLE IF NOT EXISTS `alumni_prestasi` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(120) NOT NULL,
  `angkatan` YEAR NOT NULL,
  `jurusan_kode` VARCHAR(16) NOT NULL,
  `tipe` ENUM('perusahaan', 'universitas') NOT NULL,
  `instansi` VARCHAR(160) NOT NULL,
  `posisi_atau_prodi` VARCHAR(160) NOT NULL,
  `peringkat_info` VARCHAR(100) DEFAULT NULL,
  `kutipan` TEXT NOT NULL,
  `urutan` TINYINT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Pendaftaran / Konsultasi PPDB & Kontak
CREATE TABLE IF NOT EXISTS `pesan_ppdb` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(120) NOT NULL,
  `asal_sekolah` VARCHAR(140) NOT NULL,
  `email` VARCHAR(140) NOT NULL,
  `no_whatsapp` VARCHAR(30) NOT NULL,
  `jurusan_minat` VARCHAR(60) NOT NULL,
  `pesan` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Akun Portal Sekolah (Siswa, Guru & Admin)
CREATE TABLE IF NOT EXISTS `portal_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `nama_lengkap` VARCHAR(120) NOT NULL,
  `role` ENUM('siswa', 'guru', 'admin') NOT NULL DEFAULT 'siswa',
  `jurusan_kode` VARCHAR(16) DEFAULT NULL,
  `kelas_atau_jabatan` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- SEED DATA (IDEMPOTENT)
-- ==========================================

INSERT INTO `jurusan` (`kode`, `nama`, `tagline`, `deskripsi`, `prospek_karir`, `fasilitas`, `sertifikasi`, `gambar`, `artikel_slug`, `urutan`) VALUES
('TKJ', 'Teknik Komputer & Jaringan', 'Membangun Infrastruktur Digital, Cloud & Keamanan Siber',
 'Kompetensi keahlian yang mencetak teknisi jaringan, administrator sistem Linux/Windows, hingga spesialis fiber optik dan keamanan siber berbasis praktik langsung industri telekomunikasi.',
 'Network Engineer, Cloud Administrator, Fiber Optic Technician, Cyber Security Analyst, IT Support Specialist',
 'Lab Cisco & MikroTik, Fiber Optic Splicing Bench, Server Rack Enterprise, Lab Simulasi Cloud',
 'BNSP RI, MikroTik MTCNA, Cisco CCNA Track',
 'assets/img/jurusan-tkj.jpg', 'menguasai-jaringan-dan-cloud-jurusan-tkj-smk-bangun-nusa-bangsa', 1),

('AKL', 'Akuntansi & Keuangan Lembaga', 'Presisi Finansial, Perpajakan Digital & Audit Modern',
 'Program keahlian yang membekali siswa dengan penguasaan siklus akuntansi perusahaan jasa/dagang/manufaktur, perpajakan berbasis e-Faktur/Coretax, serta aplikasi akuntansi digital.',
 'Junior Accountant, Tax Officer, Financial Analyst, Internal Auditor, Banking Operations Staff',
 'Lab Akuntansi Digital (MYOB & Accurate), Mini Bank Sekolah, Simulasi Perpajakan Digital',
 'BNSP Teknisi Akuntansi Pratama, Brevet Pajak Dasar',
 'assets/img/jurusan-akl.jpg', 'cerdas-finansial-pajak-digital-jurusan-akuntansi-akl-era-modern', 2),

('TKR', 'Teknik Kendaraan Ringan', 'Teknologi Otomotif Presisi hingga Era Kendaraan Listrik',
 'Jurusan favorit bidang teknik otomotif yang mempelajari sistem EFI, transmisi otomatis, sasis, kelistrikan bodi, hingga diagnosa komputer OBD-II dan pengenalan sistem Hybrid/EV.',
 'Automotive Diagnostic Technician, Service Advisor, Quality Control Otomotif, EV Technician, Wirausaha Bengkel',
 'Bengkel Standar ATPM, Car Lift Hidrolik, Scanner OBD-II Digital, Engine Stand EFI & EV Trainer',
 'BNSP Mekanik Otomotif Level II & III, Sertifikat Praktik Industri ATPM',
 'assets/img/jurusan-tkr.svg', 'dari-mesin-konvensional-ke-kendaraan-listrik-bengkel-modern-tkr', 3)
ON DUPLICATE KEY UPDATE
  `nama` = VALUES(`nama`),
  `tagline` = VALUES(`tagline`),
  `deskripsi` = VALUES(`deskripsi`);

INSERT INTO `kelebihan` (`judul`, `ringkasan`, `ikon`, `highlight_badge`, `urutan`) VALUES
('Kurikulum Link & Match Industri', 'Disusun bersama 50+ mitra perusahaan nasional sehingga materi kelas langsung relevan dengan teknologi kerja terbaru.', 'briefcase', '50+ Mitra Industri', 1),
('Sertifikasi Profesi Resmi (BNSP)', 'Setiap lulusan dibekali sertifikat kompetensi nasional BNSP serta sertifikasi vendor seperti MikroTik, Accurate, dan standar ATPM.', 'award', 'Sertifikasi Ganda', 2),
('Laboratorium & Bengkel Modern', 'Fasilitas praktik berstandar industri: server rack, fiber optik, mini bank, software akuntansi, hingga hydraulic car lift & EV trainer.', 'cpu', 'Standar Industri', 3),
('Jalur Khusus PTN Top 10', 'Program intensif bimbingan SNBP, SNBT-UTBK, dan beasiswa prestasi untuk mengantarkan siswa SMK menembus universitas terbaik.', 'graduation-cap', 'Bimbingan UTBK Gratis', 4),
('Bursa Kerja Khusus (BKK) Aktif', 'Pendampingan rekrutmen langsung di kampus oleh HRD perusahaan ternama sebelum siswa resmi diwisuda.', 'building', '95% Terserap Cepat', 5),
('Pembinaan Karakter & Disiplin', 'Menanamkan etos kerja tangguh, kejujuran profesional, kepemimpinan, serta budaya hijau yang peduli lingkungan.', 'shield-check', 'Akreditasi A Unggul', 6);

INSERT INTO `artikel` (`slug`, `judul`, `kategori`, `label_kategori`, `ringkasan`, `konten`, `gambar`, `penulis`, `waktu_baca`, `unggulan`, `views`, `tanggal_publikasi`) VALUES
('menguasai-jaringan-dan-cloud-jurusan-tkj-smk-bangun-nusa-bangsa',
 'Menguasai Dunia Jaringan & Cloud: Mengapa Jurusan TKJ SMK Bangun Nusa Bangsa Jadi Incaran Industri Tech',
 'jurusan', 'Jurusan TKJ',
 'Dari konfigurasi MikroTik, instalasi fiber optik FTTH, hingga manajemen server cloud—simak bagaimana siswa TKJ SMK Bangun Nusa Bangsa ditempa menjadi talenta IT siap kerja.',
 '<p class="lead">Di tengah percepatan transformasi digital Indonesia, kebutuhan tenaga ahli infrastruktur jaringan dan komputasi awan melonjak drastis. Jurusan <strong>Teknik Komputer dan Jaringan (TKJ)</strong> di SMK Bangun Nusa Bangsa hadir bukan sekadar mengajarkan perakitan komputer, melainkan merancang arsitektur jaringan modern yang andal dan aman.</p>
<h3>Kurikulum Berbasis Praktik Lapangan 70%</h3>
<p>Siswa TKJ menghabiskan sebagian besar jam belajar di Laboratorium Jaringan Enterprise. Sejak kelas X, peserta didik dilatih melakukan crimping kabel UTP, perancangan subnetting IPv4/IPv6, hingga troubleshooting perangkat keras.</p>
<p>Memasuki kelas XI dan XII, materi meningkat ke level industri:</p>
<ul>
<li><strong>Routing & Switching Lanjutan:</strong> Konfigurasi VLAN, OSPF, BGP, dan manajemen bandwidth menggunakan perangkat MikroTik dan Cisco.</li>
<li><strong>Teknologi Fiber Optik (FTTH):</strong> Praktik langsung menggunakan fusion splicer dan OTDR untuk mengukur redaman jaringan optik.</li>
<li><strong>Administrasi Server & Cloud:</strong> Pengelolaan sistem operasi Linux Server, virtualisasi Docker/Proxmox, serta dasar keamanan siber (firewall & intrusion prevention).</li>
</ul>
<div class="callout-box"><strong>Fakta Menarik:</strong> Lebih dari 85% siswa TKJ kelas XII sudah mengantongi sertifikat internasional <em>MikroTik Certified Network Associate (MTCNA)</em> sebelum mengikuti Uji Kompetensi Keahlian (UKK).</div>
<h3>Peluang Karir & Studi Lanjut</h3>
<p>Lulusan TKJ SMK Bangun Nusa Bangsa banyak direkrut sebagai <em>Network Operations Center (NOC) Engineer</em>, teknisi FTTH provider internet nasional, hingga melanjutkan studi ke jurusan Teknik Informatika dan Sistem Informasi di kampus ternama.</p>',
 'assets/img/jurusan-tkj.jpg', 'Tim Kurikulum TKJ', '5 Menit Baca', 1, 1420, '2026-10-18'),

('cerdas-finansial-pajak-digital-jurusan-akuntansi-akl-era-modern',
 'Cerdas Finansial & Pajak Digital: Prospek Gemilang Jurusan Akuntansi (AKL) di Era Ekonomi Modern',
 'jurusan', 'Jurusan AKL',
 'Bukan lagi sekadar mencatat buku kas manual—siswa Akuntansi SMK Bangun Nusa Bangsa menguasai software akuntansi korporat, simulasi Mini Bank, dan sistem perpajakan digital.',
 '<p class="lead">Setiap perusahaan, mulai dari startup teknologi hingga korporasi multinasional, berdiri di atas fondasi keuangan yang sehat. Jurusan <strong>Akuntansi dan Keuangan Lembaga (AKL)</strong> SMK Bangun Nusa Bangsa memadukan ketelitian ilmu akuntansi klasik dengan kecakapan teknologi finansial modern.</p>
<h3>Belajar di Mini Bank & Lab Akuntansi Digital</h3>
<p>Keunggulan pembelajaran AKL di sekolah kami adalah hadirnya fasilitas <strong>Mini Bank Bangun Nusa</strong> yang dikelola langsung oleh siswa di bawah supervisi guru praktisi. Siswa berlatih pelayanan nasabah, rekonsiliasi transaksi, hingga manajemen kas harian.</p>
<ul>
<li><strong>Komputer Akuntansi:</strong> Penguasaan penuh aplikasi MYOB Accounting, Accurate Online, dan otomasi laporan keuangan berbasis Excel lanjutan.</li>
<li><strong>Perpajakan Terintegrasi:</strong> Simulasi penghitungan dan pelaporan PPh 21, PPh Badan, serta PPN sesuai regulasi perpajakan digital terbaru.</li>
<li><strong>Audit & Etika Bisnis:</strong> Pelatihan deteksi selisih pembukuan dan integritas pelaporan keuangan lembaga.</li>
</ul>
<div class="callout-box"><strong>Sertifikasi Keahlian:</strong> Lulusan AKL mengikuti uji sertifikasi BNSP Skema Teknisi Akuntansi Pratama yang diakui secara nasional oleh sektor perbankan dan kantor akuntan publik.</div>
<h3>Siap Menjadi Profesional Muda Keuangan</h3>
<p>Kombinasi ketelitian angka dan penguasaan software membuat alumni AKL cepat terserap di sektor perbankan, perusahaan ritel, maupun lolos seleksi SNBP/SNBT ke Fakultas Ekonomi dan Bisnis ternama.</p>',
 'assets/img/jurusan-akl.jpg', 'Tim Kurikulum AKL', '5 Menit Baca', 1, 1185, '2026-10-22'),

('dari-mesin-konvensional-ke-kendaraan-listrik-bengkel-modern-tkr',
 'Dari Mesin Konvensional ke Kendaraan Listrik: Keunggulan Bengkel Modern TKR SMK Bangun Nusa Bangsa',
 'jurusan', 'Jurusan TKR',
 'Menjawab revolusi otomotif masa depan, jurusan Teknik Kendaraan Ringan (TKR) membekali siswa keahlian engine tune-up EFI, sistem kelistrikan modern, hingga pengenalan teknologi EV.',
 '<p class="lead">Industri otomotif bergerak sangat cepat menuju efisiensi bahan bakar dan elektrifikasi. Jurusan <strong>Teknik Kendaraan Ringan (TKR)</strong> di SMK Bangun Nusa Bangsa memastikan siswa tidak tertinggal zaman dengan memperbarui kurikulum serta peralatan bengkel sesuai standar Agen Tunggal Pemegang Merek (ATPM).</p>
<h3>Workshop Berstandar Bengkel Resmi</h3>
<p>Begitu melangkah ke area workshop TKR, siswa langsung dihadapkan pada tata letak bengkel profesional yang bersih, aman (menerapkan budaya K3/5R), dan dilengkapi peralatan diagnostik modern:</p>
<ul>
<li><strong>Engine Management & EFI Diagnostic:</strong> Penggunaan scanner OBD-II digital untuk membaca sensor mesin dan menganalisis kode kerusakan secara akurat.</li>
<li><strong>Sistem Sasis, Rem ABS & Transmisi:</strong> Perawatan sistem suspensi, spooring-balancing, serta overhoul transmisi manual dan otomatis.</li>
<li><strong>Pengenalan Hybrid & Electric Vehicle (EV):</strong> Modul trainer kendaraan listrik untuk memahami manajemen baterai tegangan tinggi dan motor penggerak listrik.</li>
</ul>
<div class="callout-box"><strong>Program Magang ATPM:</strong> Siswa TKR berkesempatan menjalani Praktik Kerja Lapangan (PKL) selama 6 bulan penuh di jaringan bengkel resmi Toyota, Daihatsu, Mitsubishi, dan pabrik manufaktur otomotif.</div>
<h3>Mekanik Andal Sekaligus Teknisi Masa Depan</h3>
<p>Dengan karakter disiplin tinggi dan jam terbang bengkel yang matang, lulusan TKR SMK Bangun Nusa Bangsa menjadi pilihan utama industri manufaktur otomotif serta siap membuka bengkel modern mandiri.</p>',
 'assets/img/jurusan-tkr.svg', 'Tim Kurikulum TKR', '6 Menit Baca', 1, 1340, '2026-10-28'),

('keunggulan-smk-bangun-nusa-bangsa-siap-kerja-kuliah-wirausaha',
 '7 Keunggulan Utama SMK Bangun Nusa Bangsa: Mencetak Lulusan Siap Kerja, Siap Kuliah, dan Siap Wirausaha',
 'keunggulan', 'Keunggulan Sekolah',
 'Apa yang membedakan SMK Bangun Nusa Bangsa dari sekolah kejuruan lainnya? Mulai dari kurikulum Link & Match, guru praktisi, hingga jaminan pembinaan PTN Top 10.',
 '<p class="lead">Memilih sekolah menengah kejuruan adalah investasi besar bagi masa depan karier dan pendidikan remaja. <strong>SMK Bangun Nusa Bangsa</strong> merancang ekosistem pendidikan vokasi terpadu yang menyeimbangkan keterampilan teknis, prestasi akademik, dan karakter kepemimpinan.</p>
<h3>Mengapa Memilih SMK Bangun Nusa Bangsa?</h3>
<ol>
<li><strong>Kurikulum Sinkron Industri (Link & Match):</strong> Setiap awal tahun ajaran, silabus TKJ, AKL, dan TKR direview bersama mitra industri agar tidak ada materi usang.</li>
<li><strong>Sertifikasi Ganda (Ijazah + BNSP):</strong> Siswa lulus dengan membawa portofolio proyek nyata dan sertifikat kompetensi berlogo Garuda.</li>
<li><strong>Bursa Kerja Khusus (BKK) Proaktif:</strong> Sekolah memfasilitasi tes psikotes dan wawancara kerja langsung di aula sekolah bersama HRD puluhan perusahaan besar.</li>
<li><strong>Program Khusus Tembus PTN Top 10:</strong> Siapa bilang anak SMK tidak bisa masuk UI, ITB, atau UGM? Kami menyediakan kelas pengayaan UTBK-SNBT intensif tanpa biaya tambahan.</li>
<li><strong>Lingkungan Sekolah Hijau & Berintegritas:</strong> Kampus asri dengan ruang terbuka hijau, laboratorium ber-AC, dan budaya anti-perundungan.</li>
<li><strong>Inkubator Wirausaha Muda (SPW):</strong> Siswa dilatih membangun unit usaha digital, jasa servis, dan koperasi kreatif sejak bangku sekolah.</li>
<li><strong>Tenaga Pendidik Bersertifikat Asesor:</strong> Mayoritas guru produktif telah mengikuti magang industri dan tersertifikasi sebagai asesor kompetensi.</li>
</ol>
<div class="callout-box"><strong>Komitmen Kami:</strong> Visi BMW (Bekerja, Melanjutkan, Wirausaha) bukan sekadar slogan—melainkan target terukur yang dipantau untuk setiap siswa hingga lulus.</div>',
 'assets/img/bnb.jpg', 'Kepala Sekolah & Humas', '5 Menit Baca', 1, 1890, '2026-11-03'),

('jejak-emas-alumni-smk-bangun-nusa-bangsa-perusahaan-ternama',
 'Jejak Emas Alumni SMK Bangun Nusa Bangsa Menembus Telkom Indonesia, Astra, Bank BCA, hingga Tokopedia',
 'alumni', 'Alumni Perusahaan',
 'Kisah nyata para alumni TKJ, AKL, dan TKR yang berhasil meniti karier cemerlang di perusahaan BUMN, perbankan nasional, hingga raksasa otomotif dan teknologi.',
 '<p class="lead">Bukti terbaik kualitas sebuah sekolah terletak pada kiprah para alumninya. Lulusan <strong>SMK Bangun Nusa Bangsa</strong> kini tersebar di berbagai perusahaan papan atas nasional dan multinasional, membuktikan bahwa lulusan vokasi mampu bersaing di level tertinggi.</p>
<h3>Dari Bangku Praktik ke Korporasi Ternama</h3>
<p>Salah satu kisah inspiratif datang dari <strong>Rizky Pratama (Alumni TKJ 2023)</strong> yang kini menjabat sebagai <em>Junior Network Operations Engineer</em> di <strong>PT Telkom Indonesia</strong>. Berbekal pengalaman mengkonfigurasi BGP dan fiber optik di lab sekolah serta sertifikat MTCNA, Rizky langsung lolos rekrutmen jalur vokasi unggulan.</p>
<p>Dari jurusan Akuntansi, <strong>Siti Nurhaliza (Alumni AKL 2022)</strong> dipercaya bergabung di divisi Operasional Kredit <strong>Bank Central Asia (BCA)</strong> setelah sebelumnya menjuarai Lomba Kompetensi Siswa (LKS) bidang Akuntansi tingkat provinsi. Ketelitian dalam rekonsiliasi data keuangan yang diasah di Mini Bank sekolah menjadi modal utamanya.</p>
<p>Sementara itu, di sektor manufaktur otomotif, <strong>Dimas Saputra (Alumni TKR 2023)</strong> dan belasan rekan seangkatannya resmi berkarya di lini inspeksi mesin dan quality control <strong>PT Astra Daihatsu Motor</strong> serta <strong>PT Toyota Motor Manufacturing Indonesia</strong>.</p>
<div class="callout-box"><strong>Testimoni Industri:</strong> <em>"Lulusan SMK Bangun Nusa Bangsa memiliki adaptasi kerja yang cepat, disiplin K3 yang kuat, dan pemahaman teknis di atas rata-rata."</em> — Perwakilan Mitra Rekrutmen Industri.</div>
<h3>Jejaring Alumni yang Saling Menguatkan</h3>
<p>Melalui Ikatan Alumni Bangun Nusa (IKABANU), para senior yang telah bekerja di <strong>Pertamina, Bank Mandiri, hingga GoTo/Tokopedia</strong> rutin pulang ke kampus untuk memberikan sesi mentoring CV, simulasi wawancara HRD, dan berbagi info lowongan eksklusif bagi adik tingkatnya.</p>',
 'assets/img/alumni-perusahaan.jpg', 'Tim Bursa Kerja Khusus', '6 Menit Baca', 1, 2150, '2026-11-10'),

('lulusan-smk-tembus-top-10-universitas-indonesia-ui-itb-ugm-its',
 'Lulusan SMK Bisa Tembus UI, ITB, UGM, hingga ITS! Ini Rahasia Program Bimbingan Universitas Top 10 SMK Bangun Nusa Bangsa',
 'universitas', 'Universitas Top 10',
 'Mematahkan stigma bahwa anak SMK sulit masuk PTN bergengsi—simak strategi bimbingan intensif yang mengantarkan puluhan alumni meraih kursi di Top 10 Universitas Indonesia.',
 '<p class="lead">Masih ada anggapan keliru bahwa masuk SMK berarti menutup pintu menuju perguruan tinggi negeri favorit. <strong>SMK Bangun Nusa Bangsa</strong> mematahkan mitos tersebut dengan meluluskan siswa-siswinya ke jajaran <strong>Top 10 Universitas Terbaik di Indonesia</strong> melalui jalur SNBP, SNBT-UTBK, hingga Seleksi Mandiri Berprestasi.</p>
<h3>Daftar Kampus Top 10 yang Ditembus Alumni Kami</h3>
<p>Dalam tiga tahun terakhir, alumni dari jurusan TKJ, AKL, maupun TKR sukses mengenakan jas almamater kampus impian, antara lain:</p>
<ul>
<li><strong>Universitas Indonesia (UI)</strong> — Prodi Ilmu Komputer & Akuntansi</li>
<li><strong>Institut Teknologi Bandung (ITB)</strong> — Sekolah Teknik Elektro dan Informatika (STEI)</li>
<li><strong>Universitas Gadjah Mada (UGM)</strong> — Teknologi Rekayasa Internet & Teknik Mesin</li>
<li><strong>Institut Teknologi Sepuluh Nopember (ITS)</strong> — Teknik Komputer & Teknik Otomotif</li>
<li><strong>IPB University, Universitas Airlangga (Unair), Universitas Diponegoro (Undip), Universitas Padjadjaran (Unpad), Universitas Brawijaya (UB),</strong> dan <strong>Telkom University</strong>.</li>
</ul>
<h3>Strategi 3 Pilar Pendampingan Kampus</h3>
<p>Keberhasilan ini bukan kebetulan, melainkan hasil program terstruktur sejak kelas XI:</p>
<ol>
<li><strong>Klinik Penalaran & UTBK Gratis:</strong> Bimbingan intensif Penalaran Matematika, Literasi Bahasa Inggris, dan Pengetahuan Kuantitatif setiap pekan.</li>
<li><strong>Kurasi Portofolio SNBP & Lomba LKS:</strong> Guru Bimbingan Konseling memetakan nilai rapor dan prestasi LKS agar siswa memiliki daya saing tinggi pada jalur undangan.</li>
<li><strong>Klinik Beasiswa KIP-Kuliah & Swasta Unggulan:</strong> Pendampingan penuh pengajuan beasiswa sehingga kendala biaya tidak menghalangi siswa berprestasi meraih gelar sarjana.</li>
</ol>
<div class="callout-box"><strong>Inspirasi Alumni:</strong> <em>"Skill praktik TKJ membuat saya jauh lebih cepat memahami mata kuliah Jaringan Komputer dan Sistem Operasi di ITB. Masuk SMK justru jadi keunggulan ganda!"</em> — Nadia Putri, Alumni TKJ, Mahasiswa STEI ITB.</div>',
 'assets/img/alumni-universitas.svg', 'Tim BK & Akademik', '6 Menit Baca', 1, 2480, '2026-11-18')
ON DUPLICATE KEY UPDATE
  `judul` = VALUES(`judul`),
  `konten` = VALUES(`konten`),
  `gambar` = VALUES(`gambar`);

INSERT INTO `alumni_prestasi` (`nama`, `angkatan`, `jurusan_kode`, `tipe`, `instansi`, `posisi_atau_prodi`, `peringkat_info`, `kutipan`, `urutan`) VALUES
('Rizky Pratama', 2023, 'TKJ', 'perusahaan', 'PT Telkom Indonesia (Persero) Tbk', 'Network Operations Engineer', 'BUMN Telekomunikasi #1', 'Lab MikroTik dan fiber optik di SMK Bangun Nusa Bangsa membuat saya siap terjun langsung sejak hari pertama kerja.', 1),
('Siti Nurhaliza', 2022, 'AKL', 'perusahaan', 'PT Bank Central Asia (BCA) Tbk', 'Credit Operations & Financial Staff', 'Perbankan Nasional Terkemuka', 'Praktik di Mini Bank sekolah melatih ketelitian dan standar layanan profesional yang sangat dihargai saat wawancara HRD.', 2),
('Dimas Saputra', 2023, 'TKR', 'perusahaan', 'PT Astra Daihatsu Motor', 'Engine Quality & Diagnostic Specialist', 'Manufaktur Otomotif Nasional', 'Budaya disiplin bengkel 5R dan penguasaan scanner OBD-II menjadikan alumni TKR sangat kompetitif di lini produksi Astra.', 3),
('Fajar Nugroho', 2021, 'TKJ', 'perusahaan', 'GoTo / Tokopedia', 'Junior Cloud Infrastructure Support', 'Tech Ecosystem Indonesia', 'Kebiasaan eksplorasi Linux Server di sekolah menjadi batu loncatan karier saya di industri teknologi digital.', 4),
('Ayu Lestari', 2022, 'AKL', 'perusahaan', 'PT Bank Mandiri (Persero) Tbk', 'Accounting & Tax Administration', 'BUMN Perbankan Terbesar', 'Sertifikasi BNSP Akuntansi dan materi pajak digital dari sekolah sangat membantu tugas pelaporan keuangan perusahaan.', 5),
('Bagus Setiawan', 2023, 'TKR', 'perusahaan', 'PT Toyota Motor Manufacturing Indonesia', 'Production & Maintenance Technician', 'Otomotif Global', 'Magang industri selama 6 bulan lewat program BKK sekolah langsung mengantarkan saya menjadi karyawan tetap sebelum wisuda.', 6),

('Nadia Putri Maharani', 2024, 'TKJ', 'universitas', 'Institut Teknologi Bandung (ITB)', 'S1 Teknik Informatika (STEI)', 'Top 10 Universitas #1-2 RI', 'Bekal pemrograman dan arsitektur jaringan dari TKJ membuat saya percaya diri bersaing di ITB melalui jalur UTBK-SNBT.', 1),
('Muhammad Rafi', 2024, 'AKL', 'universitas', 'Universitas Indonesia (UI)', 'S1 Akuntansi - Fakultas Ekonomi & Bisnis', 'Top 10 Universitas #1 RI', 'Bimbingan intensif SNBT gratis dari guru SMK Bangun Nusa Bangsa membuktikan anak SMK bisa menembus kampus jaket kuning.', 2),
('Putri Wulandari', 2023, 'AKL', 'universitas', 'Universitas Gadjah Mada (UGM)', 'S1 Akuntansi Sektor Publik', 'Top 10 Universitas #3 RI', 'Prestasi juara LKS dan pendampingan portofolio SNBP oleh guru BK sukses membuka jalan saya ke UGM Yogyakarta.', 3),
('Andika Wijaya', 2024, 'TKR', 'universitas', 'Institut Teknologi Sepuluh Nopember (ITS)', 'S1 Teknik Mesin & Otomotif', 'Top 10 Universitas Teknologi', 'Memahami mesin secara nyata di bengkel TKR mempermudah saya mendalami termodinamika dan desain mesin di ITS.', 4),
('Kevin Alamsyah', 2023, 'TKJ', 'universitas', 'Universitas Diponegoro (Undip) & IPB', 'S1 Teknik Komputer (Undip)', 'Top 10 PTN Nasional', 'Klinik penalaran matematika sekolah membantu saya meraih skor UTBK tinggi dan lolos pilihan pertama.', 5),
('Zahra Aulia', 2024, 'AKL', 'universitas', 'Universitas Airlangga (Unair) / Unpad / UB', 'S1 Ekonomi Pembangunan (Unair)', 'Top 10 PTN Nasional', 'Guru-guru selalu memotivasi kami punya target tinggi: siap kerja di industri sekaligus siap kuliah di PTN terbaik.', 6);

INSERT IGNORE INTO `portal_users` (`username`, `nama_lengkap`, `role`, `jurusan_kode`, `kelas_atau_jabatan`, `password_hash`) VALUES
('20261001', 'Rafi Akbar Pratama', 'siswa', 'TKJ', 'XII TKJ 1', '$2y$10$lJN4ts5Kk5f98hd6n6iGLunxix2NtvMlEjka2ZkJ4ibCBi0zQioru'),
('20261002', 'Nayla Azzahra', 'siswa', 'AKL', 'XII AKL 2', '$2y$10$lJN4ts5Kk5f98hd6n6iGLunxix2NtvMlEjka2ZkJ4ibCBi0zQioru'),
('guru.bnb', 'Budi Santoso, S.Kom., Gr.', 'guru', 'TKJ', 'Wali Kelas & Kaprodi TKJ', '$2y$10$mF3g2OfyBRkn8v2ZYR..xuuAcpEk7XX3x/5YGMLhm2jAXCbotU7mO'),
('admin.bnb', 'Administrator Sistem Akademik', 'admin', NULL, 'Admin Portal & Panitia PPDB', '$2y$10$T266jHI37Ktk0HzNs6BZdeVSGPTVfTy5iRW63frwtkxoxbIH8M0GS');
