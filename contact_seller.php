<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   CHECK LOGIN
========================= */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   GET USER ID (SELLER)
========================= */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$seller_id = intval($_GET['id']);

/* =========================
   FETCH SELLER INFO
========================= */
$stmt = $conn->prepare("SELECT name, telegram FROM users WHERE id=?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$seller = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* =========================
   VALIDATION
========================= */
if (!$seller) {
    echo "Seller not found.";
    exit;
}

if (empty($seller['telegram'])) {
    echo "This seller has no Telegram account.";
    exit;
}

/* =========================
   CLEAN TELEGRAM USERNAME
========================= */
$username = str_replace('@', '', $seller['telegram']);

/* =========================
   REDIRECT TO TELEGRAM
========================= */
header("Location: https://t.me/" . $username);
exit;