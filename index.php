<?php
require_once 'includes/db.php';
session_start();

$keyword = $_GET['q'] ?? '';
$searchBooks = [];
$favoriteIds = [];
$userFavorites = [];
$popularBooks = [];

if ($keyword !== '') {
    $stmt = $conn->prepare("
        SELECT b.*, u.name AS owner_name, c.name AS category_name
        FROM books b
        JOIN users u ON u.id = b.user_id
        LEFT JOIN book_categories c ON c.id = b.category_id
        WHERE b.status='approved'
        AND (b.title LIKE ? OR c.name LIKE ?)
        ORDER BY b.created_at DESC
    ");

    $search = "%$keyword%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $searchBooks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

if (isset($_SESSION['user']['id'])) {
    $favStmt = $conn->prepare("SELECT book_id FROM favorites WHERE user_id=?");
    $favStmt->bind_param("i", $_SESSION['user']['id']);
    $favStmt->execute();
    $favRows = $favStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $favStmt->close();
    $favoriteIds = array_map('intval', array_column($favRows, 'book_id'));
    
    // Get user's favorite books
    $favBooksStmt = $conn->prepare("
        SELECT b.*, u.name AS owner_name, c.name AS category_name
        FROM favorites f
        JOIN books b ON b.id = f.book_id
        JOIN users u ON u.id = b.user_id
        LEFT JOIN book_categories c ON c.id = b.category_id
        WHERE f.user_id=? AND b.status='approved'
        ORDER BY f.created_at DESC
        LIMIT 6
    ");
    $favBooksStmt->bind_param("i", $_SESSION['user']['id']);
    $favBooksStmt->execute();
    $userFavorites = $favBooksStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $favBooksStmt->close();
} else {
    // Get popular/latest books for non-logged-in users
    $popStmt = $conn->prepare("
        SELECT b.*, u.name AS owner_name, c.name AS category_name
        FROM books b
        JOIN users u ON u.id = b.user_id
        LEFT JOIN book_categories c ON c.id = b.category_id
        WHERE b.status='approved'
        ORDER BY b.created_at DESC
        LIMIT 6
    ");
    $popStmt->execute();
    $popularBooks = $popStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $popStmt->close();
}

function home_book_cover_src(array $book): string {
    if (!empty($book['image']) && file_exists(__DIR__ . '/uploads/' . $book['image'])) {
        return 'uploads/' . rawurlencode($book['image']);
    }

    return 'assets/images/placeholder.png';
}

require_once 'includes/header.php';
?>

<section class="hero">
  <div class="container hero-inner">
    <div class="hero-copy">
      <h1><?= htmlspecialchars(t('hero.title')); ?> <span><?= htmlspecialchars(t('hero.title_span')); ?></span></h1>
      <p class="lead"><?= htmlspecialchars(t('hero.lead')); ?></p>

      <form method="GET" action="index.php" class="hero-search">
        <i class="fas fa-search"></i>
        <input type="text" name="q" placeholder="<?= htmlspecialchars(t('hero.search_placeholder')); ?>" value="<?= htmlspecialchars($keyword); ?>" required>
        <button type="submit"><?= htmlspecialchars(t('hero.search_btn')); ?></button>
      </form>

      <div class="trust-row" aria-label="Reader trust">
        <span>AR</span>
        <span>DC</span>
        <span>KL</span>
        <p><?= htmlspecialchars(t('hero.trusted')); ?> <strong>10k+</strong> <?= htmlspecialchars(t('hero.trusted_readers')); ?></p>
      </div>
    </div>

    <div class="hero-art" aria-hidden="true">
      <img class="hero-art-back" src="assets/images/history.png" alt="">
      <img class="hero-art-front" src="assets/images/novel.png" alt="">
    </div>
  </div>
</section>

<section class="container section categories-section">
  <div class="section-heading">
    <div>
      <h2 class="section-head"><?= htmlspecialchars(t('categories.head')); ?></h2>
      <p><?= htmlspecialchars(t('categories.sub')); ?></p>
    </div>
    <a href="book_list.php" class="text-link"><?= htmlspecialchars(t('categories.browse_all')); ?> <i class="fas fa-arrow-right"></i></a>
  </div>

  <div class="category-grid">
    <div class="category-card">
      <img src="assets/images/novel.png" alt="Novel books">
      <div>
        <h3>Novel</h3>
        <a href="book_list_category.php?category=Novel"><?= htmlspecialchars(t('cat.view')); ?></a>
      </div>
    </div>

    <div class="category-card">
      <img src="assets/images/education.png" alt="Education books">
      <div>
        <h3>Education</h3>
        <a href="book_list_category.php?category=Education"><?= htmlspecialchars(t('cat.view')); ?></a>
      </div>
    </div>

    <div class="category-card">
      <img src="assets/images/science.png" alt="Science books">
      <div>
        <h3>Science</h3>
        <a href="book_list_category.php?category=Science"><?= htmlspecialchars(t('cat.view')); ?></a>
      </div>
    </div>

    <div class="category-card">
      <img src="assets/images/history.png" alt="History books">
      <div>
        <h3>History</h3>
        <a href="book_list_category.php?category=History"><?= htmlspecialchars(t('cat.view')); ?></a>
      </div>
    </div>

    <div class="category-card">
      <img src="assets/images/ch.png" alt="Children books">
      <div>
        <h3>Children</h3>
        <a href="book_list_category.php?category=Children"><?= htmlspecialchars(t('cat.view')); ?></a>
      </div>
    </div>
  </div>
</section>

<?php if (isset($_SESSION['user']['id']) && !empty($userFavorites)): ?>
<section class="container section favorites-home-section">
  <div class="section-heading">
    <div>
      <h2 class="section-head"><?= htmlspecialchars(t('home.favorites_head')); ?></h2>
      <p><?= htmlspecialchars(t('home.favorites_sub')); ?></p>
    </div>
    <a href="favorite.php" class="text-link"><?= htmlspecialchars(t('home.favorites_all')); ?> <i class="fas fa-arrow-right"></i></a>
  </div>

  <div class="favorite-card-grid">
    <?php foreach ($userFavorites as $book): ?>
      <article class="favorite-card">
        <a href="book_detail.php?id=<?= (int)$book['id']; ?>" class="favorite-cover">
          <img src="<?= home_book_cover_src($book); ?>" alt="<?= htmlspecialchars($book['title']); ?>">
        </a>

        <a href="remove_favorite.php?book_id=<?= (int)$book['id']; ?>" class="favorite-heart" aria-label="<?= htmlspecialchars(t('aria.remove_favorite')); ?>">
          <i class="fas fa-heart"></i>
        </a>

        <div class="favorite-body">
          <span class="favorite-condition"><?= htmlspecialchars($book['book_condition'] ?: $book['category_name'] ?: t('word.book')); ?></span>
          <h2><a href="book_detail.php?id=<?= (int)$book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a></h2>
          <p><?= htmlspecialchars($book['author'] ?: $book['owner_name']); ?></p>
          <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php elseif (!isset($_SESSION['user']['id']) && !empty($popularBooks)): ?>
<section class="container section popular-books-section">
  <div class="section-heading">
    <div>
      <h2 class="section-head"><?= htmlspecialchars(t('home.popular_head')); ?></h2>
      <p><?= htmlspecialchars(t('home.popular_sub')); ?></p>
    </div>
    <a href="book_list.php" class="text-link"><?= htmlspecialchars(t('home.popular_browse')); ?> <i class="fas fa-arrow-right"></i></a>
  </div>

  <div class="favorite-card-grid">
    <?php foreach ($popularBooks as $book): ?>
      <article class="favorite-card">
        <a href="book_detail.php?id=<?= (int)$book['id']; ?>" class="favorite-cover">
          <img src="<?= home_book_cover_src($book); ?>" alt="<?= htmlspecialchars($book['title']); ?>">
        </a>

        <?php if (isset($_SESSION['user']['id'])): ?>
          <?php if (in_array((int)$book['id'], $favoriteIds, true)): ?>
            <a href="remove_favorite.php?book_id=<?= (int)$book['id']; ?>" class="favorite-heart" aria-label="<?= htmlspecialchars(t('aria.remove_favorite')); ?>">
              <i class="fas fa-heart"></i>
            </a>
          <?php else: ?>
            <a href="add_favorite.php?book_id=<?= (int)$book['id']; ?>" class="favorite-heart inactive" aria-label="<?= htmlspecialchars(t('aria.save_book')); ?>">
              <i class="far fa-heart"></i>
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <div class="favorite-body">
          <span class="favorite-condition"><?= htmlspecialchars($book['book_condition'] ?: $book['category_name'] ?: t('word.book')); ?></span>
          <h2><a href="book_detail.php?id=<?= (int)$book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a></h2>
          <p><?= htmlspecialchars($book['author'] ?: $book['owner_name']); ?></p>
          <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($keyword !== ''): ?>
<section class="container section search-results">
  <div class="section-heading">
    <div>
      <h2 class="section-head"><?= htmlspecialchars(t('home.search_results')); ?></h2>
      <p><?= htmlspecialchars(t('home.search_matching', ['q' => $keyword])); ?></p>
    </div>
  </div>

  <div class="favorite-card-grid home-results-grid">
    <?php if (empty($searchBooks)): ?>
      <div class="favorites-empty">
        <i class="fas fa-book-open"></i>
        <h2><?= htmlspecialchars(t('home.no_results')); ?></h2>
        <p><?= htmlspecialchars(t('home.no_results_hint')); ?></p>
      </div>
    <?php endif; ?>

    <?php foreach ($searchBooks as $b): ?>
      <article class="favorite-card">
        <a href="book_detail.php?id=<?= (int)$b['id']; ?>" class="favorite-cover">
          <img src="<?= home_book_cover_src($b); ?>" alt="<?= htmlspecialchars($b['title']); ?>">
        </a>

        <?php if (isset($_SESSION['user']['id'])): ?>
          <?php if (in_array((int)$b['id'], $favoriteIds, true)): ?>
            <a href="remove_favorite.php?book_id=<?= (int)$b['id']; ?>" class="favorite-heart" aria-label="<?= htmlspecialchars(t('aria.remove_favorite')); ?>">
              <i class="fas fa-heart"></i>
            </a>
          <?php else: ?>
            <a href="add_favorite.php?book_id=<?= (int)$b['id']; ?>" class="favorite-heart inactive" aria-label="<?= htmlspecialchars(t('aria.save_book')); ?>">
              <i class="far fa-heart"></i>
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <div class="favorite-body">
          <span class="favorite-condition"><?= htmlspecialchars($b['book_condition'] ?: $b['category_name'] ?: t('word.book')); ?></span>
          <h2><a href="book_detail.php?id=<?= (int)$b['id']; ?>"><?= htmlspecialchars($b['title']); ?></a></h2>
          <p><?= htmlspecialchars($b['author'] ?: $b['owner_name']); ?></p>
          <strong>$<?= number_format((float)$b['price'], 2); ?></strong>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="newsletter">
  <div class="container newsletter-inner">
    <p><?= htmlspecialchars(t('home.newsletter_eyebrow')); ?></p>
    <h2><?= htmlspecialchars(t('home.newsletter_title')); ?></h2>
    <span><?= htmlspecialchars(t('home.newsletter_desc')); ?></span>
    <form action="index.php" method="GET" class="newsletter-form">
      <input type="email" name="email" placeholder="<?= htmlspecialchars(t('home.newsletter_placeholder')); ?>">
      <button type="submit"><?= htmlspecialchars(t('home.newsletter_btn')); ?></button>
    </form>
  </div>
</section>

<?php if (isset($_SESSION['user']['id'])): ?>
<a href="post_book.php" class="add_post" aria-label="<?= htmlspecialchars(t('aria.post_book')); ?>">
  <i class="fas fa-plus"></i>
</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
