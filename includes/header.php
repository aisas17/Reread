<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/i18n.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$bookPages = ['book_list.php', 'book_list_category.php', 'book_detail.php'];
$profilePic = $_SESSION['user']['profile_pic'] ?? '';
$profilePicPath = '';

if ($profilePic !== '' && file_exists(__DIR__ . '/../uploads/profile/' . $profilePic)) {
    $profilePicPath = 'uploads/profile/' . rawurlencode($profilePic);
}

$__script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$__return = $__script . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$__returnEnc = rawurlencode($__return);
$__lang = reread_lang();
?>
<!doctype html>
<html lang="<?= htmlspecialchars($__lang, ENT_QUOTES, 'UTF-8'); ?>" class="lang-<?= $__lang === 'km' ? 'km' : 'en'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?= htmlspecialchars(t('site.title'), ENT_QUOTES, 'UTF-8'); ?></title>
<link rel="icon" href="assets/images/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="assets/images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/khmer-overrides.css">
<link rel="stylesheet" href="assets/css/theme.css">
<script>
(function(){try{var s=localStorage.getItem('reread-theme');var dark=false;if(s==='dark')dark=true;else if(s!=='light'&&window.matchMedia('(prefers-color-scheme: dark)').matches)dark=true;if(dark)document.documentElement.setAttribute('data-theme','dark');}catch(e){}})();
</script>
<script src="assets/js/main.js" defer></script>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="index.php">Reread</a>

    <div class="header-tools">
    <nav class="nav nav-main" aria-label="Primary">
      <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : ''; ?>"><i class="fas fa-house"></i><span><?= htmlspecialchars(t('nav.home')); ?></span></a>
      <a href="book_list.php" class="<?= in_array($currentPage, $bookPages, true) ? 'active' : ''; ?>"><i class="fas fa-book-open"></i><span><?= htmlspecialchars(t('nav.books')); ?></span></a>
      <a href="about.php" class="<?= $currentPage === 'about.php' ? 'active' : ''; ?>"><i class="fas fa-circle-info"></i><span><?= htmlspecialchars(t('nav.about')); ?></span></a>
      <?php if (isset($_SESSION['user'])): ?>
        <a href="post_book.php" class="<?= $currentPage === 'post_book.php' ? 'active' : ''; ?>"><i class="fas fa-plus"></i><span><?= htmlspecialchars(t('nav.sell')); ?></span></a>
        <a href="favorite.php" class="<?= $currentPage === 'favorite.php' ? 'active' : ''; ?>" aria-label="<?= htmlspecialchars(t('nav.aria_favorites')); ?>"><i class="fas fa-heart"></i><span><?= htmlspecialchars(t('nav.favorites')); ?></span></a>
        <a href="profile.php" class="nav-profile <?= $currentPage === 'profile.php' ? 'active' : ''; ?>" aria-label="<?= htmlspecialchars(t('nav.aria_profile')); ?>">
          <?php if ($profilePicPath !== ''): ?>
            <img src="<?= $profilePicPath; ?>" alt="">
          <?php else: ?>
            <i class="fas fa-user"></i>
          <?php endif; ?>
          <span><?= htmlspecialchars(t('nav.profile')); ?></span>
        </a>
        <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
          <a href="admin/index.php"><i class="fas fa-gauge-high"></i><span><?= htmlspecialchars(t('nav.admin')); ?></span></a>
        <?php endif; ?>
        <a href="logout.php"><i class="fas fa-arrow-right-from-bracket"></i><span><?= htmlspecialchars(t('nav.logout')); ?></span></a>
      <?php else: ?>
        <a href="login.php"><i class="fas fa-right-to-bracket"></i><span><?= htmlspecialchars(t('nav.login')); ?></span></a>
        <a href="signup.php" class="nav-cta"><i class="fas fa-user-plus"></i><span><?= htmlspecialchars(t('nav.signup')); ?></span></a>
      <?php endif; ?>
    </nav>

    <div class="lang-switch" role="navigation" aria-label="<?= htmlspecialchars(t('nav.lang_label')); ?>">
      <a href="set_language.php?lang=en&return=<?= $__returnEnc; ?>" class="<?= $__lang === 'en' ? 'is-active' : ''; ?>" hreflang="en">EN</a>
      <span class="lang-sep" aria-hidden="true">|</span>
      <a href="set_language.php?lang=km&return=<?= $__returnEnc; ?>" class="<?= $__lang === 'km' ? 'is-active' : ''; ?>" hreflang="km"><?= htmlspecialchars(t('nav.lang_km')); ?></a>
    </div>

    <div class="theme-switch" role="group" aria-label="Theme">
      <button type="button" class="theme-toggle-btn" data-set-theme="light" title="<?= htmlspecialchars(t('nav.theme_to_light')); ?>">
        <i class="fas fa-sun" aria-hidden="true"></i><span class="visually-hidden"><?= htmlspecialchars(t('nav.theme_light')); ?></span>
      </button>
      <button type="button" class="theme-toggle-btn" data-set-theme="dark" title="<?= htmlspecialchars(t('nav.theme_to_dark')); ?>">
        <i class="fas fa-moon" aria-hidden="true"></i><span class="visually-hidden"><?= htmlspecialchars(t('nav.theme_dark')); ?></span>
      </button>
    </div>
    </div>
  </div>
</header>

<?php require_once __DIR__ . '/chat_widget.php'; ?>

<main>
