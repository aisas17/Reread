<?php
require_once 'includes/header.php';
?>

<section class="about-hero">
    <div class="container about-hero-inner">
        <p class="eyebrow"><?= htmlspecialchars(t('about.hero_eyebrow')); ?></p>
        <h1><?= htmlspecialchars(t('about.hero_title')); ?></h1>
        <p>
            <?= htmlspecialchars(t('about.hero_p')); ?>
        </p>
        <a href="book_list.php" class="about-cta"><?= htmlspecialchars(t('about.hero_cta')); ?></a>
    </div>
</section>

<section class="about-mission">
    <div class="container about-split">
        <div class="about-copy">
            <p class="eyebrow"><?= htmlspecialchars(t('about.mission_eyebrow')); ?></p>
            <h2><?= htmlspecialchars(t('about.mission_title')); ?></h2>
            <p>
                <?= htmlspecialchars(t('about.mission_p1')); ?>
            </p>
            <p>
                <?= htmlspecialchars(t('about.mission_p2')); ?>
            </p>
        </div>

        <div class="about-image-card">
            <img src="assets/images/history.png" alt="">
        </div>
    </div>
</section>

<section class="about-values">
    <div class="container">
        <div class="about-section-head">
            <h2><?= htmlspecialchars(t('about.values_title')); ?></h2>
        </div>

        <div class="values-grid">
            <article class="value-card">
                <span><i class="fas fa-leaf"></i></span>
                <h3><?= htmlspecialchars(t('about.value1_title')); ?></h3>
                <p><?= htmlspecialchars(t('about.value1_p')); ?></p>
            </article>

            <article class="value-card">
                <span><i class="fas fa-users"></i></span>
                <h3><?= htmlspecialchars(t('about.value2_title')); ?></h3>
                <p><?= htmlspecialchars(t('about.value2_p')); ?></p>
            </article>

            <article class="value-card">
                <span><i class="far fa-circle-check"></i></span>
                <h3><?= htmlspecialchars(t('about.value3_title')); ?></h3>
                <p><?= htmlspecialchars(t('about.value3_p')); ?></p>
            </article>
        </div>
    </div>
</section>

<section class="about-impact">
    <div class="container">
        <div class="about-section-head impact-head">
            <h2><?= htmlspecialchars(t('about.impact_title')); ?></h2>
            <p><?= htmlspecialchars(t('about.impact_sub')); ?></p>
        </div>

        <div class="impact-grid">
            <div>
                <strong>50K+</strong>
                <span><?= htmlspecialchars(t('about.stat1_label')); ?></span>
            </div>
            <div>
                <strong>12.5t</strong>
                <span><?= htmlspecialchars(t('about.stat2_label')); ?></span>
            </div>
            <div>
                <strong>15K</strong>
                <span><?= htmlspecialchars(t('about.stat3_label')); ?></span>
            </div>
            <div>
                <strong>200+</strong>
                <span><?= htmlspecialchars(t('about.stat4_label')); ?></span>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
