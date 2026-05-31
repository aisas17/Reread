<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   GET BOOK ID
========================= */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$book_id = intval($_GET['id']);

/* =========================
   FETCH BOOK (SAFE JOIN)
========================= */
$stmt = $conn->prepare("
SELECT
    b.*,
    u.name AS seller_name,
    u.email AS seller_email,
    u.phone AS seller_phone,
    c.name AS category_name
FROM books b
JOIN users u ON u.id = b.user_id
LEFT JOIN book_categories c ON c.id = b.category_id
WHERE b.id=? AND b.status='approved'
");

$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    require_once 'includes/header.php';
    ?>
    <section class="container section">
        <div class="detail-empty">
            <h1><?= htmlspecialchars(t('detail.not_found_title')); ?></h1>
            <p><?= htmlspecialchars(t('detail.not_found_p')); ?></p>
            <a href="book_list.php" class="btn primary"><?= htmlspecialchars(t('detail.browse')); ?></a>
        </div>
    </section>
    <?php
    require_once 'includes/footer.php';
    exit;
}

$coverSrc = 'assets/images/placeholder.png';
if (!empty($book['image']) && file_exists(__DIR__ . '/uploads/' . $book['image'])) {
    $coverSrc = 'uploads/' . rawurlencode($book['image']);
}

$isLoggedIn = isset($_SESSION['user']['id']);
$isOwner = $isLoggedIn && (int)$_SESSION['user']['id'] === (int)$book['user_id'];
$isFav = false;

if ($isLoggedIn) {
    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id=? AND book_id=?");
    $check->bind_param("ii", $_SESSION['user']['id'], $book['id']);
    $check->execute();
    $isFav = (bool)$check->get_result()->fetch_assoc();
    $check->close();
}

require_once 'includes/header.php';
?>

<section class="book-detail-page">
    <div class="container">
        <a href="book_list.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars(t('detail.back')); ?></a>

        <div class="book-detail-layout">
            <div class="detail-media-panel">
                <div class="detail-main-image">
                    <img src="<?= $coverSrc; ?>" alt="<?= htmlspecialchars($book['title']); ?>">
                </div>

                <div class="detail-thumbs" aria-label="Book images">
                    <button class="detail-thumb active" type="button" aria-label="Main cover">
                        <img src="<?= $coverSrc; ?>" alt="">
                    </button>
                </div>
            </div>

            <div class="detail-content-panel">
                <div class="detail-badges">
                    <span><i class="fas fa-book-open"></i> <?= htmlspecialchars(t('detail.badge_rare')); ?></span>
                    <span><i class="fas fa-recycle"></i> <?= htmlspecialchars(t('detail.badge_green')); ?></span>
                </div>

                <h1 class="detail-title"><?= htmlspecialchars($book['title']); ?></h1>

                <?php if (!empty($book['author'])): ?>
                    <p class="detail-author"><?= htmlspecialchars(t('detail.by')); ?> <?= htmlspecialchars($book['author']); ?></p>
                <?php endif; ?>

                <div class="detail-facts">
                    <div>
                        <span><?= htmlspecialchars(t('detail.price')); ?></span>
                        <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
                    </div>
                    <?php
$conditionPercent = [
    'Like New' => '90%-95%',
            'Very Good' => '80%-85%',
            'Good' => '60%-70%',
            'Fair' => '40%-50%'
];

$currentCondition = $book['book_condition'] ?? '';
?>

<div>
    <span><?= htmlspecialchars(t('detail.condition')); ?></span>

    <strong>
        <?= htmlspecialchars($currentCondition ?: t('detail.not_specified')); ?>

        <?php if (isset($conditionPercent[$currentCondition])): ?>
            (<?= $conditionPercent[$currentCondition]; ?>)
        <?php endif; ?>
    </strong>
