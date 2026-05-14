<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang = $_GET['lang'] ?? '';
$return = $_GET['return'] ?? 'index.php';

if (strlen($return) > 220 || str_contains($return, '..') || preg_match('#^https?://#i', $return)) {
    $return = 'index.php';
}

if ($lang === 'km' || $lang === 'en') {
    $_SESSION['lang'] = $lang;
}

header('Location: ' . $return);
exit;
