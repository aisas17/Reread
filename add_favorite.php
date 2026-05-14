<?php
require_once 'includes/db.php';
session_start();

/* CHECK LOGIN */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$book_id = intval($_GET['book_id'] ?? 0);

if ($book_id <= 0) {
    header("Location: index.php");
    exit;
}

/* CHECK IF ALREADY FAVORITE */
$stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id=? AND book_id=?");
$stmt->bind_param("ii", $user_id, $book_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exists) {
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, book_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $book_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: book_detail.php?id=$book_id");
exit;