</div>
                    <div>
                        <span><?= htmlspecialchars(t('detail.category')); ?></span>
                        <strong><?= htmlspecialchars($book['category_name'] ?: t('detail.na')); ?></strong>
                    </div>
                </div>

                <div class="detail-info-grid">
                    <div class="detail-box">
                        <h2><i class="far fa-file-lines"></i> <?= htmlspecialchars(t('Description')); ?></h2>
                        <p>
                            <?= !empty(trim($book['description'] ?? ''))
                                ? nl2br(htmlspecialchars($book['description']))
                                : htmlspecialchars(t('detail.no_desc')); ?>
                        </p>
                    </div>

                    <div class="detail-box">
                        <h2><i class="fas fa-circle-check"></i> <?= htmlspecialchars(t('detail.listing')); ?></h2>
                        <ul>
                            <li><i class="fas fa-check"></i> <?= htmlspecialchars(t('detail.approved')); ?></li>
                            <li><i class="fas fa-check"></i> <?= htmlspecialchars($book['book_condition'] ?: t('detail.condition_none')); ?></li>
                            <li><i class="fas fa-location-dot"></i> <?= htmlspecialchars(!empty(trim($book['location'] ?? '')) ? $book['location'] : t('detail.loc_none')); ?></li>
                        </ul>
                    </div>
                </div>

                <div class="seller-card detail-seller-card">
                    <h2><i class="far fa-user"></i> <?= htmlspecialchars(t('detail.seller')); ?></h2>
                    <div class="seller-details">
                        <p>
                            <span><?= htmlspecialchars(t('detail.name')); ?></span>
                            <a href="profile_view.php?id=<?= (int)$book['user_id']; ?>">
                                <?= htmlspecialchars($book['seller_name']); ?>
                            </a>
                        </p>
                        <p>
                            <span><?= htmlspecialchars(t('detail.email')); ?></span>
                            <a href="mailto:<?= htmlspecialchars($book['seller_email']); ?>">
                                <?= htmlspecialchars($book['seller_email']); ?>
                            </a>
                        </p>
                        <?php if (!empty($book['seller_phone'])): ?>
                            <p>
                                <span><?= htmlspecialchars(t('detail.phone')); ?></span>
                                <?= htmlspecialchars($book['seller_phone']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-actions">
                    <?php if (!$isLoggedIn): ?>
                        <a href="login.php" class="detail-btn detail-btn-primary">
                            <i class="fas fa-right-to-bracket"></i> <?= htmlspecialchars(t('detail.login_contact')); ?>
                        </a>
                    <?php elseif ($isOwner): ?>
                        <a href="update_book.php?id=<?= (int)$book['id']; ?>" class="detail-btn detail-btn-primary">
                            <i class="fas fa-pen"></i> <?= htmlspecialchars(t('detail.edit')); ?>
                        </a>
                        <a href="delete_book.php?id=<?= (int)$book['id']; ?>"
                           class="detail-btn detail-btn-outline"
                           onclick="return confirm(<?= json_encode(t('detail.confirm_delete'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>);">
                            <i class="fas fa-trash"></i> <?= htmlspecialchars(t('detail.delete')); ?>
                        </a>
                    <?php else: ?>
                        <a href="contact_seller.php?id=<?= (int)$book['user_id']; ?>" class="detail-btn detail-btn-primary">
                            <i class="fas fa-paper-plane"></i> <?= htmlspecialchars(t('detail.contact')); ?>
                        </a>

                        <?php if ($isFav): ?>
                            <a href="remove_favorite.php?book_id=<?= (int)$book['id']; ?>" class="detail-fav active" aria-label="<?= htmlspecialchars(t('aria.remove_favorite')); ?>">
                                <i class="fas fa-heart"></i>
                            </a>
                        <?php else: ?>
                            <a href="add_favorite.php?book_id=<?= (int)$book['id']; ?>" class="detail-fav" aria-label="<?= htmlspecialchars(t('aria.save_book')); ?>">
                                <i class="far fa-heart"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <p class="detail-note"><?= htmlspecialchars(t('detail.note')); ?></p>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
