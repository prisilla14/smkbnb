<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Mengambil seluruh data jurusan dari MySQL (atau cadangan jika MySQL belum aktif).
 */
function getJurusanList(?PDO $pdo): array
{
    if ($pdo instanceof PDO) {
        $stmt = $pdo->query('SELECT * FROM jurusan ORDER BY urutan ASC, id ASC');
        return $stmt->fetchAll();
    }

    return [
        [
            'kode' => 'TKJ',
            'nama' => 'Teknik Komputer & Jaringan',
            'tagline' => 'Membangun Infrastruktur Digital, Cloud & Keamanan Siber',
            'deskripsi' => 'Kompetensi keahlian yang mencetak teknisi jaringan, administrator sistem Linux/Windows, hingga spesialis fiber optik dan keamanan siber berbasis praktik langsung industri telekomunikasi.',
            'prospek_karir' => 'Network Engineer, Cloud Administrator, Fiber Optic Technician, Cyber Security Analyst, IT Support Specialist',
            'fasilitas' => 'Lab Cisco & MikroTik, Fiber Optic Splicing Bench, Server Rack Enterprise, Lab Simulasi Cloud',
            'sertifikasi' => 'BNSP RI, MikroTik MTCNA, Cisco CCNA Track',
            'gambar' => 'assets/img/jurusan-tkj.jpg',
            'artikel_slug' => 'menguasai-jaringan-dan-cloud-jurusan-tkj-smk-bangun-nusa-bangsa',
        ],
        [
            'kode' => 'AKL',
            'nama' => 'Akuntansi & Keuangan Lembaga',
            'tagline' => 'Presisi Finansial, Perpajakan Digital & Audit Modern',
            'deskripsi' => 'Program keahlian yang membekali siswa dengan penguasaan siklus akuntansi perusahaan jasa/dagang/manufaktur, perpajakan berbasis e-Faktur/Coretax, serta aplikasi akuntansi digital.',
            'prospek_karir' => 'Junior Accountant, Tax Officer, Financial Analyst, Internal Auditor, Banking Operations Staff',
            'fasilitas' => 'Lab Akuntansi Digital (MYOB & Accurate), Mini Bank Sekolah, Simulasi Perpajakan Digital',
            'sertifikasi' => 'BNSP Teknisi Akuntansi Pratama, Brevet Pajak Dasar',
            'gambar' => 'assets/img/jurusan-akl.jpg',
            'artikel_slug' => 'cerdas-finansial-pajak-digital-jurusan-akuntansi-akl-era-modern',
        ],
        [
            'kode' => 'TKR',
            'nama' => 'Teknik Kendaraan Ringan',
            'tagline' => 'Teknologi Otomotif Presisi hingga Era Kendaraan Listrik',
            'deskripsi' => 'Jurusan favorit bidang teknik otomotif yang mempelajari sistem EFI, transmisi otomatis, sasis, kelistrikan bodi, hingga diagnosa komputer OBD-II dan pengenalan sistem Hybrid/EV.',
            'prospek_karir' => 'Automotive Diagnostic Technician, Service Advisor, Quality Control Otomotif, EV Technician, Wirausaha Bengkel',
            'fasilitas' => 'Bengkel Standar ATPM, Car Lift Hidrolik, Scanner OBD-II Digital, Engine Stand EFI & EV Trainer',
            'sertifikasi' => 'BNSP Mekanik Otomotif Level II & III, Sertifikat Praktik Industri ATPM',
            'gambar' => 'assets/img/jurusan-tkr.svg',
            'artikel_slug' => 'dari-mesin-konvensional-ke-kendaraan-listrik-bengkel-modern-tkr',
        ],
    ];
}

/**
 * Mengambil kelebihan sekolah dari MySQL.
 */
