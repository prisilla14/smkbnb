<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Data akun cadangan apabila layanan MySQL belum dijalankan.
 */
function getFallbackPortalUsers(): array
{
    return [
        [
            'id' => 1,
            'username' => '20261001',
            'nama_lengkap' => 'Rafi Akbar Pratama',
            'role' => 'siswa',
            'jurusan_kode' => 'TKJ',
            'kelas_atau_jabatan' => 'XII TKJ 1',
            'password_hash' => '$2y$10$lJN4ts5Kk5f98hd6n6iGLunxix2NtvMlEjka2ZkJ4ibCBi0zQioru',
        ],
        [
            'id' => 2,
            'username' => '20261002',
            'nama_lengkap' => 'Nayla Azzahra',
            'role' => 'siswa',
            'jurusan_kode' => 'AKL',
            'kelas_atau_jabatan' => 'XII AKL 2',
            'password_hash' => '$2y$10$lJN4ts5Kk5f98hd6n6iGLunxix2NtvMlEjka2ZkJ4ibCBi0zQioru',
        ],
        [
            'id' => 3,
            'username' => 'guru.bnb',
            'nama_lengkap' => 'Budi Santoso, S.Kom., Gr.',
            'role' => 'guru',
            'jurusan_kode' => 'TKJ',
            'kelas_atau_jabatan' => 'Wali Kelas & Kaprodi TKJ',
            'password_hash' => '$2y$10$mF3g2OfyBRkn8v2ZYR..xuuAcpEk7XX3x/5YGMLhm2jAXCbotU7mO',
        ],
        [
            'id' => 4,
            'username' => 'admin.bnb',
            'nama_lengkap' => 'Administrator Sistem Akademik',
            'role' => 'admin',
            'jurusan_kode' => null,
            'kelas_atau_jabatan' => 'Admin Portal & Panitia PPDB',
            'password_hash' => '$2y$10$T266jHI37Ktk0HzNs6BZdeVSGPTVfTy5iRW63frwtkxoxbIH8M0GS',
        ],
    ];
}

/**
 * Memastikan tabel portal_users tersedia dan terisi pada database yang sudah ada.
 */
