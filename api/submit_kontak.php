<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

startSafeSession();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metode permintaan tidak diizinkan.']);
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Sesi formulir tidak valid. Silakan muat ulang halaman.']);
    exit;
}

$nama    = trim((string) ($_POST['nama_lengkap'] ?? ''));
$asal    = trim((string) ($_POST['asal_sekolah'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$wa      = trim((string) ($_POST['no_whatsapp'] ?? ''));
$jurusan = trim((string) ($_POST['jurusan_minat'] ?? ''));
$pesan   = trim((string) ($_POST['pesan'] ?? ''));

if ($nama === '' || $asal === '' || $email === '' || $wa === '' || $jurusan === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Mohon lengkapi seluruh kolom wajib yang bertanda (*).']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Format alamat email tidak valid.']);
    exit;
}

$allowedJurusan = ['TKJ - Teknik Komputer & Jaringan', 'AKL - Akuntansi & Keuangan Lembaga', 'TKR - Teknik Kendaraan Ringan'];
if (!in_array($jurusan, $allowedJurusan, true)) {
    $jurusan = 'TKJ - Teknik Komputer & Jaringan';
}

$dbError = null;
$pdo = getDbConnection($dbError);
if (!($pdo instanceof PDO)) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'message' => 'Database MySQL belum aktif. Aktifkan MySQL di XAMPP untuk menyimpan pendaftaran.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO pesan_ppdb (nama_lengkap, asal_sekolah, email, no_whatsapp, jurusan_minat, pesan)
         VALUES (:nama, :asal, :email, :wa, :jurusan, :pesan)'
    );
    $stmt->execute([
        ':nama'    => mb_substr($nama, 0, 120),
        ':asal'    => mb_substr($asal, 0, 140),
        ':email'   => mb_substr($email, 0, 140),
        ':wa'      => mb_substr($wa, 0, 30),
        ':jurusan' => mb_substr($jurusan, 0, 60),
        ':pesan'   => mb_substr($pesan, 0, 1000),
    ]);

    echo json_encode([
        'ok'      => true,
        'message' => 'Terima kasih, ' . $nama . '! Data konsultasi & minat PPDB jurusan ' . $jurusan . ' telah tersimpan di sistem kami. Tim Panitia PPDB akan menghubungi Anda via WhatsApp.'
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Terjadi kesalahan saat menyimpan data ke database.']);
}
