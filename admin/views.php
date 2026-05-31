<?php
require_once '../includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   SECURITY CHECK
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
   FETCH BOOK DETAIL
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Book Detail</title>

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

    body{
        font-family: Arial, sans-serif;
        background:#f5f6fa;
        margin:0;
        padding:30px;
    }

    .detail-container{
        max-width:1100px;
        margin:auto;
        background:#fff;
        border-radius:16px;
        padding:30px;
        box-shadow:0 5px 20px rgba(0,0,0,0.08);
    }

    .top-bar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:25px;
    }

    .back-btn{
        text-decoration:none;
        background:#333;
        color:#fff;
        padding:10px 18px;
        border-radius:8px;
    }

    .detail-wrapper{
        display:grid;
        grid-template-columns:350px 1fr;
        gap:35px;
    }

    .book-image img{
        width:100%;
        border-radius:14px;
        object-fit:cover;
        background:#eee;
    }

    .book-info h1{
        margin-top:0;
        margin-bottom:15px;
        font-size:32px;
    }

    .meta{
        margin-bottom:10px;
        color:#555;
        font-size:16px;
    }

    .price{
        font-size:28px;
        color:#e63946;
        font-weight:bold;
        margin:20px 0;
    }

    .status{
        display:inline-block;
        padding:8px 14px;
        border-radius:30px;
        color:#fff;
        font-size:14px;
        margin-bottom:20px;
    }

    .approved{
        background:green;
    }

    .pending{
        background:orange;
    }

    .rejected{
        background:red;
    }

    .description{
        background:#fafafa;
        padding:18px;
        border-radius:10px;
        line-height:1.7;
        margin-top:20px;
    }

    .seller-box{
        margin-top:25px;
        background:#f8f9ff;
        padding:20px;
        border-radius:12px;
    }

    .seller-box h3{
        margin-top:0;
    }

    .actions{
        margin-top:30px;
        display:flex;
        gap:12px;
        flex-wrap:wrap;
    }

    .btn{
        padding:12px 18px;
        border-radius:10px;
        color:#fff;
        text-decoration:none;
        font-weight:bold;
    }

    .approve{
        background:green;
    }

    .reject{
        background:red;
    }

    .delete{
        background:#333;
    }

    @media(max-width:768px){

        .detail-wrapper{
            grid-template-columns:1fr;
        }

    }

    </style>
</head>

<body>

<div class="detail-container">

    <div class="top-bar">
        <h2>📚 Book Review</h2>

        <a href="books.php" class="back-btn">
            ← Back
        </a>
    </div>

    <div class="detail-wrapper">

        <!-- IMAGE -->
        <div class="book-image">

            <?php if(!empty($book['image']) && file_exists("../uploads/".$book['image'])): ?>

                <img src="../uploads/<?= rawurlencode($book['image']); ?>">

            <?php else: ?>

                <img src="../assets/images/placeholder.png">

            <?php endif; ?>

        </div>

        <!-- INFO -->
        <div class="book-info">

            <h1>
                <?= htmlspecialchars($book['title']); ?>
            </h1>

            <div class="meta">
                <strong>Author:</strong>
                <?= htmlspecialchars($book['author']); ?>
            </div>

            <div class="meta">
                <strong>Category:</strong>
                <?= htmlspecialchars($book['category_name']); ?>
            </div>

            <div class="meta">
                <strong>Condition:</strong>
                <?= htmlspecialchars($book['book_condition']); ?>
            </div>

            <div class="meta">
                <strong>Location:</strong>
                <?= htmlspecialchars($book['location']); ?>
            </div>

            <div class="price">
                $<?= number_format($book['price'], 2); ?>
            </div>

            <!-- STATUS -->
            <div class="status <?= $book['status']; ?>">
                <?= ucfirst($book['status']); ?>
            </div>

            <!-- DESCRIPTION -->
            <div class="description">
                <?= nl2br(htmlspecialchars($book['description'])); ?>
            </div>

            <!-- SELLER -->
            <div class="seller-box">

                <h3>👤 Seller Information</h3>

                <p>
                    <strong>Name:</strong>
                    <?= htmlspecialchars($book['seller_name']); ?>
                </p>

                <p>
                    <strong>Email:</strong>
                    <?= htmlspecialchars($book['seller_email']); ?>
                </p>

                <p>
                    <strong>Phone:</strong>
                    <?= htmlspecialchars($book['seller_phone']); ?>
                </p>

                <p>
                    <strong>Telegram:</strong>
                    <?= htmlspecialchars($book['seller_telegram']); ?>
                </p>

            </div>

            <!-- ACTIONS -->
            <div class="actions">

                <a href="update_status.php?id=<?= $book['id']; ?>&status=approved"
                   class="btn approve">
                   ✅ Approve
                </a>

                <a href="update_status.php?id=<?= $book['id']; ?>&status=rejected"
                   class="btn reject">
                   ❌ Reject
                </a>

                <a href="delete_book.php?id=<?= $book['id']; ?>"
                   onclick="return confirm('Delete this book?')"
                   class="btn delete">
                   🗑 Delete
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>