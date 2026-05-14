<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();


// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

?>

<section class="container section">
    <h1 style="margin-bottom: 20px; color:#333;">Users</h1>

    <table style="width:100%; border-collapse: collapse; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <thead>
            <tr style="background-color: #007BFF; color: #fff; text-align: left;">
                <th style="padding: 10px;">ID</th>
                <th style="padding: 10px;">Name</th>
                <th style="padding: 10px;">Email</th>
                <th style="padding: 10px;">Phone</th>
                <th style="padding: 10px;">Created At</th>
                <th style="padding: 10px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach($users as $u): ?>
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 8px;"><?= $u['id']; ?></td>
                        <td style="padding: 8px;"><?= htmlspecialchars($u['name']); ?></td>
                        <td style="padding: 8px;"><?= htmlspecialchars($u['email']); ?></td>
                        <td style="padding: 8px;"><?= htmlspecialchars($u['phone']); ?></td>
                        <td style="padding: 8px;"><?= $u['created_at']; ?></td>
                        <td style="padding: 8px;">
                            <a href="edit_user.php?id=<?= $u['id']; ?>" style="padding: 5px 10px; background-color:#28a745; color:#fff; text-decoration:none; border-radius:4px; font-size:12px;">Edit</a>
                            <a href="delete_user.php?id=<?= $u['id']; ?>" style="padding: 5px 10px; background-color:#dc3545; color:#fff; text-decoration:none; border-radius:4px; font-size:12px;" onclick="return confirm('Delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding:10px; text-align:center;">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>
