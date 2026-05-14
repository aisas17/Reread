<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

/* =========================
   FETCH CATEGORIES
========================= */
$catQuery = $conn->query("SELECT id, name FROM book_categories ORDER BY name ASC");
$categories = $catQuery->fetch_all(MYSQLI_ASSOC);

/* =========================
   HANDLE SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id     = $_SESSION['user']['id'];
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price       = floatval($_POST['price'] ?? 0);
    $condition   = trim($_POST['book_condition'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location    = trim($_POST['location'] ?? '');

    /* =========================
       VALIDATION
    ========================= */
    if ($title === '') $errors[] = "Book title is required.";
    if ($author === '') $errors[] = "Author is required.";
    if ($category_id <= 0) $errors[] = "Please select category.";
    if ($price <= 0) $errors[] = "Price must be valid.";
    if ($condition === '') $errors[] = "Condition required.";
    if ($location === '') $errors[] = "Location required.";

    /* =========================
       IMAGE UPLOAD
    ========================= */
    $mainImage = "";

    if (!empty($_FILES['image']['name'])) {

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Image too large (max 5MB).";
        }

        if (!in_array($ext, $allowed, true)) {
            $errors[] = "Invalid image format.";
        }

        if (empty($errors)) {

            $dir = __DIR__ . "/uploads/";

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $mainImage = time() . "_" . rand(1000,9999) . "." . $ext;
            $uploadPath = $dir . $mainImage;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $errors[] = "Upload failed.";
            }
        }

    } else {
        $errors[] = "Main image required.";
    }

    /* =========================
       INSERT DATABASE
    ========================= */
    if (empty($errors)) {

        $status = "pending";

        $stmt = $conn->prepare("
            INSERT INTO books
            (user_id, category_id, title, author, price, book_condition,
             description, image, location, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "iissdsssss",
            $user_id,
            $category_id,
            $title,
            $author,
            $price,
            $condition,
            $description,
            $mainImage,
            $location,
            $status
        );

        if ($stmt->execute()) {
            $success = "Book posted successfully! Waiting for approval.";
            $_POST = [];
        } else {
            $errors[] = "Database error.";
        }

        $stmt->close();
    }
}

$selectedCondition = $_POST['book_condition'] ?? 'Very Good';

require_once 'includes/header.php';
?>

<section class="sell-page">
    <div class="sell-hero">
        <div class="container sell-hero-inner">
            <p class="eyebrow">Sell on Reread</p>
            <h1>Sell Your Stories</h1>
            <p>Give your well-loved books a new home. Share the magic with another reader and join our sustainable literary community.</p>
        </div>
    </div>

    <div class="container sell-form-wrap">
        <div class="sell-card">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="sell-form">
                <div class="sell-field full">
                    <label for="image">Book Cover Image</label>
                    <label class="upload-dropzone" for="image">
                        <input id="image" type="file" name="image" accept="image/png,image/jpeg,image/gif">
                        <span class="upload-icon"><i class="fas fa-camera-retro"></i></span>
                        <strong>Click to upload high-quality photos</strong>
                        <small>PNG, JPG, or GIF up to 5MB</small>
                    </label>
                </div>

                <div class="sell-field full">
                    <label for="title">Book Title</label>
                    <input id="title" type="text" name="title" placeholder="e.g. The Shadow of the Wind" value="<?= htmlspecialchars($_POST['title'] ?? ''); ?>">
                </div>

                <div class="sell-grid">
                    <div class="sell-field">
                        <label for="author">Author</label>
                        <input id="author" type="text" name="author" placeholder="e.g. Carlos Ruiz Zafon" value="<?= htmlspecialchars($_POST['author'] ?? ''); ?>">
                    </div>

                    <div class="sell-field">
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">Choose category</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int)$c['id']; ?>" <?= (isset($_POST['category_id']) && (int)$_POST['category_id'] === (int)$c['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="sell-field">
                        <label for="price">Price ($)</label>
                        <input id="price" type="number" step="0.01" name="price" placeholder="0.00" value="<?= htmlspecialchars($_POST['price'] ?? ''); ?>">
                    </div>

                    <div class="sell-field">
                        <label for="location">Location</label>
                        <div class="sell-input-icon">
                            <i class="fas fa-location-dot"></i>
                            <input id="location" type="text" name="location" placeholder="City, Country" value="<?= htmlspecialchars($_POST['location'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="sell-field full">
                    <label>Book Condition</label>
                    <div class="condition-options">
                        <?php foreach (['Like New', 'Very Good', 'Good', 'Fair'] as $conditionOption): ?>
                            <label>
                                <input type="radio" name="book_condition" value="<?= htmlspecialchars($conditionOption); ?>" <?= $selectedCondition === $conditionOption ? 'checked' : ''; ?>>
                                <span><?= htmlspecialchars($conditionOption); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sell-field full">
                    <label for="description">Book Description</label>
                    <textarea id="description" name="description" placeholder="Tell us about the story and the state of the book..."><?= htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="sell-submit">Post Book</button>
                <p class="sell-note">By posting, your book will be sent to admins for approval before appearing publicly.</p>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
