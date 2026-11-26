<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
startSafeSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: portal.php');
    exit;
}

$token = (string) ($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    header('Location: portal.php');
    exit;
}

unset($_SESSION['portal_user']);
session_regenerate_id(true);
header('Location: login.php?status=logout');
exit;
