<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT user_id, image FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) { header('Location: profile.php'); exit; }

$isOwner = $_SESSION['user']['id'] == $book['user_id'];
$isAdmin = $_SESSION['user']['role'] === 'admin';

if (!$isOwner && !$isAdmin) { die('Unauthorized'); }

if (!empty($book['image']) && file_exists(__DIR__ . '/uploads/' . $book['image'])) {
    unlink(__DIR__ . '/uploads/' . $book['image']);
}

$stmt2 = $conn->prepare("DELETE FROM books WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$stmt2->close();

header('Location: profile.php');
exit;
