<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Mengembalikan daftar gambar bawaan website yang dapat dipilih langsung di CMS portal.
 */
function getAvailableAssetImages(): array
{
    return [
        'assets/img/bnb.jpg' => 'Gedung Sekolah SMK Bangun Nusa Bangsa (bnb.jpg)',
        'assets/img/hero-campus.jpg' => 'Ilustrasi Kampus Modern (hero-campus.jpg)',
        'assets/img/jurusan-tkj.jpg' => 'Laboratorium TKJ (jurusan-tkj.jpg)',
        'assets/img/jurusan-akl.jpg' => 'Lab Akuntansi & Mini Bank AKL (jurusan-akl.jpg)',
        'assets/img/jurusan-tkr.svg' => 'Bengkel Otomotif TKR (jurusan-tkr.svg)',
        'assets/img/alumni-perusahaan.jpg' => 'Alumni Perusahaan Ternama (alumni-perusahaan.jpg)',
        'assets/img/alumni-universitas.svg' => 'Alumni Universitas Top 10 (alumni-universitas.svg)',
    ];
}

/**
 * Membuat slug URL yang rapi dari judul artikel.
 */
function buildArtikelSlug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
    $slug = trim($slug, '-');
    return $slug !== '' ? mb_substr($slug, 0, 140) : 'artikel-' . date('YmdHis');
}

/**
 * Menormalkan konten artikel: mengubah teks polos menjadi paragraf HTML dan menghapus tag berbahaya.
 */
function sanitizeArtikelContent(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }

    // Hapus script/style/event handler berbahaya
    $raw = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $raw) ?? $raw;
    $raw = preg_replace('/\son\w+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $raw) ?? $raw;

    // Jika tidak ada tag blok HTML, bungkus tiap paragraf dengan <p>
    if (!preg_match('/<(p|h2|h3|h4|ul|ol|div|blockquote)\b/i', $raw)) {
        $paragraphs = preg_split('/\R{2,}/', $raw) ?: [$raw];
        $wrapped = [];
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p !== '') {
                $wrapped[] = '<p>' . nl2br(e($p)) . '</p>';
            }
        }
        return implode("\n", $wrapped);
    }

    return $raw;
}

/**
 * Mengunggah gambar baru secara aman ke assets/img/uploads/ jika ada file diunggah.
 * Mengembalikan path relatif atau null jika tidak ada file yang diunggah.
 *
 * @throws RuntimeException bila file gagal divalidasi.
 */
function handleOptionalImageUpload(string $fieldName): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        return null;
    }

    $file = $_FILES[$fieldName];
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gagal mengunggah file gambar (kode error ' . $error . ').');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 3 * 1024 * 1024) {
        throw new RuntimeException('Ukuran file gambar maksimal 3 MB.');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $origName = (string) ($file['name'] ?? '');
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Format gambar harus JPG, JPEG, PNG, atau WEBP.');
    }

    if ($tmpPath === '' || @getimagesize($tmpPath) === false) {
        throw new RuntimeException('File yang diunggah bukan gambar yang valid.');
    }

    $uploadDir = __DIR__ . '/../assets/img/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Tidak dapat membuat folder upload gambar.');
    }

    $filename = 'portal-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpPath, $destPath)) {
        throw new RuntimeException('Gagal memindahkan file gambar ke server.');
    }

    return 'assets/img/uploads/' . $filename;
}

/**
 * Menyimpan (tambah atau perbarui) data artikel ke database.
 */
