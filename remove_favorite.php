<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$book_id = intval($_GET['book_id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM favorites WHERE user_id=? AND book_id=?");
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();
$stmt->close();

$redirect = $_GET['redirect'] ?? '';

if ($redirect === 'favorite') {
    header("Location: favorite.php");
} else {
    header("Location: book_detail.php?id=$book_id");
}
exit;
