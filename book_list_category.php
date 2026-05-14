<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/i18n.php';

/* =========================
   GET CATEGORY
========================= */
$category = trim($_GET['category'] ?? '');

if ($category == '') {
    header("Location: index.php");
    exit;
}

/* =========================
   FETCH BOOKS BY CATEGORY
========================= */
$stmt = $conn->prepare("
SELECT
    b.*,
    u.name AS owner_name,
    c.name AS category_name
FROM books b
JOIN users u ON u.id = b.user_id
LEFT JOIN book_categories c ON c.id = b.category_id
WHERE c.name = ?
AND b.status = 'approved'
ORDER BY b.created_at DESC
");

$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$bookCount = count($books);
$categoryImages = [
    'Novel' => 'assets/images/novel.png',
    'Education' => 'assets/images/education.png',
    'Science' => 'assets/images/science.png',
    'History' => 'assets/images/history.png',
    'Children' => 'assets/images/ch.png',
];
$categoryImage = $categoryImages[$category] ?? 'assets/images/placeholder.png';

$introCat = $category;
if (reread_lang() === 'en' && preg_match('/^[A-Za-z\s]+$/u', $category)) {
    $introCat = strtolower($category);
}

$categoryResultsLine = $bookCount === 1
    ? t('cat_page.results_one', ['cat' => $category])
    : t('cat_page.results', ['count' => (string) $bookCount, 'cat' => $category]);

require_once 'includes/header.php';
?>

<section class="category-page">
    <div class="container">
        <div class="category-hero">
            <div class="category-hero-copy">
                <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars(t('cat_page.back_home')); ?></a>
                <p class="eyebrow"><?= htmlspecialchars(t('cat_page.eyebrow')); ?></p>
                <h1><?= htmlspecialchars(t('cat_page.title', ['cat' => $category])); ?></h1>
                <p class="category-intro">
                    <?= htmlspecialchars(t('cat_page.intro', ['cat' => $introCat])); ?>
                </p>
                <div class="category-stats">
                    <span><?= $bookCount === 1 ? htmlspecialchars(t('cat_page.stats_book')) : htmlspecialchars(t('cat_page.stats_books', ['count' => (string) $bookCount])); ?></span>
                    <span><strong><?= htmlspecialchars(t('cat_page.approved_only')); ?></strong></span>
                </div>
            </div>

            <div class="category-hero-media">
                <img src="<?= htmlspecialchars($categoryImage); ?>" alt="<?= htmlspecialchars($category); ?> books">
            </div>
        </div>

        <div class="category-toolbar">
            <div>
                <h2><?= htmlspecialchars(t('cat_page.listings')); ?></h2>
                <p><?= htmlspecialchars($categoryResultsLine); ?></p>
            </div>
            <a href="book_list.php" class="text-link"><?= htmlspecialchars(t('cat_page.view_all')); ?> <i class="fas fa-arrow-right"></i></a>
        </div>

        <?php if (empty($books)): ?>
            <div class="empty-state category-empty">
                <i class="fas fa-book-open"></i>
                <h3><?= htmlspecialchars(t('cat_page.empty_title')); ?></h3>
                <p><?= htmlspecialchars(t('cat_page.empty_p')); ?></p>
                <a href="book_list.php" class="btn primary"><?= htmlspecialchars(t('cat_page.view_all')); ?></a>
            </div>
        <?php else: ?>
            <div class="book-grid category-book-grid">
                <?php foreach ($books as $b): ?>
                    <article class="book-card category-book-card">
                        <a class="book-img-wrap" href="book_detail.php?id=<?= (int)$b['id']; ?>" aria-label="View <?= htmlspecialchars($b['title']); ?>">
                            <?php if (!empty($b['image']) && file_exists(__DIR__.'/uploads/'.$b['image'])): ?>
                                <img src="uploads/<?= rawurlencode($b['image']); ?>" alt="<?= htmlspecialchars($b['title']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder.png" alt="Book placeholder">
                            <?php endif; ?>
                            <span class="category-badge"><?= htmlspecialchars($b['category_name']); ?></span>
                        </a>

                        <div class="book-info">
                            <h3 class="book-title">
                                <a href="book_detail.php?id=<?= (int)$b['id']; ?>"><?= htmlspecialchars($b['title']); ?></a>
                            </h3>

                            <div class="book-meta-list">
                                <p>
                                    <i class="fas fa-user"></i>
                                    <a href="profile_view.php?id=<?= (int)$b['user_id']; ?>">
                                        <?= htmlspecialchars($b['owner_name']); ?>
                                    </a>
                                </p>
                                <p>
                                    <i class="fas fa-location-dot"></i>
                                    <?= htmlspecialchars(!empty(trim($b['location'] ?? '')) ? $b['location'] : t('cat_page.no_location')); ?>
                                </p>
                            </div>

                            <div class="book-card-footer">
                                <span class="book-price">$<?= number_format((float)$b['price'], 2); ?></span>
                                <a href="book_detail.php?id=<?= (int)$b['id']; ?>" class="btn small"><?= htmlspecialchars(t('cat_page.view_details')); ?></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (isset($_SESSION['user']['id'])): ?>
<a href="post_book.php" class="add_post" aria-label="<?= htmlspecialchars(t('aria.post_book')); ?>">
    <i class="fas fa-plus"></i>
</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
