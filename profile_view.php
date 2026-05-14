<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   GET USER ID
========================= */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$user_id = intval($_GET['id']);

/* =========================
   FETCH USER INFO
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found";
    exit;
}

/* =========================
   FETCH USER BOOKS
========================= */
$stmt = $conn->prepare("
    SELECT b.*, c.name AS category_name
    FROM books b
    LEFT JOIN book_categories c ON c.id = b.category_id
    WHERE b.user_id=?
    ORDER BY b.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<section class="container section">

    <!-- ================= PROFILE CARD ================= -->
    <div class="profile-card-pro">

        <div class="profile-avatar">
            <i class="fas fa-user"></i>
        </div>

        <div class="profile-info">
            <h2><?= htmlspecialchars($user['name']); ?></h2>

            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']); ?></p>

            <?php if (!empty($user['telegram'])): ?>
                <p><strong>Telegram:</strong> <?= htmlspecialchars($user['telegram']); ?></p>
            <?php endif; ?>

            <p><strong>Member Since:</strong>
                <?= date('M Y', strtotime($user['created_at'])); ?>
            </p>
        </div>

    </div>

    <!-- ================= USER BOOKS ================= -->
    <h2 class="section-head" style="margin-top:30px;">
        Books by <?= htmlspecialchars($user['name']); ?>
    </h2>

    <div class="book-grid">

        <?php if (empty($books)): ?>
            <p class="muted">This user has not posted any books yet.</p>
        <?php endif; ?>

        <?php foreach ($books as $b): ?>
            <div class="book-card">

                <!-- IMAGE -->
                <div class="book-img-wrap">
                    <?php if (!empty($b['image']) && file_exists(__DIR__ . '/uploads/' . $b['image'])): ?>
                        <img src="uploads/<?= rawurlencode($b['image']); ?>">
                    <?php else: ?>
                        <img src="assets/images/placeholder.png">
                    <?php endif; ?>
                </div>

                <!-- INFO -->
                <div class="book-info">

                    <h3 class="book-title">
                        <?= htmlspecialchars($b['title']); ?>
                    </h3>

                    <p class="muted">
                        📚 <?= htmlspecialchars($b['category_name'] ?? 'No category'); ?>
                    </p>

                    <p class="muted">
                        📍 <?= htmlspecialchars($b['location']); ?>
                    </p>

                    <div class="book-card-footer">

                        <span class="book-price">
                            $<?= number_format($b['price'], 2); ?>
                        </span>

                        <a href="book_detail.php?id=<?= $b['id']; ?>" class="btn small">
                            View
                        </a>

                    </div>

                </div>

            </div>
        <?php endforeach; ?>

    </div>

</section>

<!-- FLOAT BUTTON -->
<?php if (isset($_SESSION['user']['id'])): ?>
<a href="post_book.php" class="add_post">
    <i class="fas fa-plus"></i>
</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>