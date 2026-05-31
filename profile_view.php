<?php
require_once 'includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   CHECK USER ID
========================= */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$user_id = (int) $_GET['id'];

/* =========================
   USER INFO
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$user) {
    header("Location: index.php");
    exit;
}

/* =========================
   USER BOOKS
========================= */
$stmt2 = $conn->prepare("
    SELECT 
        b.*, 
        c.name AS category_name
    FROM books b
    LEFT JOIN book_categories c 
        ON c.id = b.category_id
    WHERE b.user_id=?
    ORDER BY b.created_at DESC
");

$stmt2->bind_param("i", $user_id);
$stmt2->execute();

$books = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt2->close();

/* =========================
   PROFILE IMAGE
========================= */
$profileImg = '';

if (
    !empty($user['profile_pic']) &&
    file_exists(__DIR__ . '/uploads/profile/' . $user['profile_pic'])
) {
    $profileImg = 'uploads/profile/' . rawurlencode($user['profile_pic']);
}

/* =========================
   BOOK COVER FUNCTION
========================= */
function book_cover(array $book): string
{
    if (
        !empty($book['image']) &&
        file_exists(__DIR__ . '/uploads/' . $book['image'])
    ) {
        return 'uploads/' . rawurlencode($book['image']);
    }

    return 'assets/images/placeholder.png';
}

$total = count($books);

require_once 'includes/header.php';
?>

<section class="profile-page">

    <div class="container">

        <!-- PROFILE HERO -->
        <div class="profile-hero-card">

            <div class="profile-visual">

                <?php if ($profileImg !== ''): ?>

                    <img
                        src="<?= $profileImg; ?>"
                        alt="<?= htmlspecialchars($user['name']); ?>"
                        class="profile-avatar-img"
                    >

                <?php else: ?>

                    <div class="profile-avatar-fallback">
                        <i class="fas fa-user"></i>
                    </div>

                <?php endif; ?>

            </div>

            <div class="profile-main-info">

                <p class="eyebrow">Public Profile</p>

                <h1>
                    <?= htmlspecialchars($user['name']); ?>
                </h1>

                <div class="profile-contact-grid">

                    <span>
                        <i class="far fa-envelope"></i>
                        <?= htmlspecialchars($user['email']); ?>
                    </span>

                    <span>
                        <i class="fas fa-phone"></i>
                        <?= htmlspecialchars($user['phone'] ?: 'No phone'); ?>
                    </span>

                    <span>
                        <i class="fab fa-telegram"></i>
                        <?= htmlspecialchars($user['telegram'] ?: 'No Telegram'); ?>
                    </span>

                    <span>
                        <i class="far fa-calendar"></i>

                        Member since
                        <?= !empty($user['created_at']) 
                            ? date('M Y', strtotime($user['created_at'])) 
                            : 'Recently'; ?>
                    </span>

                </div>

            </div>

        </div>

        <!-- STATS -->
        <div class="profile-stats">

            <div>
                <span>Total Listings</span>
                <strong><?= $total; ?></strong>
            </div>

            <div>
                <span>Available Books</span>
                <strong><?= $total; ?></strong>
            </div>

            <div>
                <span>Member Status</span>
                <strong>Active</strong>
            </div>

        </div>

        <!-- HEADER -->
        <div class="profile-listings-head">

            <div>

                <h2>
                    Books by <?= htmlspecialchars($user['name']); ?>
                </h2>

                <p>
                    Browse all books posted by this user.
                </p>

            </div>

        </div>

        <!-- EMPTY STATE -->
        <?php if (empty($books)): ?>

            <div class="profile-empty">

                <i class="fas fa-book-open"></i>

                <h3>No books posted yet</h3>

                <p>
                    This user hasn't shared any books.
                </p>

            </div>

        <?php else: ?>

            <!-- BOOK GRID -->
            <div class="profile-book-grid">

                <?php foreach ($books as $b): ?>

                    <article class="profile-book-card">

                        <a
                            href="book_detail.php?id=<?= (int)$b['id']; ?>"
                            class="profile-book-cover"
                        >

                            <img
                                src="<?= book_cover($b); ?>"
                                alt="<?= htmlspecialchars($b['title']); ?>"
                            >

                            <span class="profile-status">

                                <?= htmlspecialchars(
                                    $b['category_name'] ?: 'No category'
                                ); ?>

                            </span>

                        </a>

                        <div class="profile-book-body">

                            <span class="profile-book-category">

                                <?= htmlspecialchars(
                                    $b['category_name'] ?: 'No category'
                                ); ?>

                            </span>

                            <h3>

                                <a href="book_detail.php?id=<?= (int)$b['id']; ?>">

                                    <?= htmlspecialchars($b['title']); ?>

                                </a>

                            </h3>

                            <p>

                                <i class="fas fa-location-dot"></i>

                                <?= htmlspecialchars(
                                    $b['location'] ?: 'No location'
                                ); ?>

                            </p>

                            <div class="profile-book-meta">

                                <strong>
                                    $<?= number_format((float)$b['price'], 2); ?>
                                </strong>

                                <em>
                                    <?= htmlspecialchars(
                                        $b['book_condition'] ?: 'Used'
                                    ); ?>
                                </em>

                            </div>

                            <div class="profile-book-actions">

                                <a href="book_detail.php?id=<?= (int)$b['id']; ?>">
                                    View
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</section>

<!-- FLOAT BUTTON -->
<?php if (isset($_SESSION['user']['id'])): ?>

    <a href="post_book.php" class="add_post" aria-label="Post Book">

        <i class="fas fa-plus"></i>

    </a>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>