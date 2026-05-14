<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Fetch summary
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$totalBooks = $conn->query("SELECT COUNT(*) AS total FROM books")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) AS total FROM book_categories")->fetch_assoc()['total'];

require_once 'includes/header.php';
?>

<section class="container section">
    <h1>Admin Dashboard</h1>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <div class="card">
            <h3>Total Users</h3>
            <p><?= $totalUsers; ?></p>
        </div>

        <div class="card">
            <h3>Total Books</h3>
            <p><?= $totalBooks; ?></p>
        </div>

        <div class="card">
            <h3>Total Categories</h3>
            <p><?= $totalCategories; ?></p>
        </div>
    </div>

    <!-- Quick Links -->
    <h2>Quick Links</h2>
    <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:15px;">

        <a href="users.php" class="btn btn-primary">Manage Users</a>

        <a href="books.php" class="btn btn-primary">Manage Books</a>

        <a href="categories.php" class="btn btn-primary">Manage Categories</a>

        <!-- NEW BUTTON -->
        <a href="../index.php" class="btn btn-success">Go to Homepage</a>

        <!-- Optional -->
        <a href="../logout.php" class="btn btn-danger">Logout</a>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>