<?php
declare(strict_types=1);

/**
 * Konfigurasi & Inisialisasi Koneksi MySQL (PDO) untuk SMK Bangun Nusa Bangsa.
 * Jika database belum ada saat server MySQL aktif, sistem otomatis mengeksekusi database.sql.
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'smk_bangun_nusa_bangsa');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

/**
 * Mengembalikan instance PDO yang terhubung ke database sekolah.
 * Mengembalikan null jika MySQL server belum dijalankan sehingga halaman tetap bisa menampilkan pesan ramah.
 */
function getDbConnection(?string &$errorMessage = null): ?PDO
{
    static $pdo = null;
    static $checked = false;

    if ($checked) {
        return $pdo;
    }
    $checked = true;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

        // Pastikan tabel utama sudah terisi; jika kosong, seed ulang
        $count = (int) $pdo->query('SELECT COUNT(*) FROM artikel')->fetchColumn();
        if ($count === 0) {
            seedDatabaseFromSql($pdo);
        }

        return $pdo;
    } catch (PDOException $e) {
        // Kode 1049: Unknown database -> buat database otomatis dari database.sql
        if (str_contains($e->getMessage(), '1049') || (int) $e->getCode() === 1049) {
            try {
                $rootDsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT);
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                seedDatabaseFromSql($rootPdo);

                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (Throwable $initEx) {
                $errorMessage = 'Gagal inisialisasi otomatis database: ' . $initEx->getMessage();
                return null;
            }
        }

        $errorMessage = 'Database MySQL belum terhubung (' . $e->getMessage() . '). Pastikan layanan MySQL di XAMPP aktif.';
        return null;
    }
}

/**
 * Mengeksekusi skrip database.sql secara aman.
 */
function seedDatabaseFromSql(PDO $pdo): void
{
    $sqlPath = __DIR__ . '/../database.sql';
    if (!is_file($sqlPath)) {
        return;
    }

    $sql = file_get_contents($sqlPath);
    if ($sql === false) {
        return;
    }

    // Hapus komentar baris SQL (-- ...) lalu jalankan statement per statement
    $lines = preg_split('/\R/', $sql) ?: [];
    $cleanLines = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }
        $cleanLines[] = $line;
    }

    $cleanSql = implode("\n", $cleanLines);
    $cleanSql = str_replace("\r\n", "\n", $cleanSql);
    $statements = preg_split('/;\s*(?:\n|$)/', $cleanSql) ?: [];

    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
}

/**
 * Helper escape HTML aman terhadap XSS.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Memulai sesi PHP secara aman dengan fallback direktori temp bila path bawaan tidak writable.
 */
function startSafeSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $savePath = session_save_path();
    if ($savePath === '' || !is_dir($savePath) || !is_writable($savePath)) {
        session_save_path(sys_get_temp_dir());
    }
    session_start();
}
