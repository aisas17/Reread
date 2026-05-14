<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();


// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("INSERT INTO book_categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->close();
}

// Fetch categories
$categories = $conn->query("SELECT * FROM book_categories ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

?>

<section class="container section">
    <h1 style="margin-bottom: 20px; color:#333;">Categories</h1
Parse error: syntax error, unexpected end of file, expecting "elseif" or "else" or "endif" in E:\WCT\Final\Reread\index.php on line 350>

    <!-- Add Category Form -->
    <form method="POST" style="margin-bottom: 20px; display:flex; gap:10px; align-items:center;">
        <input type="text" name="name" placeholder="New Category Name" required style="padding:8px; border:1px solid #ccc; border-radius:4px; flex:1;">
        <button type="submit" class="btn btn-primary" style="padding:8px 15px;">Add Category</button>
    </form>

    <!-- Categories Table -->
    <table style="width:100%; border-collapse: collapse; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color:#007BFF; color:#fff; text-align:left;">
                <th style="padding:10px; width:50px;">ID</th>
                <th style="padding:10px;">Name</th>
                <th style="padding:10px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($categories)): ?>
                <?php foreach($categories as $c): ?>
                    <tr style="border-bottom:1px solid #ddd;">
                        <td style="padding:8px;"><?= $c['id']; ?></td>
                        <td style="padding:8px;"><?= htmlspecialchars($c['name']); ?></td>
                        <td style="padding:8px;">
                            <a href="edit_category.php?id=<?= $c['id']; ?>" style="padding:5px 10px; background-color:#28a745; color:#fff; text-decoration:none; border-radius:4px; font-size:12px;">Edit</a>
                            <a href="delete_category.php?id=<?= $c['id']; ?>" style="padding:5px 10px; background-color:#dc3545; color:#fff; text-decoration:none; border-radius:4px; font-size:12px;" onclick="return confirm('Delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="padding:10px; text-align:center;">No categories found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

