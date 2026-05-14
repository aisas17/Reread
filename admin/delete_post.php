<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') { header('Location: ../index.php'); exit; }
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT image FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($book && !empty($book['image']) && file_exists(__DIR__.'/../uploads/'.$book['image'])) unlink(__DIR__.'/../uploads/'.$book['image']);

$stmt2 = $conn->prepare("DELETE FROM books WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$stmt2->close();
header('Location: posts.php');
exit;