function getKelebihanList(?PDO $pdo): array
{
    if ($pdo instanceof PDO) {
        $stmt = $pdo->query('SELECT * FROM kelebihan ORDER BY urutan ASC, id ASC');
        return $stmt->fetchAll();
    }

    return [
        ['judul' => 'Kurikulum Link & Match Industri', 'ringkasan' => 'Disusun bersama 50+ mitra perusahaan nasional sehingga materi kelas langsung relevan dengan teknologi kerja terbaru.', 'ikon' => 'briefcase', 'highlight_badge' => '50+ Mitra Industri'],
        ['judul' => 'Sertifikasi Profesi Resmi (BNSP)', 'ringkasan' => 'Setiap lulusan dibekali sertifikat kompetensi nasional BNSP serta sertifikasi vendor seperti MikroTik, Accurate, dan standar ATPM.', 'ikon' => 'award', 'highlight_badge' => 'Sertifikasi Ganda'],
        ['judul' => 'Laboratorium & Bengkel Modern', 'ringkasan' => 'Fasilitas praktik berstandar industri: server rack, fiber optik, mini bank, software akuntansi, hingga hydraulic car lift & EV trainer.', 'ikon' => 'cpu', 'highlight_badge' => 'Standar Industri'],
        ['judul' => 'Jalur Khusus PTN Top 10', 'ringkasan' => 'Program intensif bimbingan SNBP, SNBT-UTBK, dan beasiswa prestasi untuk mengantarkan siswa SMK menembus universitas terbaik.', 'ikon' => 'graduation-cap', 'highlight_badge' => 'Bimbingan UTBK Gratis'],
        ['judul' => 'Bursa Kerja Khusus (BKK) Aktif', 'ringkasan' => 'Pendampingan rekrutmen langsung di kampus oleh HRD perusahaan ternama sebelum siswa resmi diwisuda.', 'ikon' => 'building', 'highlight_badge' => '95% Terserap Cepat'],
        ['judul' => 'Pembinaan Karakter & Disiplin', 'ringkasan' => 'Menanamkan etos kerja tangguh, kejujuran profesional, kepemimpinan, serta budaya hijau yang peduli lingkungan.', 'ikon' => 'shield-check', 'highlight_badge' => 'Akreditasi A Unggul'],
    ];
}

/**
 * Data artikel cadangan apabila MySQL belum dijalankan.
 */
