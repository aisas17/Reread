<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   LOGIN CHECK
========================= */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   GET BOOK ID
========================= */
if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit;
}

$book_id = intval($_GET['id']);
$user_id = $_SESSION['user']['id'];

/* =========================
   FETCH BOOK
========================= */
$stmt = $conn->prepare("
    SELECT * FROM books
    WHERE id=? AND user_id=?
");
$stmt->bind_param("ii", $book_id, $user_id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    die("Book not found or permission denied.");
}

/* =========================
   FETCH CATEGORIES
========================= */
$catQuery = $conn->query("
    SELECT id, name
    FROM book_categories
    ORDER BY name ASC
");
$categories = $catQuery->fetch_all(MYSQLI_ASSOC);

$errors = [];
$success = "";

/* =========================
   UPDATE FORM
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title']);
    $author      = trim($_POST['author']);
    $category_id = intval($_POST['category_id']);
    $price       = trim($_POST['price']);
    $condition   = trim($_POST['book_condition']);
    $description = trim($_POST['description']);
    $location    = trim($_POST['location']);

    if ($title == '') $errors[] = "Book title required.";
    if ($author == '') $errors[] = "Author required.";
    if ($category_id <= 0) $errors[] = "Choose category.";
    if ($price == '') $errors[] = "Price required.";
    if ($condition == '') $errors[] = "Condition required.";
    if ($location == '') $errors[] = "Location required.";

    /* IMAGE */
    $image = $book['image'];

    if (!empty($_FILES['image']['name'])) {

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allow)) {
            $errors[] = "Only JPG PNG GIF allowed.";
        } else {

            $newName = time().'_'.rand(1000,9999).".".$ext;
            $path = __DIR__."/uploads/".$newName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {

                if (!empty($book['image']) && file_exists(__DIR__.'/uploads/'.$book['image'])) {
                    unlink(__DIR__.'/uploads/'.$book['image']);
                }

                $image = $newName;

            } else {
                $errors[] = "Upload failed.";
            }
        }
    }

    /* SAVE */
    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE books SET
                category_id=?,
                title=?,
                author=?,
                price=?,
                book_condition=?,
                description=?,
                image=?,
                location=?
            WHERE id=? AND user_id=?
        ");

        $stmt->bind_param(
            "isssssssii",
            $category_id,
            $title,
            $author,
            $price,
            $condition,
            $description,
            $image,
            $location,
            $book_id,
            $user_id
        );

        if ($stmt->execute()) {
            $success = "Book updated successfully.";

            $book['title'] = $title;
            $book['author'] = $author;
            $book['category_id'] = $category_id;
            $book['price'] = $price;
            $book['book_condition'] = $condition;
            $book['description'] = $description;
            $book['image'] = $image;
            $book['location'] = $location;

        } else {
            $errors[] = "Database error.";
        }

        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<section class="section form-section">

<div class="form-box">

<h2 class="section-title">✏️ Update Book</h2>

<?php if($errors): ?>
<div class="alert alert-danger">
<ul>
<?php foreach($errors as $e): ?>
<li><?= $e; ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if($success): ?>
<div class="alert alert-success">
<?= $success; ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="post-form">

<label>Book Title</label>
<input type="text" name="title"
value="<?= htmlspecialchars($book['title']); ?>" required>

<label>Author</label>
<input type="text" name="author"
value="<?= htmlspecialchars($book['author']); ?>" required>

<label>Category</label>
<select name="category_id" required>
<option value="">Choose Category</option>

<?php foreach($categories as $c): ?>
<option value="<?= $c['id']; ?>"
<?= ($book['category_id']==$c['id']) ? 'selected' : ''; ?>>
<?= htmlspecialchars($c['name']); ?>
</option>
<?php endforeach; ?>

</select>

<label>Condition</label>
<input type="text" name="book_condition"
value="<?= htmlspecialchars($book['book_condition']); ?>" required>

<label>Price</label>
<input type="text" name="price"
value="<?= htmlspecialchars($book['price']); ?>" required>

<label>Location</label>
<input type="text" name="location"
value="<?= htmlspecialchars($book['location']); ?>" required>

<label>Description</label>
<textarea name="description"><?= htmlspecialchars($book['description']); ?></textarea>

<label>Current Image</label>

<?php if(!empty($book['image']) && file_exists(__DIR__.'/uploads/'.$book['image'])): ?>
<img src="uploads/<?= rawurlencode($book['image']); ?>" class="preview-img">
<?php else: ?>
<p>No image</p>
<?php endif; ?>

<label>Change Image</label>
<input type="file" name="image">

<button type="submit" class="btn btn-primary full-width">
Update Book
</button>

<a href="profile.php" class="back-link">← Back to Profile</a>

</form>

</div>
</section>

<?php require_once 'includes/footer.php'; ?>