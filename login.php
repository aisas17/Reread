<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/i18n.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = t('login.err_both');
    } else {
        $stmt = $conn->prepare("
            SELECT id, name, email, password, role, phone, telegram, profile_pic
            FROM users 
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {

            // Store safe user session
            $_SESSION['user'] = [
                'id'    => (int)$user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
                'phone' => $user['phone'],
                'telegram' => $user['telegram'],
                'profile_pic' => $user['profile_pic']
            ];

            header("Location: index.php");
            exit;

        } else {
            $errors[] = t('login.err_bad_creds');
        }
    }
}

require_once 'includes/header.php';
?>

<section class="auth-page">
  <div class="container auth-shell">
    <aside class="auth-panel">
      <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= htmlspecialchars(t('login.back')); ?></a>
      <p class="eyebrow"><?= htmlspecialchars(t('login.eyebrow')); ?></p>
      <h1><?= htmlspecialchars(t('login.aside_title')); ?></h1>
      <p><?= htmlspecialchars(t('login.aside_p')); ?></p>

      <div class="auth-benefits">
        <span><i class="fas fa-heart"></i> <?= htmlspecialchars(t('login.benefit1')); ?></span>
        <span><i class="fas fa-paper-plane"></i> <?= htmlspecialchars(t('login.benefit2')); ?></span>
        <span><i class="fas fa-book-open"></i> <?= htmlspecialchars(t('login.benefit3')); ?></span>
      </div>
    </aside>

    <div class="auth-card">
      <div class="auth-card-head">
        <span class="auth-icon"><i class="fas fa-right-to-bracket"></i></span>
        <div>
          <h2><?= htmlspecialchars(t('login.card_title')); ?></h2>
          <p><?= htmlspecialchars(t('login.card_sub')); ?></p>
        </div>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <div class="form-group">
          <label for="email"><?= htmlspecialchars(t('login.label_email')); ?></label>
          <div class="input-wrap">
            <i class="far fa-envelope"></i>
            <input id="email" name="email" type="email" required placeholder="<?= htmlspecialchars(t('login.placeholder_email')); ?>" value="<?= htmlspecialchars($email ?? ''); ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="password"><?= htmlspecialchars(t('login.label_password')); ?></label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input id="password" name="password" type="password" required placeholder="<?= htmlspecialchars(t('login.placeholder_password')); ?>">
          </div>
        </div>

        <button class="auth-submit" type="submit">
          <?= htmlspecialchars(t('login.submit')); ?> <i class="fas fa-arrow-right"></i>
        </button>

        <p class="auth-note">
          <?= htmlspecialchars(t('login.note_prefix')); ?> <a href="signup.php"><?= htmlspecialchars(t('login.note_link')); ?></a>
        </p>
      </form>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