function getFallbackArtikelData(): array
{
    return [
        [
            'id' => 1,
            'slug' => 'menguasai-jaringan-dan-cloud-jurusan-tkj-smk-bangun-nusa-bangsa',
            'judul' => 'Menguasai Dunia Jaringan & Cloud: Mengapa Jurusan TKJ SMK Bangun Nusa Bangsa Jadi Incaran Industri Tech',
            'kategori' => 'jurusan',
            'label_kategori' => 'Jurusan TKJ',
            'ringkasan' => 'Dari konfigurasi MikroTik, instalasi fiber optik FTTH, hingga manajemen server cloud—simak bagaimana siswa TKJ SMK Bangun Nusa Bangsa ditempa menjadi talenta IT siap kerja.',
            'konten' => '<p class="lead">Jurusan <strong>Teknik Komputer dan Jaringan (TKJ)</strong> di SMK Bangun Nusa Bangsa berfokus pada arsitektur jaringan modern, routing &amp; switching MikroTik/Cisco, teknologi fiber optik FTTH, serta administrasi server Linux dan cloud.</p><h3>Kurikulum Berbasis Praktik Lapangan 70%</h3><p>Siswa berlatih langsung di Laboratorium Jaringan Enterprise mulai dari subnetting, VLAN, OSPF, fusion splicing, hingga keamanan siber.</p><div class="callout-box"><strong>Fakta Menarik:</strong> Lebih dari 85% siswa TKJ kelas XII sudah mengantongi sertifikat internasional <em>MikroTik Certified Network Associate (MTCNA)</em>.</div>',
            'gambar' => 'assets/img/jurusan-tkj.jpg',
            'penulis' => 'Tim Kurikulum TKJ',
            'waktu_baca' => '5 Menit Baca',
            'unggulan' => 1,
            'views' => 1420,
            'tanggal_publikasi' => '2026-10-18',
        ],
        [
            'id' => 2,
            'slug' => 'cerdas-finansial-pajak-digital-jurusan-akuntansi-akl-era-modern',
            'judul' => 'Cerdas Finansial & Pajak Digital: Prospek Gemilang Jurusan Akuntansi (AKL) di Era Ekonomi Modern',
            'kategori' => 'jurusan',
            'label_kategori' => 'Jurusan AKL',
            'ringkasan' => 'Bukan lagi sekadar mencatat buku kas manual—siswa Akuntansi SMK Bangun Nusa Bangsa menguasai software akuntansi korporat, simulasi Mini Bank, dan sistem perpajakan digital.',
            'konten' => '<p class="lead">Jurusan <strong>Akuntansi dan Keuangan Lembaga (AKL)</strong> memadukan ketelitian siklus akuntansi dengan penguasaan software MYOB, Accurate Online, dan sistem perpajakan digital.</p><h3>Belajar di Mini Bank Bangun Nusa</h3><p>Siswa mengelola simulasi perbankan langsung mulai dari layanan nasabah hingga rekonsiliasi transaksi harian dan audit internal.</p><div class="callout-box"><strong>Sertifikasi Keahlian:</strong> Lulusan AKL mengikuti uji sertifikasi BNSP Skema Teknisi Akuntansi Pratama yang diakui sektor perbankan.</div>',
            'gambar' => 'assets/img/jurusan-akl.jpg',
            'penulis' => 'Tim Kurikulum AKL',
            'waktu_baca' => '5 Menit Baca',
            'unggulan' => 1,
            'views' => 1185,
            'tanggal_publikasi' => '2026-10-22',
        ],
        [
            'id' => 3,
            'slug' => 'dari-mesin-konvensional-ke-kendaraan-listrik-bengkel-modern-tkr',
            'judul' => 'Dari Mesin Konvensional ke Kendaraan Listrik: Keunggulan Bengkel Modern TKR SMK Bangun Nusa Bangsa',
            'kategori' => 'jurusan',
            'label_kategori' => 'Jurusan TKR',
            'ringkasan' => 'Menjawab revolusi otomotif masa depan, jurusan Teknik Kendaraan Ringan (TKR) membekali siswa keahlian engine tune-up EFI, sistem kelistrikan modern, hingga pengenalan teknologi EV.',
            'konten' => '<p class="lead">Jurusan <strong>Teknik Kendaraan Ringan (TKR)</strong> dilengkapi bengkel standar ATPM dengan car lift hidrolik, scanner OBD-II digital, serta modul pengenalan Hybrid &amp; Electric Vehicle (EV).</p><div class="callout-box"><strong>Program Magang ATPM:</strong> Siswa TKR berkesempatan menjalani PKL 6 bulan penuh di jaringan bengkel resmi dan manufaktur otomotif nasional.</div>',
            'gambar' => 'assets/img/jurusan-tkr.svg',
            'penulis' => 'Tim Kurikulum TKR',
            'waktu_baca' => '6 Menit Baca',
            'unggulan' => 1,
            'views' => 1340,
            'tanggal_publikasi' => '2026-10-28',
        ],
        [
            'id' => 4,
            'slug' => 'keunggulan-smk-bangun-nusa-bangsa-siap-kerja-kuliah-wirausaha',
            'judul' => '7 Keunggulan Utama SMK Bangun Nusa Bangsa: Mencetak Lulusan Siap Kerja, Siap Kuliah, dan Siap Wirausaha',
            'kategori' => 'keunggulan',
            'label_kategori' => 'Keunggulan Sekolah',
            'ringkasan' => 'Apa yang membedakan SMK Bangun Nusa Bangsa dari sekolah kejuruan lainnya? Mulai dari kurikulum Link & Match, guru praktisi, hingga jaminan pembinaan PTN Top 10.',
            'konten' => '<p class="lead"><strong>SMK Bangun Nusa Bangsa</strong> merancang ekosistem pendidikan vokasi terpadu melalui visi BMW (Bekerja, Melanjutkan, Wirausaha).</p><ol><li>Kurikulum Sinkron 50+ Mitra Industri</li><li>Sertifikasi Ganda Ijazah &amp; BNSP</li><li>Bursa Kerja Khusus (BKK) Aktif</li><li>Program Intensif Tembus PTN Top 10</li><li>Lingkungan Hijau &amp; Berintegritas</li><li>Inkubator Wirausaha Muda</li><li>Guru Bersertifikat Asesor Industri</li></ol>',
            'gambar' => 'assets/img/bnb.jpg',
            'penulis' => 'Kepala Sekolah & Humas',
            'waktu_baca' => '5 Menit Baca',
            'unggulan' => 1,
            'views' => 1890,
            'tanggal_publikasi' => '2026-11-03',
        ],
        [
            'id' => 5,
            'slug' => 'jejak-emas-alumni-smk-bangun-nusa-bangsa-perusahaan-ternama',
            'judul' => 'Jejak Emas Alumni SMK Bangun Nusa Bangsa Menembus Telkom Indonesia, Astra, Bank BCA, hingga Tokopedia',
            'kategori' => 'alumni',
            'label_kategori' => 'Alumni Perusahaan',
            'ringkasan' => 'Kisah nyata para alumni TKJ, AKL, dan TKR yang berhasil meniti karier cemerlang di perusahaan BUMN, perbankan nasional, hingga raksasa otomotif dan teknologi.',
            'konten' => '<p class="lead">Lulusan <strong>SMK Bangun Nusa Bangsa</strong> tersebar di berbagai perusahaan ternama seperti PT Telkom Indonesia, Bank BCA, Bank Mandiri, PT Astra Daihatsu Motor, PT Toyota Motor Manufacturing Indonesia, hingga GoTo/Tokopedia.</p><div class="callout-box"><strong>Jejaring Alumni (IKABANU):</strong> Alumni senior rutin membimbing adik kelas dalam penyusunan portofolio, CV, dan simulasi wawancara HRD.</div>',
            'gambar' => 'assets/img/alumni-perusahaan.jpg',
            'penulis' => 'Tim Bursa Kerja Khusus',
            'waktu_baca' => '6 Menit Baca',
            'unggulan' => 1,
            'views' => 2150,
            'tanggal_publikasi' => '2026-11-10',
        ],
        [
            'id' => 6,
            'slug' => 'lulusan-smk-tembus-top-10-universitas-indonesia-ui-itb-ugm-its',
            'judul' => 'Lulusan SMK Bisa Tembus UI, ITB, UGM, hingga ITS! Ini Rahasia Program Bimbingan Universitas Top 10 SMK Bangun Nusa Bangsa',
            'kategori' => 'universitas',
            'label_kategori' => 'Universitas Top 10',
            'ringkasan' => 'Mematahkan stigma bahwa anak SMK sulit masuk PTN bergengsi—simak strategi bimbingan intensif yang mengantarkan puluhan alumni meraih kursi di Top 10 Universitas Indonesia.',
            'konten' => '<p class="lead"><strong>SMK Bangun Nusa Bangsa</strong> membuktikan lulusan vokasi mampu lolos seleksi SNBP dan SNBT-UTBK ke jajaran Top 10 Universitas di Indonesia: UI, ITB, UGM, ITS, IPB, Unair, Undip, Unpad, UB, dan Telkom University.</p><h3>Strategi 3 Pilar Pendampingan Kampus</h3><ol><li>Klinik Penalaran &amp; UTBK Gratis</li><li>Kurasi Portofolio SNBP &amp; Lomba LKS</li><li>Klinik Beasiswa KIP-Kuliah &amp; Prestasi</li></ol>',
            'gambar' => 'assets/img/alumni-universitas.svg',
            'penulis' => 'Tim BK & Akademik',
            'waktu_baca' => '6 Menit Baca',
            'unggulan' => 1,
            'views' => 2480,
            'tanggal_publikasi' => '2026-11-18',
        ],
    ];
}