function saveArtikelRecord(PDO $pdo, array $input, ?int $id = null): string
{
    $judul = trim((string) ($input['judul'] ?? ''));
    $kategori = trim((string) ($input['kategori'] ?? 'berita'));
    $ringkasan = trim((string) ($input['ringkasan'] ?? ''));
    $konten = sanitizeArtikelContent((string) ($input['konten'] ?? ''));
    $penulis = trim((string) ($input['penulis'] ?? 'Tim Redaksi SMK BNB'));
    $waktuBaca = trim((string) ($input['waktu_baca'] ?? '5 Menit Baca'));
    $tanggal = trim((string) ($input['tanggal_publikasi'] ?? date('Y-m-d')));
    $unggulan = !empty($input['unggulan']) ? 1 : 0;
    $gambar = trim((string) ($input['gambar'] ?? 'assets/img/bnb.jpg'));

    if ($judul === '' || $ringkasan === '' || $konten === '') {
        throw new RuntimeException('Judul, ringkasan, dan konten artikel wajib diisi.');
    }

    $kategoriLabels = [
        'jurusan' => 'Program Jurusan',
        'keunggulan' => 'Keunggulan Sekolah',
        'alumni' => 'Alumni Perusahaan',
        'universitas' => 'Universitas Top 10',
        'berita' => 'Berita Sekolah',
    ];
    if (!array_key_exists($kategori, $kategoriLabels)) {
        $kategori = 'berita';
    }
    $labelKategori = trim((string) ($input['label_kategori'] ?? ''));
    if ($labelKategori === '') {
        $labelKategori = $kategoriLabels[$kategori];
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $tanggal = date('Y-m-d');
    }

    $uploadedImage = handleOptionalImageUpload('gambar_upload');
    if ($uploadedImage !== null) {
        $gambar = $uploadedImage;
    } elseif ($gambar === '') {
        $gambar = 'assets/img/bnb.jpg';
    }

    $customSlug = trim((string) ($input['slug'] ?? ''));
    $baseSlug = buildArtikelSlug($customSlug !== '' ? $customSlug : $judul);
    $slug = $baseSlug;
    $suffix = 2;

    while (true) {
        if ($id !== null) {
            $chk = $pdo->prepare('SELECT id FROM artikel WHERE slug = :slug AND id != :id LIMIT 1');
            $chk->execute([':slug' => $slug, ':id' => $id]);
        } else {
            $chk = $pdo->prepare('SELECT id FROM artikel WHERE slug = :slug LIMIT 1');
            $chk->execute([':slug' => $slug]);
        }
        if (!$chk->fetch()) {
            break;
        }
        $slug = mb_substr($baseSlug, 0, 130) . '-' . $suffix;
        $suffix++;
    }

    if ($id !== null) {
        $stmt = $pdo->prepare(
            'UPDATE artikel
             SET slug = :slug, judul = :judul, kategori = :kategori, label_kategori = :label_kategori,
                 ringkasan = :ringkasan, konten = :konten, gambar = :gambar, penulis = :penulis,
                 waktu_baca = :waktu_baca, unggulan = :unggulan, tanggal_publikasi = :tanggal
             WHERE id = :id'
        );
        $stmt->execute([
            ':slug' => $slug,
            ':judul' => mb_substr($judul, 0, 220),
            ':kategori' => $kategori,
            ':label_kategori' => mb_substr($labelKategori, 0, 60),
            ':ringkasan' => $ringkasan,
            ':konten' => $konten,
            ':gambar' => mb_substr($gambar, 0, 255),
            ':penulis' => mb_substr($penulis !== '' ? $penulis : 'Tim Redaksi SMK BNB', 0, 100),
            ':waktu_baca' => mb_substr($waktuBaca !== '' ? $waktuBaca : '5 Menit Baca', 0, 30),
            ':unggulan' => $unggulan,
            ':tanggal' => $tanggal,
            ':id' => $id,
        ]);
        return 'Artikel "' . $judul . '" berhasil diperbarui.';
    }

    $stmt = $pdo->prepare(
        'INSERT INTO artikel (slug, judul, kategori, label_kategori, ringkasan, konten, gambar, penulis, waktu_baca, unggulan, tanggal_publikasi)
         VALUES (:slug, :judul, :kategori, :label_kategori, :ringkasan, :konten, :gambar, :penulis, :waktu_baca, :unggulan, :tanggal)'
    );
    $stmt->execute([
        ':slug' => $slug,
        ':judul' => mb_substr($judul, 0, 220),
        ':kategori' => $kategori,
        ':label_kategori' => mb_substr($labelKategori, 0, 60),
        ':ringkasan' => $ringkasan,
        ':konten' => $konten,
        ':gambar' => mb_substr($gambar, 0, 255),
        ':penulis' => mb_substr($penulis !== '' ? $penulis : 'Tim Redaksi SMK BNB', 0, 100),
        ':waktu_baca' => mb_substr($waktuBaca !== '' ? $waktuBaca : '5 Menit Baca', 0, 30),
        ':unggulan' => $unggulan,
        ':tanggal' => $tanggal,
    ]);
    return 'Artikel baru "' . $judul . '" berhasil ditambahkan ke website.';
}

