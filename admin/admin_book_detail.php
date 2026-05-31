<?php
require_once '../includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   ADMIN CHECK
========================= */
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
   GET BOOK ID
========================= */
if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit;
}

$book_id = intval($_GET['id']);

/* =========================
   FETCH BOOK
========================= */
$stmt = $conn->prepare("
SELECT 
    b.*,
    u.name AS seller_name,
    u.email AS seller_email,
    u.phone AS seller_phone,
    u.telegram AS seller_telegram,
    c.name AS category_name
FROM books b
JOIN users u ON u.id = b.user_id
LEFT JOIN book_categories c ON c.id = b.category_id
WHERE b.id=?
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    echo "Book not found.";
    exit;
}

require_once 'includes/header.php';
?>

<style>
/* ===== CLEAN ADMIN DETAIL UI ===== */
.admin-detail-wrap{
    max-width:1100px;
    margin:30px auto;
    display:grid;
    grid-template-columns: 1fr 1.2fr;
    gap:25px;
}

.admin-card{
    background:#fff;
    border-radius:16px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
    padding:20px;
}

.admin-img{
    width:100%;
    border-radius:14px;
    object-fit:cover;
    max-height:420px;
}

.title-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
}

h1{
    font-size:22px;
    margin:0;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
    color:#fff;
}

.badge.approved{background:#28a745;}
.badge.pending{background:#ff9800;}
.badge.rejected{background:#e74c3c;}

.info p{
    margin:8px 0;
    color:#444;
    font-size:14px;
}

.section-title{
    margin-top:18px;
    font-weight:bold;
    font-size:15px;
    color:#222;
}

.box{
    background:#f8f9fb;
    padding:12px;
    border-radius:10px;
    font-size:14px;
    line-height:1.6;
}

.seller{
    margin-top:15px;
    padding-top:10px;
    border-top:1px solid #eee;
}

.actions{
    display:flex;
    gap:10px;
    margin-top:20px;
}

.btn{
    padding:8px 12px;
    border-radius:8px;
    text-decoration:none;
    font-size:13px;
    color:#fff;
}

.approve{background:#28a745;}
.reject{background:#e74c3c;}
.delete{background:#333;}

@media(max-width:900px){
    .admin-detail-wrap{
        grid-template-columns:1fr;
    }
}
</style>

<section class="admin-detail-wrap">

    <!-- LEFT: IMAGE -->
    <div class="admin-card">
        <?php if (!empty($book['image']) && file_exists(__DIR__ . '/../uploads/' . $book['image'])): ?>
            <img class="admin-img" src="../uploads/<?= rawurlencode($book['image']); ?>">
        <?php else: ?>
            <img class="admin-img" src="../assets/images/placeholder.png">
        <?php endif; ?>
    </div>

    <!-- RIGHT: INFO -->
    <div class="admin-card">

        <div class="title-row">
            <h1><?= htmlspecialchars($book['title']); ?></h1>

            <span class="badge <?= $book['status']; ?>">
                <?= ucfirst($book['status']); ?>
            </span>
        </div>

        <div class="info">

            <p><strong>Author:</strong> <?= htmlspecialchars($book['author']); ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($book['category_name'] ?? 'N/A'); ?></p>
            <p><strong>Condition:</strong> <?= htmlspecialchars($book['book_condition']); ?></p>
            <p><strong>Price:</strong> $<?= number_format($book['price'],2); ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($book['location']); ?></p>

            <div class="section-title">Description</div>
            <div class="box">
                <?= nl2br(htmlspecialchars($book['description'])); ?>
            </div>

            <div class="seller">
                <div class="section-title">Seller Info</div>

                <p><strong>Name:</strong> <?= htmlspecialchars($book['seller_name']); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($book['seller_email']); ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($book['seller_phone']); ?></p>
                <p><strong>Telegram:</strong> <?= htmlspecialchars($book['seller_telegram']); ?></p>
            </div>

            <div class="actions">

                <?php if ($book['status'] !== 'approved'): ?>
                    <a class="btn approve"
                       href="update_status.php?id=<?= $book['id']; ?>&status=approved">
                        Approve
                    </a>
                <?php endif; ?>

                <?php if ($book['status'] !== 'rejected'): ?>
                    <a class="btn reject"
                       href="update_status.php?id=<?= $book['id']; ?>&status=rejected">
                        Reject
                    </a>
                <?php endif; ?>

                <a class="btn delete"
                   onclick="return confirm('Delete this book?')"
                   href="delete_book.php?id=<?= $book['id']; ?>">
                    Delete
                </a>

            </div>

        </div>

    </div>

</section>

<?php require_once 'includes/footer.php'; ?>