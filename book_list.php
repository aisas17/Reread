<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   FILTER OPTIONS
========================= */
$categories = $conn->query("
    SELECT c.id, c.name, COUNT(b.id) AS total
    FROM book_categories c
    LEFT JOIN books b ON b.category_id = c.id AND b.status = 'approved'
    GROUP BY c.id, c.name
    ORDER BY c.name ASC
")->fetch_all(MYSQLI_ASSOC);

$conditionRows = $conn->query("
    SELECT DISTINCT book_condition
    FROM books
    WHERE status = 'approved' AND book_condition IS NOT NULL AND book_condition <> ''
    ORDER BY book_condition ASC
")->fetch_all(MYSQLI_ASSOC);

$maxPriceRow = $conn->query("
    SELECT COALESCE(CEIL(MAX(price)), 100) AS max_price
    FROM books
    WHERE status = 'approved'
")->fetch_assoc();

$maxAvailablePrice = max(1, (int)$maxPriceRow['max_price']);

$selectedCategories = array_values(array_filter(array_map('intval', $_GET['categories'] ?? [])));
$selectedCondition = trim($_GET['condition'] ?? '');
$selectedMaxPrice = isset($_GET['max_price']) ? min($maxAvailablePrice, max(1, (int)$_GET['max_price'])) : $maxAvailablePrice;
$keyword = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'latest';

$sortOptions = [
    'latest' => 'b.created_at DESC',
    'price_low' => 'b.price ASC',
    'price_high' => 'b.price DESC',
    'title' => 'b.title ASC',
];

if (!isset($sortOptions[$sort])) {
    $sort = 'latest';
}

$validConditions = array_column($conditionRows, 'book_condition');
if ($selectedCondition !== '' && !in_array($selectedCondition, $validConditions, true)) {
    $selectedCondition = '';
}

/* =========================
   RECENT BOOKS
========================= */
$recentStmt = $conn->prepare("
    SELECT b.*, u.name AS owner_name, c.name AS category_name
    FROM books b
    JOIN users u ON b.user_id = u.id
    LEFT JOIN book_categories c ON c.id = b.category_id
    WHERE b.status = 'approved'
    ORDER BY b.created_at DESC
    LIMIT 4
");
$recentStmt->execute();
$recentBooks = $recentStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentStmt->close();

/* =========================
   FILTERED BOOKS
========================= */
$where = ["b.status = 'approved'", "b.price <= ?"];
$types = "d";
$params = [(float)$selectedMaxPrice];

if (!empty($selectedCategories)) {
    $placeholders = implode(',', array_fill(0, count($selectedCategories), '?'));
    $where[] = "b.category_id IN ($placeholders)";
    $types .= str_repeat('i', count($selectedCategories));
    foreach ($selectedCategories as $categoryId) {
        $params[] = $categoryId;
    }
}

if ($selectedCondition !== '') {
    $where[] = "b.book_condition = ?";
    $types .= "s";
    $params[] = $selectedCondition;
}

if ($keyword !== '') {
    $where[] = "(b.title LIKE ? OR b.author LIKE ? OR c.name LIKE ?)";
    $types .= "sss";
    $search = "%$keyword%";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$sql = "
    SELECT b.*, u.name AS owner_name, c.name AS category_name
    FROM books b
    JOIN users u ON b.user_id = u.id
    LEFT JOIN book_categories c ON c.id = b.category_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY {$sortOptions[$sort]}
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function book_cover_src(array $book): string {
    if (!empty($book['image']) && file_exists(__DIR__ . '/uploads/' . $book['image'])) {
        return 'uploads/' . rawurlencode($book['image']);
    }

    return 'assets/images/placeholder.png';
}

require_once 'includes/header.php';
?>

<section class="browse-page">
    <div class="container">
        <div class="browse-head">
            <div>
                <p class="eyebrow"><?= htmlspecialchars(t('')); ?></p>
                <h1><?= htmlspecialchars(t('All BOOkS')); ?></h1>
                <p><?= htmlspecialchars(t('browse.sub')); ?></p>
            </div>
            <a href="#all-books" class="text-link"><?= htmlspecialchars(t('browse.view_all')); ?> <i class="fas fa-arrow-down"></i></a>
        </div>

        <div class="recent-grid">
            <?php foreach ($recentBooks as $book): ?>
                <article class="recent-book">
                    <a href="book_detail.php?id=<?= (int)$book['id']; ?>" class="recent-cover">
                        <img src="<?= book_cover_src($book); ?>" alt="<?= htmlspecialchars($book['title']); ?>">
                        <span><?= htmlspecialchars(t('browse.recent_badge')); ?></span>
                    </a>
                    <h2><a href="book_detail.php?id=<?= (int)$book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a></h2>
                    <p><?= htmlspecialchars($book['author'] ?: $book['owner_name']); ?></p>
                    <div>
                        <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
                        <em><?= htmlspecialchars($book['book_condition'] ?: t('word.used')); ?></em>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="browse-layout" id="all-books">
            <aside class="browse-filters">
                <form method="GET" action="book_list.php">
                    <div class="filter-block">
                        <h2><?= htmlspecialchars(t('browse.filter_category')); ?></h2>
                        <label class="filter-check">
                            <input type="checkbox" <?= empty($selectedCategories) ? 'checked' : ''; ?> onchange="if(this.checked){document.querySelectorAll('[name=&quot;categories[]&quot;]').forEach(cb => cb.checked = false);}">
                            <span><?= htmlspecialchars(t('browse.all_genres')); ?></span>
                        </label>

                        <?php foreach ($categories as $category): ?>
                            <label class="filter-check">
                                <input type="checkbox"
                                       name="categories[]"
                                       value="<?= (int)$category['id']; ?>"
                                       <?= in_array((int)$category['id'], $selectedCategories, true) ? 'checked' : ''; ?>>
                                <span><?= htmlspecialchars($category['name']); ?></span>
                                <small><?= (int)$category['total']; ?></small>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-block">

    <h2><?= htmlspecialchars(t('browse.condition')); ?></h2>

    <?php
    $conditionPercent = [
         'Like New' => '90%-95%',
            'Very Good' => '80%-85%',
            'Good' => '60%-70%',
            'Fair' => '40%-50%'
    ];
    ?>

    <!-- ALL -->
    <label class="filter-radio">

        <input
            type="radio"
            name="condition"
            value=""
            <?= $selectedCondition === '' ? 'checked' : ''; ?>
        >

        <span>
            <?= htmlspecialchars(t('browse.all_conditions')); ?>
        </span>

    </label>

    <!-- LIKE NEW -->
    <label class="filter-radio">

        <input
            type="radio"
            name="condition"
            value="Like New"
            <?= $selectedCondition === 'Like New' ? 'checked' : ''; ?>
        >

        <span>
            Like New (90%-95%)
        </span>

    </label>

    <!-- VERY GOOD -->
    <label class="filter-radio">

        <input
            type="radio"
            name="condition"
            value="Very Good"
            <?= $selectedCondition === 'Very Good' ? 'checked' : ''; ?>
        >

        <span>
            Very Good (80%-85%)
        </span>

    </label>

    <!-- GOOD -->
    <label class="filter-radio">

        <input
            type="radio"
            name="condition"
            value="Good"
            <?= $selectedCondition === 'Good' ? 'checked' : ''; ?>
        >

        <span>
            Good (60%-70%)
        </span>

    </label>

    <!-- FAIR -->
    <label class="filter-radio">

        <input
            type="radio"
            name="condition"
            value="Fair"
            <?= $selectedCondition === 'Fair' ? 'checked' : ''; ?>
        >

        <span>
            Fair (40%-50%)
        </span>

    </label>

</div>

                    <div class="filter-block">
                        <h2><?= htmlspecialchars(t('browse.price_range')); ?></h2>
                        <input class="price-range" type="range" name="max_price" min="1" max="<?= $maxAvailablePrice; ?>" value="<?= $selectedMaxPrice; ?>">
                        <div class="price-row">
                            <span>$0</span>
                            <span>$<?= $selectedMaxPrice; ?></span>
                        </div>
                    </div>

                    <input type="hidden" name="q" value="<?= htmlspecialchars($keyword); ?>">
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort); ?>">

                    <div class="filter-actions">
                        <button type="submit"><?= htmlspecialchars(t('browse.apply')); ?></button>
                        <a href="book_list.php"><?= htmlspecialchars(t('browse.reset')); ?></a>
                    </div>
                </form>
            </aside>

            <section class="browse-results">
                <div class="results-toolbar">
                    <div>
                        <h2><?= htmlspecialchars(t('browse.found_line', ['count' => (string) count($books)])); ?></h2>
                        <?php if ($keyword !== ''): ?>
                            <p><?= htmlspecialchars(t('browse.showing_for', ['q' => $keyword])); ?></p>
                        <?php endif; ?>
                    </div>

                    <form method="GET" action="book_list.php" class="browse-search-sort">
                        <?php foreach ($selectedCategories as $categoryId): ?>
                            <input type="hidden" name="categories[]" value="<?= (int)$categoryId; ?>">
                        <?php endforeach; ?>
                        <input type="hidden" name="condition" value="<?= htmlspecialchars($selectedCondition); ?>">
                        <input type="hidden" name="max_price" value="<?= $selectedMaxPrice; ?>">

                        <label class="browse-search">
                            <i class="fas fa-search"></i>
                            <input type="search" name="q" placeholder="<?= htmlspecialchars(t('browse.search_ph')); ?>" value="<?= htmlspecialchars($keyword); ?>">
                        </label>

                        <label class="sort-select">
                            <span><?= htmlspecialchars(t('browse.sort')); ?></span>
                            <select name="sort" onchange="this.form.submit()">
                                <option value="latest" <?= $sort === 'latest' ? 'selected' : ''; ?>><?= htmlspecialchars(t('browse.sort_latest')); ?></option>
                                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : ''; ?>><?= htmlspecialchars(t('browse.sort_price_low')); ?></option>
                                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : ''; ?>><?= htmlspecialchars(t('browse.sort_price_high')); ?></option>
                                <option value="title" <?= $sort === 'title' ? 'selected' : ''; ?>><?= htmlspecialchars(t('browse.sort_title')); ?></option>
                            </select>
                        </label>
                    </form>
                </div>

                <?php if (empty($books)): ?>
                    <div class="browse-empty">
                        <i class="fas fa-book-open"></i>
                        <h3><?= htmlspecialchars(t('browse.empty_title')); ?></h3>
                        <p><?= htmlspecialchars(t('browse.empty_hint')); ?></p>
                    </div>
                <?php else: ?>
                    <div class="browse-book-grid">
                        <?php foreach ($books as $book): ?>
                            <article class="browse-card">
                                <a href="book_detail.php?id=<?= (int)$book['id']; ?>" class="browse-card-img">
                                    <img src="<?= book_cover_src($book); ?>" alt="<?= htmlspecialchars($book['title']); ?>">
                                </a>
                                <div class="browse-card-body">
                                    <span class="browse-category"><?= htmlspecialchars($book['category_name'] ?: t('browse.uncategorized')); ?></span>
                                    <h3><a href="book_detail.php?id=<?= (int)$book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a></h3>
                                    <p><?= htmlspecialchars($book['author'] ?: $book['owner_name']); ?></p>
                                    <div class="browse-card-foot">
                                        <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
                                        <em><?= htmlspecialchars($book['book_condition'] ?: t('word.used')); ?></em>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</section>

<?php if (isset($_SESSION['user']['id'])): ?>
<a href="post_book.php" class="add_post" aria-label="<?= htmlspecialchars(t('aria.post_book')); ?>">
    <i class="fas fa-plus"></i>
</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