function ensurePortalUsersTable(PDO $pdo): void
{
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $ensured = true;

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `portal_users` (
          `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          `username` VARCHAR(60) NOT NULL UNIQUE,
          `nama_lengkap` VARCHAR(120) NOT NULL,
          `role` ENUM('siswa', 'guru', 'admin') NOT NULL DEFAULT 'siswa',
          `jurusan_kode` VARCHAR(16) DEFAULT NULL,
          `kelas_atau_jabatan` VARCHAR(100) NOT NULL,
          `password_hash` VARCHAR(255) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $count = (int) $pdo->query('SELECT COUNT(*) FROM portal_users')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO portal_users (username, nama_lengkap, role, jurusan_kode, kelas_atau_jabatan, password_hash)
             VALUES (:username, :nama_lengkap, :role, :jurusan_kode, :kelas_atau_jabatan, :password_hash)'
        );
        foreach (getFallbackPortalUsers() as $user) {
            $stmt->execute([
                ':username' => $user['username'],
                ':nama_lengkap' => $user['nama_lengkap'],
                ':role' => $user['role'],
                ':jurusan_kode' => $user['jurusan_kode'],
                ':kelas_atau_jabatan' => $user['kelas_atau_jabatan'],
                ':password_hash' => $user['password_hash'],
            ]);
        }
    }
}

/**
 * Memverifikasi login portal berdasarkan username (NIS/NIP) dan kata sandi.
 */
function authenticatePortalUser(?PDO $pdo, string $username, string $password): ?array
{
    $username = trim($username);
    if ($username === '' || $password === '') {
        return null;
    }

    $user = null;
    if ($pdo instanceof PDO) {
        ensurePortalUsersTable($pdo);
        $stmt = $pdo->prepare('SELECT * FROM portal_users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        if (is_array($row)) {
            $user = $row;
        }
    } else {
        foreach (getFallbackPortalUsers() as $fallbackUser) {
            if (strcasecmp($fallbackUser['username'], $username) === 0) {
                $user = $fallbackUser;
                break;
            }
        }
    }

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        return null;
    }

    return [
        'id' => (int) $user['id'],
        'username' => (string) $user['username'],
        'nama_lengkap' => (string) $user['nama_lengkap'],
        'role' => (string) $user['role'],
        'jurusan_kode' => $user['jurusan_kode'] !== null ? (string) $user['jurusan_kode'] : null,
        'kelas_atau_jabatan' => (string) $user['kelas_atau_jabatan'],
    ];
}

/**
 * Mengambil data pendaftar PPDB terbaru untuk dashboard guru/admin.
 */
function getRecentPpdbSubmissions(?PDO $pdo, int $limit = 8): array
{
    if (!($pdo instanceof PDO)) {
        return [];
    }

    try {
        $stmt = $pdo->prepare('SELECT id, nama_lengkap, asal_sekolah, email, no_whatsapp, jurusan_minat, pesan, created_at FROM pesan_ppdb ORDER BY created_at DESC, id DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

/**
 * Mengembalikan data ringkas portal sesuai peran pengguna.
 */
function getPortalRoleSummary(string $role, ?string $jurusanKode): array
{
    if ($role === 'guru') {
        return [
            'label' => 'Portal Guru & Wali Kelas',
            'jadwal' => [
                ['hari' => 'Senin', 'waktu' => '07.30 - 09.30 WIB', 'kegiatan' => 'Praktik Routing MikroTik & Fiber Optik — Lab TKJ 1'],
                ['hari' => 'Rabu', 'waktu' => '10.00 - 12.00 WIB', 'kegiatan' => 'Supervisi Uji Kompetensi BNSP Kelas XII'],
                ['hari' => 'Jumat', 'waktu' => '13.30 - 15.00 WIB', 'kegiatan' => 'Rapat Kurikulum Link & Match Mitra Industri'],
            ],
            'agenda' => [
                ['judul' => 'Verifikasi Portofolio SNBP Siswa Kelas XII', 'status' => 'Aktif', 'catatan' => 'Batas unggah rapor semester 1-5'],
                ['judul' => 'Monitoring PKL Mitra Telkom & Astra', 'status' => 'Berjalan', 'catatan' => '18 siswa sedang magang gelombang 2'],
                ['judul' => 'Jadwal Klinik UTBK Pekan Ini', 'status' => 'Terjadwal', 'catatan' => 'Sabtu pukul 08.00 WIB di Aula Utama'],
            ],
        ];
    }

    if ($role === 'admin') {
        return [
            'label' => 'Portal Administrator Sekolah',
            'jadwal' => [
                ['hari' => 'Harian', 'waktu' => '07.00 - 15.00 WIB', 'kegiatan' => 'Verifikasi Berkas Pendaftar PPDB 2026/2027'],
                ['hari' => 'Selasa', 'waktu' => '09.00 - 11.30 WIB', 'kegiatan' => 'Sinkronisasi Data Dapodik & Sertifikasi LSP P1'],
                ['hari' => 'Kamis', 'waktu' => '13.00 - 15.30 WIB', 'kegiatan' => 'Koordinasi Rekrutmen Bursa Kerja Khusus (BKK)'],
            ],
            'agenda' => [
                ['judul' => 'Rekapitulasi Pendaftar PPDB Jalur Prestasi', 'status' => 'Prioritas', 'catatan' => 'Pantau formulir masuk dari website utama'],
                ['judul' => 'Pembaruan Berita & Artikel Prestasi Alumni', 'status' => 'Aktif', 'catatan' => '6 artikel unggulan tayang di beranda'],
                ['judul' => 'Persiapan Tes Seleksi Mitra Industri BKK', 'status' => 'Terjadwal', 'catatan' => 'Bersama HRD Telkom, BCA, dan Astra'],
            ],
        ];
    }

    $jurusanLabel = $jurusanKode ?: 'Vokasi';
    return [
        'label' => 'Portal Siswa (' . $jurusanLabel . ')',
        'jadwal' => [
            ['hari' => 'Senin', 'waktu' => '07.15 - 11.30 WIB', 'kegiatan' => 'Praktikum Kejuruan ' . $jurusanLabel . ' — Lab Standar Industri'],
            ['hari' => 'Rabu', 'waktu' => '12.30 - 14.30 WIB', 'kegiatan' => 'Persiapan Sertifikasi Kompetensi BNSP RI'],
            ['hari' => 'Jumat', 'waktu' => '14.00 - 16.00 WIB', 'kegiatan' => 'Klinik Penalaran UTBK-SNBT & Konsultasi BKK'],
        ],
        'agenda' => [
            ['judul' => 'Status Sertifikasi Kompetensi (' . $jurusanLabel . ')', 'status' => 'Terdaftar', 'catatan' => 'Siap mengikuti uji praktik asesor BNSP'],
            ['judul' => 'Program Penyaluran Bursa Kerja Khusus', 'status' => 'Tersedia', 'catatan' => 'Akses info rekrutmen 50+ perusahaan mitra'],
            ['judul' => 'Pembinaan Jalur Top 10 PTN Indonesia', 'status' => 'Aktif', 'catatan' => 'Tryout UTBK bulanan & kurasi portofolio'],
        ],
    ];
}
