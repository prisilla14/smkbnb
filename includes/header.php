<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
startSafeSession();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pageTitle = $pageTitle ?? 'SMK Bangun Nusa Bangsa — Unggul Vokasi, Siap Kerja & Tembus PTN Top 10';
$pageDesc  = $pageDesc ?? 'Portal resmi SMK Bangun Nusa Bangsa. Program keahlian unggulan TKJ, AKL, dan TKR dengan fasilitas industri modern, penyaluran perusahaan ternama, dan pembinaan Universitas Top 10.';
$isHome    = $isHome ?? false;
$isPortal  = $isPortal ?? false;
$navPrefix = $isHome ? '' : 'index.php';
$portalUser = $_SESSION['portal_user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDesc) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.2">
</head>
<body>
    <!-- Top Info Ticker -->
    <div class="top-bar">
        <div class="container top-bar-inner">
            <div class="top-bar-left">
                <span class="pulse-dot"></span>
                <span><strong>PPDB 2026/2027 Dibuka!</strong> Jalur Prestasi, Industri &amp; Beasiswa Top 10 PTN</span>
            </div>
            <div class="top-bar-right">
                <span>Akreditasi <strong>A (Unggul)</strong></span>
                <span class="divider-dot">•</span>
                <span>TKJ • AKL • TKR</span>
            </div>
        </div>
    </div>

    <!-- Sticky Navbar -->
    <header class="site-header" id="siteHeader">
        <div class="container nav-container">
            <a href="index.php" class="brand" aria-label="Beranda SMK Bangun Nusa Bangsa">
                <div class="brand-emblem">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="40" height="40" rx="11" fill="url(#bnbGrad)"/>
                        <path d="M20 7L32 13V21C32 27.9 26.8 33.4 20 35C13.2 33.4 8 27.9 8 21V13L20 7Z" stroke="white" stroke-width="2.2" stroke-linejoin="round"/>
                        <path d="M15 21L18.5 24.5L25.5 16.5" stroke="#4ADE80" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="bnbGrad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#80132C"/>
                                <stop offset="1" stop-color="#16A34A"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-name">SMK BANGUN NUSA BANGSA</span>
                    <span class="brand-sub">Cerdas Vokasi • Berkarakter Juara</span>
                </div>
            </a>

            <nav class="nav-links" id="navMenu" aria-label="Navigasi Utama">
                <a href="<?= $navPrefix ?>#beranda" class="nav-link">Beranda</a>
                <a href="<?= $navPrefix ?>#keunggulan" class="nav-link">Keunggulan</a>
                <a href="<?= $navPrefix ?>#jurusan" class="nav-link">Jurusan</a>
                <a href="<?= $navPrefix ?>#artikel" class="nav-link">Artikel</a>
                <a href="<?= $navPrefix ?>#alumni" class="nav-link">Alumni &amp; PTN</a>
                <div class="nav-cta-group">
                    <a href="<?= $navPrefix ?>#daftar" class="btn btn-nav-cta">Daftar PPDB</a>
                    <?php if (is_array($portalUser)): ?>
                        <a href="portal.php" class="btn btn-nav-portal<?= $isPortal ? ' active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Portal Saya</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-nav-portal<?= $isPortal ? ' active' : '' ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                            <span>Login Portal</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>

            <button class="mobile-toggle" id="mobileToggle" aria-label="Buka Menu" aria-expanded="false" aria-controls="navMenu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>
