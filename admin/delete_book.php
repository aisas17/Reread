<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();


if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$book_id = intval($_GET['id']);

// Fetch book image
$stmt = $conn->prepare("SELECT image FROM books WHERE id=?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Delete image file
if (!empty($result['image']) && file_exists(__DIR__ . "/../uploads/" . $result['image'])) {
    unlink(__DIR__ . "/../uploads/" . $result['image']);
}

// Delete book record
$stmt = $conn->prepare("DELETE FROM books WHERE id=?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$stmt->close();

header("Location: books.php");
exit;
