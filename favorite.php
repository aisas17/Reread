<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT b.*, u.name AS owner_name, c.name AS category_name
    FROM favorites f
    JOIN books b ON b.id = f.book_id
    JOIN users u ON u.id = b.user_id
    LEFT JOIN book_categories c ON c.id = b.category_id
    WHERE f.user_id=? AND b.status='approved'
    ORDER BY f.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function favorite_cover_src(array $book): string {
    if (!empty($book['image']) && file_exists(__DIR__ . '/uploads/' . $book['image'])) {
        return 'uploads/' . rawurlencode($book['image']);
    }

    return 'assets/images/placeholder.png';
}

require_once 'includes/header.php';
?>

<section class="favorites-page">
    <div class="container">
        <div class="favorites-head">
            <div>
                <p class="eyebrow"><?= htmlspecialchars(t('fav.eyebrow')); ?></p>
                <h1><?= htmlspecialchars(t('fav.title')); ?></h1>
                <p><?= htmlspecialchars(t('fav.sub')); ?></p>
            </div>
            <a href="book_list.php" class="text-link"><?= htmlspecialchars(t('fav.browse_more')); ?> <i class="fas fa-arrow-right"></i></a>
        </div>

        <?php if (empty($books)): ?>
            <div class="favorites-empty">
                <i class="fas fa-heart"></i>
                <h2><?= htmlspecialchars(t('fav.empty_title')); ?></h2>
                <p><?= htmlspecialchars(t('fav.empty_p')); ?></p>
                <a href="book_list.php" class="favorite-empty-link"><?= htmlspecialchars(t('fav.empty_link')); ?></a>
            </div>
        <?php else: ?>
            <div class="favorite-card-grid">
                <?php foreach ($books as $book): ?>
                    <article class="favorite-card">
                        <a href="book_detail.php?id=<?= (int)$book['id']; ?>" class="favorite-cover">
                            <img src="<?= favorite_cover_src($book); ?>" alt="<?= htmlspecialchars($book['title']); ?>">
                        </a>

                        <a href="remove_favorite.php?book_id=<?= (int)$book['id']; ?>&redirect=favorite" class="favorite-heart" aria-label="<?= htmlspecialchars(t('aria.remove_favorite')); ?>">
                            <i class="fas fa-heart"></i>
                        </a>

                        <div class="favorite-body">
                            <span class="favorite-condition"><?= htmlspecialchars($book['book_condition'] ?: t('fav.saved')); ?></span>
                            <h2><a href="book_detail.php?id=<?= (int)$book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a></h2>
                            <p><?= htmlspecialchars($book['author'] ?: $book['owner_name']); ?></p>
                            <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