/**
 * Mengambil daftar artikel dengan filter kategori & pencarian opsional.
 */
function getArtikelList(?PDO $pdo, string $kategori = 'semua', string $search = ''): array
{
    if ($pdo instanceof PDO) {
        $sql = 'SELECT * FROM artikel WHERE 1=1';
        $params = [];

        if ($kategori !== 'semua' && $kategori !== '') {
            $sql .= ' AND kategori = :kategori';
            $params[':kategori'] = $kategori;
        }

        if ($search !== '') {
            $sql .= ' AND (judul LIKE :q1 OR ringkasan LIKE :q2 OR label_kategori LIKE :q3)';
            $term = '%' . $search . '%';
            $params[':q1'] = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
        }

        $sql .= ' ORDER BY unggulan DESC, tanggal_publikasi DESC, id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    $rows = getFallbackArtikelData();
    return array_values(array_filter($rows, static function (array $row) use ($kategori, $search): bool {
        if ($kategori !== 'semua' && $kategori !== '' && $row['kategori'] !== $kategori) {
            return false;
        }
        if ($search !== '') {
            $haystack = mb_strtolower($row['judul'] . ' ' . $row['ringkasan'] . ' ' . $row['label_kategori']);
            if (!str_contains($haystack, mb_strtolower($search))) {
                return false;
            }
        }
        return true;
    }));
}

/**
 * Mengambil satu artikel berdasarkan slug dan menambah jumlah views.
 */
function getArtikelBySlug(?PDO $pdo, string $slug): ?array
{
    if ($slug === '') {
        return null;
    }

    if ($pdo instanceof PDO) {
        $stmt = $pdo->prepare('SELECT * FROM artikel WHERE slug = :slug LIMIT 1');
        $stmt->execute([':slug' => $slug]);
        $artikel = $stmt->fetch();

        if ($artikel) {
            $upd = $pdo->prepare('UPDATE artikel SET views = views + 1 WHERE id = :id');
            $upd->execute([':id' => $artikel['id']]);
            $artikel['views'] = (int) $artikel['views'] + 1;
            return $artikel;
        }

        return null;
    }

    foreach (getFallbackArtikelData() as $row) {
        if ($row['slug'] === $slug) {
            return $row;
        }
    }

    return null;
}

/**
 * Mengambil artikel terkait (selain slug saat ini).
 */
function getRelatedArtikel(?PDO $pdo, string $excludeSlug, int $limit = 3): array
{
    if ($pdo instanceof PDO) {
        $stmt = $pdo->prepare('SELECT id, slug, judul, label_kategori, gambar, waktu_baca, tanggal_publikasi FROM artikel WHERE slug != :slug ORDER BY unggulan DESC, tanggal_publikasi DESC LIMIT :lim');
        $stmt->bindValue(':slug', $excludeSlug, PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $filtered = array_values(array_filter(getFallbackArtikelData(), static fn(array $r): bool => $r['slug'] !== $excludeSlug));
    return array_slice($filtered, 0, $limit);
}

/**
 * Mengambil data alumni sukses (perusahaan & universitas top 10).
 */
function getAlumniList(?PDO $pdo): array
{
    if ($pdo instanceof PDO) {
        $stmt = $pdo->query('SELECT * FROM alumni_prestasi ORDER BY tipe ASC, urutan ASC, id ASC');
        return $stmt->fetchAll();
    }

    return [
        ['nama' => 'Rizky Pratama', 'angkatan' => 2023, 'jurusan_kode' => 'TKJ', 'tipe' => 'perusahaan', 'instansi' => 'PT Telkom Indonesia (Persero) Tbk', 'posisi_atau_prodi' => 'Network Operations Engineer', 'peringkat_info' => 'BUMN Telekomunikasi #1', 'kutipan' => 'Lab MikroTik dan fiber optik di SMK Bangun Nusa Bangsa membuat saya siap terjun langsung sejak hari pertama kerja.'],
        ['nama' => 'Siti Nurhaliza', 'angkatan' => 2022, 'jurusan_kode' => 'AKL', 'tipe' => 'perusahaan', 'instansi' => 'PT Bank Central Asia (BCA) Tbk', 'posisi_atau_prodi' => 'Credit Operations & Financial Staff', 'peringkat_info' => 'Perbankan Nasional Terkemuka', 'kutipan' => 'Praktik di Mini Bank sekolah melatih ketelitian dan standar layanan profesional yang sangat dihargai saat wawancara HRD.'],
        ['nama' => 'Dimas Saputra', 'angkatan' => 2023, 'jurusan_kode' => 'TKR', 'tipe' => 'perusahaan', 'instansi' => 'PT Astra Daihatsu Motor', 'posisi_atau_prodi' => 'Engine Quality & Diagnostic Specialist', 'peringkat_info' => 'Manufaktur Otomotif Nasional', 'kutipan' => 'Budaya disiplin bengkel 5R dan penguasaan scanner OBD-II menjadikan alumni TKR sangat kompetitif di lini produksi Astra.'],
        ['nama' => 'Fajar Nugroho', 'angkatan' => 2021, 'jurusan_kode' => 'TKJ', 'tipe' => 'perusahaan', 'instansi' => 'GoTo / Tokopedia', 'posisi_atau_prodi' => 'Junior Cloud Infrastructure Support', 'peringkat_info' => 'Tech Ecosystem Indonesia', 'kutipan' => 'Kebiasaan eksplorasi Linux Server di sekolah menjadi batu loncatan karier saya di industri teknologi digital.'],
        ['nama' => 'Ayu Lestari', 'angkatan' => 2022, 'jurusan_kode' => 'AKL', 'tipe' => 'perusahaan', 'instansi' => 'PT Bank Mandiri (Persero) Tbk', 'posisi_atau_prodi' => 'Accounting & Tax Administration', 'peringkat_info' => 'BUMN Perbankan Terbesar', 'kutipan' => 'Sertifikasi BNSP Akuntansi dan materi pajak digital dari sekolah sangat membantu tugas pelaporan keuangan perusahaan.'],
        ['nama' => 'Bagus Setiawan', 'angkatan' => 2023, 'jurusan_kode' => 'TKR', 'tipe' => 'perusahaan', 'instansi' => 'PT Toyota Motor Manufacturing Indonesia', 'posisi_atau_prodi' => 'Production & Maintenance Technician', 'peringkat_info' => 'Otomotif Global', 'kutipan' => 'Magang industri selama 6 bulan lewat program BKK sekolah langsung mengantarkan saya menjadi karyawan tetap sebelum wisuda.'],

        ['nama' => 'Nadia Putri Maharani', 'angkatan' => 2024, 'jurusan_kode' => 'TKJ', 'tipe' => 'universitas', 'instansi' => 'Institut Teknologi Bandung (ITB)', 'posisi_atau_prodi' => 'S1 Teknik Informatika (STEI)', 'peringkat_info' => 'Top 10 Universitas #1-2 RI', 'kutipan' => 'Bekal pemrograman dan arsitektur jaringan dari TKJ membuat saya percaya diri bersaing di ITB melalui jalur UTBK-SNBT.'],
        ['nama' => 'Muhammad Rafi', 'angkatan' => 2024, 'jurusan_kode' => 'AKL', 'tipe' => 'universitas', 'instansi' => 'Universitas Indonesia (UI)', 'posisi_atau_prodi' => 'S1 Akuntansi - Fakultas Ekonomi & Bisnis', 'peringkat_info' => 'Top 10 Universitas #1 RI', 'kutipan' => 'Bimbingan intensif SNBT gratis dari guru SMK Bangun Nusa Bangsa membuktikan anak SMK bisa menembus kampus jaket kuning.'],
        ['nama' => 'Putri Wulandari', 'angkatan' => 2023, 'jurusan_kode' => 'AKL', 'tipe' => 'universitas', 'instansi' => 'Universitas Gadjah Mada (UGM)', 'posisi_atau_prodi' => 'S1 Akuntansi Sektor Publik', 'peringkat_info' => 'Top 10 Universitas #3 RI', 'kutipan' => 'Prestasi juara LKS dan pendampingan portofolio SNBP oleh guru BK sukses membuka jalan saya ke UGM Yogyakarta.'],
        ['nama' => 'Andika Wijaya', 'angkatan' => 2024, 'jurusan_kode' => 'TKR', 'tipe' => 'universitas', 'instansi' => 'Institut Teknologi Sepuluh Nopember (ITS)', 'posisi_atau_prodi' => 'S1 Teknik Mesin & Otomotif', 'peringkat_info' => 'Top 10 Universitas Teknologi', 'kutipan' => 'Memahami mesin secara nyata di bengkel TKR mempermudah saya mendalami termodinamika dan desain mesin di ITS.'],
        ['nama' => 'Kevin Alamsyah', 'angkatan' => 2023, 'jurusan_kode' => 'TKJ', 'tipe' => 'universitas', 'instansi' => 'Universitas Diponegoro (Undip) & IPB', 'posisi_atau_prodi' => 'S1 Teknik Komputer (Undip)', 'peringkat_info' => 'Top 10 PTN Nasional', 'kutipan' => 'Klinik penalaran matematika sekolah membantu saya meraih skor UTBK tinggi dan lolos pilihan pertama.'],
        ['nama' => 'Zahra Aulia', 'angkatan' => 2024, 'jurusan_kode' => 'AKL', 'tipe' => 'universitas', 'instansi' => 'Universitas Airlangga (Unair) / Unpad / UB', 'posisi_atau_prodi' => 'S1 Ekonomi Pembangunan (Unair)', 'peringkat_info' => 'Top 10 PTN Nasional', 'kutipan' => 'Guru-guru selalu memetakan potensi kami: siap kerja di industri sekaligus siap kuliah di PTN terbaik.'],
    ];
}

/**
 * Format tanggal ke Bahasa Indonesia.
 */
function formatTanggalIndo(string $dateStr): string
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $ts = strtotime($dateStr);
    if ($ts === false) {
        return $dateStr;
    }
    $m = (int) date('n', $ts);
    return date('d', $ts) . ' ' . ($bulan[$m] ?? date('M', $ts)) . ' ' . date('Y', $ts);
}
