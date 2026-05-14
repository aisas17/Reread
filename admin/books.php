<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   SECURITY (ADMIN CHECK)
========================= */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$allowedStatuses = ['all', 'pending', 'approved', 'rejected'];
$statusFilter = $_GET['status'] ?? 'all';

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

/* =========================
   FETCH SUMMARY
========================= */
$summaryRows = $conn->query("
    SELECT status, COUNT(*) AS total
    FROM books
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

$statusCounts = [
    'all' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
];

foreach ($summaryRows as $row) {
    $status = $row['status'];
    $total = (int)$row['total'];

    if (isset($statusCounts[$status])) {
        $statusCounts[$status] = $total;
        $statusCounts['all'] += $total;
    }
}

/* =========================
   FETCH BOOKS
========================= */
$sql = "
    SELECT b.*, u.name AS seller_name, c.name AS category_name
    FROM books b
    JOIN users u ON u.id = b.user_id
    LEFT JOIN book_categories c ON c.id = b.category_id
";

if ($statusFilter !== 'all') {
    $sql .= " WHERE b.status = ?";
}

$sql .= " ORDER BY b.id DESC";

$stmt = $conn->prepare($sql);

if ($statusFilter !== 'all') {
    $stmt->bind_param("s", $statusFilter);
}

$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once 'includes/header.php';
?>

<section class="container section admin-books-page">
    <div class="admin-page-head">
        <div>
            <p class="admin-eyebrow">Content moderation</p>
            <h1>Book Management</h1>
            <p>Review listings, approve ready books, reject unsuitable posts, or remove duplicate entries.</p>
        </div>

        <a href="../book_list.php" class="admin-link-btn">View Public Books</a>
    </div>

    <div class="admin-stat-grid">
        <a href="books.php" class="admin-stat-card <?= $statusFilter === 'all' ? 'active' : ''; ?>">
            <span>Total Books</span>
            <strong><?= $statusCounts['all']; ?></strong>
        </a>
        <a href="books.php?status=pending" class="admin-stat-card status-pending <?= $statusFilter === 'pending' ? 'active' : ''; ?>">
            <span>Pending</span>
            <strong><?= $statusCounts['pending']; ?></strong>
        </a>
        <a href="books.php?status=approved" class="admin-stat-card status-approved <?= $statusFilter === 'approved' ? 'active' : ''; ?>">
            <span>Approved</span>
            <strong><?= $statusCounts['approved']; ?></strong>
        </a>
        <a href="books.php?status=rejected" class="admin-stat-card status-rejected <?= $statusFilter === 'rejected' ? 'active' : ''; ?>">
            <span>Rejected</span>
            <strong><?= $statusCounts['rejected']; ?></strong>
        </a>
    </div>

    <div class="admin-table-card">
        <div class="admin-table-head">
            <div>
                <h2><?= ucfirst($statusFilter); ?> Listings</h2>
                <p><?= count($books); ?> book<?= count($books) === 1 ? '' : 's'; ?> shown</p>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-books-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($books)): ?>
                        <?php foreach ($books as $b): ?>
                            <?php
                            $coverSrc = '../assets/images/placeholder.png';
                            if (!empty($b['image']) && file_exists(__DIR__ . '/../uploads/' . $b['image'])) {
                                $coverSrc = '../uploads/' . rawurlencode($b['image']);
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="admin-book-title">
                                        <img src="<?= $coverSrc; ?>" alt="<?= htmlspecialchars($b['title']); ?>">
                                        <div>
                                            <strong><?= htmlspecialchars($b['title']); ?></strong>
                                            <span>ID #<?= (int)$b['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($b['seller_name']); ?></td>
                                <td><?= htmlspecialchars($b['category_name'] ?: 'No category'); ?></td>
                                <td>$<?= number_format((float)$b['price'], 2); ?></td>
                                <td>
                                    <span class="admin-status admin-status-<?= htmlspecialchars($b['status']); ?>">
                                        <?= ucfirst(htmlspecialchars($b['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="../book_detail.php?id=<?= (int)$b['id']; ?>" class="admin-action view">View</a>

                                        <?php if ($b['status'] !== 'approved'): ?>
                                            <a href="update_status.php?id=<?= (int)$b['id']; ?>&status=approved" class="admin-action approve">Approve</a>
                                        <?php endif; ?>

                                        <?php if ($b['status'] !== 'rejected'): ?>
                                            <a href="update_status.php?id=<?= (int)$b['id']; ?>&status=rejected" class="admin-action reject">Reject</a>
                                        <?php endif; ?>

                                        <a href="delete_book.php?id=<?= (int)$b['id']; ?>"
                                           class="admin-action delete"
                                           onclick="return confirm('Delete this book?')">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty">
                                    <strong>No books found</strong>
                                    <span>There are no <?= htmlspecialchars($statusFilter); ?> listings to review right now.</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
