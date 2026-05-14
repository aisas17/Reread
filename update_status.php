<?php
require_once '../includes/db.php';
session_start();

/* =========================
   ADMIN CHECK
========================= */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
   GET DATA
========================= */
$id = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

$allowed = ['approved', 'rejected'];

if ($id > 0 && in_array($status, $allowed)) {

    $stmt = $conn->prepare("UPDATE books SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: books.php");
exit;