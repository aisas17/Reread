<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/i18n.php';

$errors = [];
$success = "";

// Keep old input
$name  = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($name);
    $email    = trim($email);
    $phone    = trim($phone);
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Validation
    if ($name === '' || $email === '' || $phone === '' || $password === '' || $confirm === '') {
        $errors[] = t('signup.err_required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = t('signup.err_email');
    }

    if (strlen($password) < 6) {
        $errors[] = t('signup.err_pass_len');
    }

    if ($password !== $confirm) {
        $errors[] = t('signup.err_pass_match');
    }

    // Check email exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = t('signup.err_email_taken');
        }
        $stmt->close();
    }

    // Insert user
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = "user";

        $stmt = $conn->prepare("
            INSERT INTO users (name, email, phone, password, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("sssss", $name, $email, $phone, $hashed, $role);

        if ($stmt->execute()) {
            $success = t('signup.success');
            $name = $email = $phone = ""; // clear form
        } else {
            $errors[] = t('signup.err_generic');
        }
        $stmt->close();
    }
}

require_once 'includes/header.php';
?>

<section class="auth-page">
    <div class="container auth-shell auth-shell-signup">
        <aside class="auth-panel">
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars(t('signup.back')); ?></a>
            <p class="eyebrow"><?= htmlspecialchars(t('signup.eyebrow')); ?></p>
            <h1><?= htmlspecialchars(t('signup.aside_title')); ?></h1>
            <p><?= htmlspecialchars(t('signup.aside_p')); ?></p>

            <div class="auth-benefits">
                <span><i class="fas fa-camera"></i> <?= htmlspecialchars(t('signup.benefit1')); ?></span>
                <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars(t('signup.benefit2')); ?></span>
                <span><i class="fas fa-recycle"></i> <?= htmlspecialchars(t('signup.benefit3')); ?></span>
            </div>
        </aside>

        <div class="auth-card">
            <div class="auth-card-head">
                <span class="auth-icon"><i class="fas fa-user-plus"></i></span>
                <div>
                    <h2><?= htmlspecialchars(t('signup.card_title')); ?></h2>
                    <p><?= htmlspecialchars(t('signup.card_sub')); ?></p>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="name"><?= htmlspecialchars(t('signup.label_name')); ?></label>
                    <div class="input-wrap">
                        <i class="far fa-user"></i>
                        <input id="name" type="text" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="<?= htmlspecialchars(t('signup.ph_name')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email"><?= htmlspecialchars(t('signup.label_email')); ?></label>
                    <div class="input-wrap">
                        <i class="far fa-envelope"></i>
                        <input id="email" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="<?= htmlspecialchars(t('signup.ph_email')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone"><?= htmlspecialchars(t('signup.label_phone')); ?></label>
                    <div class="input-wrap">
                        <i class="fas fa-phone"></i>
                        <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="<?= htmlspecialchars(t('signup.ph_phone')); ?>" required>
                    </div>
                </div>

                <div class="auth-form-grid">
                    <div class="form-group">
                        <label for="password"><?= htmlspecialchars(t('signup.label_password')); ?></label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input id="password" type="password" name="password" placeholder="<?= htmlspecialchars(t('signup.ph_password')); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm"><?= htmlspecialchars(t('signup.label_confirm')); ?></label>
                        <div class="input-wrap">
                            <i class="fas fa-shield-halved"></i>
                            <input id="confirm" type="password" name="confirm" placeholder="<?= htmlspecialchars(t('signup.ph_confirm')); ?>" required>
                        </div>
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <?= htmlspecialchars(t('signup.submit')); ?> <i class="fas fa-arrow-right"></i>
                </button>

                <p class="auth-note">
                    <?= htmlspecialchars(t('signup.note_prefix')); ?> <a href="login.php"><?= htmlspecialchars(t('signup.note_link')); ?></a>
                </p>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