/**
 * Menghapus artikel berdasarkan ID (menjaga minimal 1 artikel tetap tersedia).
 */
function deleteArtikelRecord(PDO $pdo, int $id): string
{
    $total = (int) $pdo->query('SELECT COUNT(*) FROM artikel')->fetchColumn();
    if ($total <= 1) {
        throw new RuntimeException('Minimal harus tersisa 1 artikel agar halaman utama tetap terisi.');
    }
    $stmt = $pdo->prepare('DELETE FROM artikel WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return 'Artikel berhasil dihapus.';
}

/**
 * Memperbarui informasi program keahlian / jurusan.
 */
function updateJurusanRecord(PDO $pdo, int $id, array $input): string
{
    $nama = trim((string) ($input['nama'] ?? ''));
    $tagline = trim((string) ($input['tagline'] ?? ''));
    $deskripsi = trim((string) ($input['deskripsi'] ?? ''));
    $fasilitas = trim((string) ($input['fasilitas'] ?? ''));
    $prospek = trim((string) ($input['prospek_karir'] ?? ''));
    $sertifikasi = trim((string) ($input['sertifikasi'] ?? ''));
    $artikelSlug = trim((string) ($input['artikel_slug'] ?? ''));

    if ($nama === '' || $tagline === '' || $deskripsi === '') {
        throw new RuntimeException('Nama jurusan, tagline, dan deskripsi wajib diisi.');
    }

    $stmt = $pdo->prepare(
        'UPDATE jurusan
         SET nama = :nama, tagline = :tagline, deskripsi = :deskripsi,
             fasilitas = :fasilitas, prospek_karir = :prospek,
             sertifikasi = :sertifikasi, artikel_slug = :artikel_slug
         WHERE id = :id'
    );
    $stmt->execute([
        ':nama' => mb_substr($nama, 0, 120),
        ':tagline' => mb_substr($tagline, 0, 200),
        ':deskripsi' => $deskripsi,
        ':fasilitas' => $fasilitas,
        ':prospek' => $prospek,
        ':sertifikasi' => mb_substr($sertifikasi, 0, 200),
        ':artikel_slug' => mb_substr($artikelSlug, 0, 160),
        ':id' => $id,
    ]);

    return 'Data jurusan "' . $nama . '" berhasil diperbarui.';
}

/**
 * Menambah atau memperbarui kartu keunggulan sekolah.
 */
function saveKelebihanRecord(PDO $pdo, array $input, ?int $id = null): string
{
    $judul = trim((string) ($input['judul'] ?? ''));
    $ringkasan = trim((string) ($input['ringkasan'] ?? ''));
    $ikon = trim((string) ($input['ikon'] ?? 'shield-check'));
    $badge = trim((string) ($input['highlight_badge'] ?? 'Keunggulan'));
    $urutan = max(1, min(99, (int) ($input['urutan'] ?? 1)));

    if ($judul === '' || $ringkasan === '') {
        throw new RuntimeException('Judul dan ringkasan keunggulan wajib diisi.');
    }

    $allowedIcons = ['briefcase', 'award', 'cpu', 'graduation-cap', 'building', 'shield-check'];
    if (!in_array($ikon, $allowedIcons, true)) {
        $ikon = 'shield-check';
    }

    if ($id !== null) {
        $stmt = $pdo->prepare(
            'UPDATE kelebihan SET judul = :judul, ringkasan = :ringkasan, ikon = :ikon, highlight_badge = :badge, urutan = :urutan WHERE id = :id'
        );
        $stmt->execute([
            ':judul' => mb_substr($judul, 0, 150),
            ':ringkasan' => $ringkasan,
            ':ikon' => $ikon,
            ':badge' => mb_substr($badge, 0, 60),
            ':urutan' => $urutan,
            ':id' => $id,
        ]);
        return 'Keunggulan "' . $judul . '" berhasil diperbarui.';
    }

    $stmt = $pdo->prepare(
        'INSERT INTO kelebihan (judul, ringkasan, ikon, highlight_badge, urutan) VALUES (:judul, :ringkasan, :ikon, :badge, :urutan)'
    );
    $stmt->execute([
        ':judul' => mb_substr($judul, 0, 150),
        ':ringkasan' => $ringkasan,
        ':ikon' => $ikon,
        ':badge' => mb_substr($badge, 0, 60),
        ':urutan' => $urutan,
    ]);
    return 'Keunggulan "' . $judul . '" berhasil ditambahkan.';
}

/**
 * Menghapus kartu keunggulan sekolah.
 */
function deleteKelebihanRecord(PDO $pdo, int $id): string
{
    $stmt = $pdo->prepare('DELETE FROM kelebihan WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return 'Item keunggulan berhasil dihapus.';
}

/**
 * Menambah atau memperbarui data prestasi alumni (perusahaan / universitas Top 10).
 */
function saveAlumniRecord(PDO $pdo, array $input, ?int $id = null): string
{
    $nama = trim((string) ($input['nama'] ?? ''));
    $angkatan = max(2015, min(2035, (int) ($input['angkatan'] ?? date('Y'))));
    $jurusanKode = strtoupper(trim((string) ($input['jurusan_kode'] ?? 'TKJ')));
    $tipe = trim((string) ($input['tipe'] ?? 'perusahaan'));
    $instansi = trim((string) ($input['instansi'] ?? ''));
    $posisi = trim((string) ($input['posisi_atau_prodi'] ?? ''));
    $peringkat = trim((string) ($input['peringkat_info'] ?? ''));
    $kutipan = trim((string) ($input['kutipan'] ?? ''));
    $urutan = max(1, min(99, (int) ($input['urutan'] ?? 1)));

    if ($nama === '' || $instansi === '' || $posisi === '' || $kutipan === '') {
        throw new RuntimeException('Nama alumni, instansi, posisi/prodi, dan kutipan wajib diisi.');
    }

    if (!in_array($tipe, ['perusahaan', 'universitas'], true)) {
        $tipe = 'perusahaan';
    }
    if (!in_array($jurusanKode, ['TKJ', 'AKL', 'TKR'], true)) {
        $jurusanKode = 'TKJ';
    }

    if ($id !== null) {
        $stmt = $pdo->prepare(
            'UPDATE alumni_prestasi
             SET nama = :nama, angkatan = :angkatan, jurusan_kode = :jurusan_kode, tipe = :tipe,
                 instansi = :instansi, posisi_atau_prodi = :posisi, peringkat_info = :peringkat,
                 kutipan = :kutipan, urutan = :urutan
             WHERE id = :id'
        );
        $stmt->execute([
            ':nama' => mb_substr($nama, 0, 120),
            ':angkatan' => $angkatan,
            ':jurusan_kode' => $jurusanKode,
            ':tipe' => $tipe,
            ':instansi' => mb_substr($instansi, 0, 160),
            ':posisi' => mb_substr($posisi, 0, 160),
            ':peringkat' => $peringkat !== '' ? mb_substr($peringkat, 0, 100) : null,
            ':kutipan' => $kutipan,
            ':urutan' => $urutan,
            ':id' => $id,
        ]);
        return 'Data alumni "' . $nama . '" berhasil diperbarui.';
    }

    $stmt = $pdo->prepare(
        'INSERT INTO alumni_prestasi (nama, angkatan, jurusan_kode, tipe, instansi, posisi_atau_prodi, peringkat_info, kutipan, urutan)
         VALUES (:nama, :angkatan, :jurusan_kode, :tipe, :instansi, :posisi, :peringkat, :kutipan, :urutan)'
    );
    $stmt->execute([
        ':nama' => mb_substr($nama, 0, 120),
        ':angkatan' => $angkatan,
        ':jurusan_kode' => $jurusanKode,
        ':tipe' => $tipe,
        ':instansi' => mb_substr($instansi, 0, 160),
        ':posisi' => mb_substr($posisi, 0, 160),
        ':peringkat' => $peringkat !== '' ? mb_substr($peringkat, 0, 100) : null,
        ':kutipan' => $kutipan,
        ':urutan' => $urutan,
    ]);
    return 'Data alumni "' . $nama . '" berhasil ditambahkan.';
}

/**
 * Menghapus data alumni.
 */
function deleteAlumniRecord(PDO $pdo, int $id): string
{
    $stmt = $pdo->prepare('DELETE FROM alumni_prestasi WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return 'Data alumni berhasil dihapus.';
}

/**
 * Menghapus entri formulir PPDB.
 */
function deletePpdbRecord(PDO $pdo, int $id): string
{
    $stmt = $pdo->prepare('DELETE FROM pesan_ppdb WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return 'Data pendaftar PPDB berhasil dihapus.';
}
