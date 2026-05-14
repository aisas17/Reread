<?php
// includes/footer.php
?>
</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <a class="logo" href="index.php">Reread</a>
      <p><?= htmlspecialchars(t('footer.tagline')); ?></p>
    </div>

    <div>
      <h3><?= htmlspecialchars(t('footer.shop')); ?></h3>
      <a href="book_list.php"><?= htmlspecialchars(t('footer.rare_finds')); ?></a>
      <a href="book_list.php"><?= htmlspecialchars(t('footer.best_sellers')); ?></a>
      <a href="book_list.php"><?= htmlspecialchars(t('footer.new_arrivals')); ?></a>
    </div>

    <div>
      <h3><?= htmlspecialchars(t('footer.resources')); ?></h3>
      <a href="about.php"><?= htmlspecialchars(t('footer.about_us')); ?></a>
      <a href="book_list.php"><?= htmlspecialchars(t('footer.browse_books')); ?></a>
      <a href="post_book.php"><?= htmlspecialchars(t('footer.sell_book')); ?></a>
    </div>

    <div>
      <h3><?= htmlspecialchars(t('footer.connect')); ?></h3>
      <div class="footer-social">
        <a href="book_list.php" aria-label="<?= htmlspecialchars(t('footer.share')); ?>"><i class="fas fa-share-nodes"></i></a>
        <a href="mailto:hello@reread.local" aria-label="<?= htmlspecialchars(t('footer.email')); ?>"><i class="far fa-envelope"></i></a>
      </div>
    </div>
  </div>

  <div class="container footer-bottom">
    <span>&copy; <?php echo date('Y'); ?> <?= htmlspecialchars(t('footer.copyright')); ?></span>
    <span><a href="index.php"><?= htmlspecialchars(t('footer.privacy')); ?></a><a href="index.php"><?= htmlspecialchars(t('footer.terms')); ?></a></span>
  </div>
</footer>
</body>
</html>
