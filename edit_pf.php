<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$errors = [];
$success = "";

/* =========================
   GET USER DATA
========================= */
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found");
}

/* =========================
   UPDATE PROFILE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $telegram = trim($_POST['telegram'] ?? '');

    if ($name === '') {
        $errors[] = "Name is required.";
    }

    if ($phone === '') {
        $errors[] = "Phone is required.";
    }

    $profile_pic = $user['profile_pic'];

    /* =========================
       IMAGE UPLOAD
    ========================= */
    if (!empty($_FILES['profile_pic']['name'])) {

        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image format.";
        } else {

            $dir = __DIR__ . "/uploads/profile/";

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $newPic = time() . "_" . rand(1000,9999) . "." . $ext;
            $uploadPath = $dir . $newPic;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {

                // delete old image
                if (!empty($profile_pic) && file_exists($dir . $profile_pic)) {
                    unlink($dir . $profile_pic);
                }

                $profile_pic = $newPic;

            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    /* =========================
       SAVE TO DB
    ========================= */
    if (empty($errors)) {

        $stmt2 = $conn->prepare("
            UPDATE users 
            SET name=?, phone=?, telegram=?, profile_pic=?
            WHERE id=?
        ");

        $stmt2->bind_param(
            "ssssi",
            $name,
            $phone,
            $telegram,
            $profile_pic,
            $user_id
        );

        if ($stmt2->execute()) {

            $success = "Profile updated successfully!";

            // UPDATE SESSION (IMPORTANT FIX)
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['telegram'] = $telegram;
            $_SESSION['user']['profile_pic'] = $profile_pic;

            // refresh local user
            $user['name'] = $name;
            $user['phone'] = $phone;
            $user['telegram'] = $telegram;
            $user['profile_pic'] = $profile_pic;

        } else {
            $errors[] = "Database error.";
        }

        $stmt2->close();
    }
}

require_once 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<section class="section signup-section">
<div class="auth-container">

    <h2 class="section-title">✏️ Edit Profile</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success); ?>
        </div>
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

    <form method="POST" enctype="multipart/form-data" class="auth-form">

        <label>Full Name</label>
        <input type="text" name="name"
               value="<?= htmlspecialchars($user['name']); ?>">

        <label>Phone</label>
        <input type="text" name="phone"
               value="<?= htmlspecialchars($user['phone']); ?>">

        <label>Telegram</label>
        <input type="text" name="telegram"
               value="<?= htmlspecialchars($user['telegram'] ?? ''); ?>">

        <label>Current Profile Picture</label><br>

        <?php if(!empty($user['profile_pic']) && file_exists(__DIR__.'/uploads/profile/'.$user['profile_pic'])): ?>
            <img src="uploads/profile/<?= rawurlencode($user['profile_pic']); ?>"
                 style="width:120px;height:120px;border-radius:50%;object-fit:cover;">
        <?php else: ?>
            <i class="fas fa-user" style="font-size:60px;"></i>
        <?php endif; ?>

        <label>Upload New Picture</label>
        <input type="file" name="profile_pic">

        <button type="submit" class="btn btn-primary full-width">
            Update Profile
        </button>

        <p class="auth-note">
            <a href="profile.php">← Back to Profile</a>
        </p>

    </form>

</div>
</section>

<?php require_once 'includes/footer.php'; ?>