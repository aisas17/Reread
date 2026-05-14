<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$keyword = $_GET['q'] ?? '';

$books = [];

if ($keyword !== '') {

    $stmt = $conn->prepare("
        SELECT b.*, u.name AS owner_name, c.name AS category_name
        FROM books b
        JOIN users u ON u.id = b.user_id
        LEFT JOIN book_categories c ON c.id = b.category_id
        WHERE b.status = 'approved'
        AND (b.title LIKE ? OR c.name LIKE ?)
        ORDER BY b.created_at DESC
    ");

    $search = "%$keyword%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

require_once 'includes/header.php';
?>

<section class="list-section">

<h2 class="section-head"><?= htmlspecialchars(t('search.title')); ?></h2>

<!-- SEARCH BOX -->
<form method="GET" action="search.php" style="margin-bottom:20px;">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword); ?>" placeholder="<?= htmlspecialchars(t('search.placeholder')); ?>" style="padding:10px;width:300px;">
    <button class="btn small"><?= htmlspecialchars(t('search.btn')); ?></button>
</form>

<div class="book-grid">

<?php if ($keyword !== '' && empty($books)): ?>
    <p><?= htmlspecialchars(t('search.none')); ?></p>
<?php endif; ?>

<?php foreach ($books as $b): ?>
<div class="book-card">

    <div class="book-img-wrap">
        <?php if (!empty($b['image']) && file_exists(__DIR__.'/uploads/'.$b['image'])): ?>
            <img src="uploads/<?= rawurlencode($b['image']); ?>">
        <?php else: ?>
            <img src="assets/images/placeholder.png">
        <?php endif; ?>
    </div>

    <div class="book-info">

        <h3><?= htmlspecialchars($b['title']); ?></h3>

        <p>
            📚 <?= htmlspecialchars($b['category_name']); ?>
        </p>

        <!-- CLICKABLE USER PROFILE -->
        <p>
            👤
            <a href="profile_view.php?id=<?= $b['user_id']; ?>">
                <?= htmlspecialchars($b['owner_name']); ?>
            </a>
        </p>

        <div class="book-card-footer">
            <span>$<?= number_format($b['price'],2); ?></span>
            <a href="book_detail.php?id=<?= $b['id']; ?>" class="btn small"><?= htmlspecialchars(t('search.view')); ?></a>
        </div>

    </div>

</div>
<?php endforeach; ?>

</div>

</section>

<?php require_once 'includes/footer.php'; ?>