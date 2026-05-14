<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

/* =========================
   USER INFO
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: logout.php');
    exit;
}

$_SESSION['user']['name'] = $user['name'];
$_SESSION['user']['phone'] = $user['phone'];
$_SESSION['user']['telegram'] = $user['telegram'] ?? '';
$_SESSION['user']['profile_pic'] = $user['profile_pic'] ?? '';

/* =========================
   USER BOOKS
========================= */
$stmt2 = $conn->prepare("
    SELECT b.*, c.name AS category_name
    FROM books b
    LEFT JOIN book_categories c ON c.id = b.category_id
    WHERE b.user_id=?
    ORDER BY b.created_at DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$books = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

$statusCounts = [
    'approved' => 0,
    'pending' => 0,
    'rejected' => 0,
];

foreach ($books as $book) {
    if (isset($statusCounts[$book['status']])) {
        $statusCounts[$book['status']]++;
    }
}

$profilePicSrc = '';
if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/uploads/profile/' . $user['profile_pic'])) {
    $profilePicSrc = 'uploads/profile/' . rawurlencode($user['profile_pic']);
}

function profile_book_cover(array $book): string {
    if (!empty($book['image']) && file_exists(__DIR__ . '/uploads/' . $book['image'])) {
        return 'uploads/' . rawurlencode($book['image']);
    }

    return 'assets/images/placeholder.png';
}

require_once 'includes/header.php';
?>

<section class="profile-page">
    <div class="container">
        <div class="profile-hero-card">
            <div class="profile-visual">
                <?php if ($profilePicSrc !== ''): ?>
                    <img src="<?= $profilePicSrc; ?>" alt="<?= htmlspecialchars($user['name']); ?> profile picture">
                <?php else: ?>
                    <div class="profile-avatar-fallback">
                        <i class="fas fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-main-info">
                <p class="eyebrow">Reader Profile</p>
                <h1><?= htmlspecialchars($user['name']); ?></h1>
                <div class="profile-contact-grid">
                    <span><i class="far fa-envelope"></i><?= htmlspecialchars($user['email']); ?></span>
                    <span><i class="fas fa-phone"></i><?= htmlspecialchars($user['phone'] ?: 'No phone'); ?></span>
                    <span><i class="fab fa-telegram"></i><?= htmlspecialchars($user['telegram'] ?: 'No Telegram'); ?></span>
                    <span><i class="far fa-calendar"></i>Member since <?= !empty($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : 'recently'; ?></span>
                </div>

                <div class="profile-actions-row">
                    <a href="edit_pf.php?id=<?= (int)$user['id']; ?>" class="profile-action primary">
                        <i class="fas fa-pen"></i> Edit Profile
                    </a>
                    <a href="favorite.php" class="profile-action">
                        <i class="fas fa-heart"></i> My Favorites
                    </a>
                    <a href="post_book.php" class="profile-action">
                        <i class="fas fa-plus"></i> Post Book
                    </a>
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <div>
                <span>Total Listings</span>
                <strong><?= count($books); ?></strong>
            </div>
            <div>
                <span>Approved</span>
                <strong><?= $statusCounts['approved']; ?></strong>
            </div>
            <div>
                <span>Pending</span>
                <strong><?= $statusCounts['pending']; ?></strong>
            </div>
            <div>
                <span>Rejected</span>
                <strong><?= $statusCounts['rejected']; ?></strong>
            </div>
        </div>

        <div class="profile-listings-head">
            <div>
                <h2>Your Book Listings</h2>
                <p>Manage the books you have posted and review their approval status.</p>
            </div>
        </div>

        <?php if (empty($books)): ?>
            <div class="profile-empty">
                <i class="fas fa-book-open"></i>
                <h3>No books posted yet</h3>
                <p>Your listings will appear here with cover photos after you post a book.</p>
                <a href="post_book.php" class="profile-action primary">Post your first book</a>
            </div>
        <?php else: ?>
            <div class="profile-book-grid">
                <?php foreach ($books as $book): ?>
                    <article class="profile-book-card">
                        <a href="book_detail.php?id=<?= (int)$book['id']; ?>" class="profile-book-cover">
                            <img src="<?= profile_book_cover($book); ?>" alt="<?= htmlspecialchars($book['title']); ?>">
                            <span class="profile-status profile-status-<?= htmlspecialchars($book['status']); ?>">
                                <?= ucfirst(htmlspecialchars($book['status'])); ?>
                            </span>
                        </a>

                        <div class="profile-book-body">
                            <span class="profile-book-category"><?= htmlspecialchars($book['category_name'] ?: 'No category'); ?></span>
                            <h3><a href="book_detail.php?id=<?= (int)$book['id']; ?>"><?= htmlspecialchars($book['title']); ?></a></h3>
                            <p><i class="fas fa-location-dot"></i><?= htmlspecialchars($book['location'] ?: 'No location'); ?></p>

                            <div class="profile-book-meta">
                                <strong>$<?= number_format((float)$book['price'], 2); ?></strong>
                                <em><?= htmlspecialchars($book['book_condition'] ?: 'Used'); ?></em>
                            </div>

                            <div class="profile-book-actions">
                                <a href="book_detail.php?id=<?= (int)$book['id']; ?>">View</a>
                                <a href="update_book.php?id=<?= (int)$book['id']; ?>">Edit</a>
                                <a href="delete_book.php?id=<?= (int)$book['id']; ?>" onclick="return confirm('Delete this book?');">Delete</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<a href="post_book.php" class="add_post" aria-label="Post a book">
    <i class="fas fa-plus"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
