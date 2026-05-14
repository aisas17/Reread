<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();


if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = intval($_GET['id']);

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

header("Location: users.php");
exit;
