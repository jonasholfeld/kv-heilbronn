<?php snippet('head') ?>

<main class="page">
  <div class="page__inner">
    <p class="eyebrow">Fehler</p>
    <h1><?= $page->title()->esc() ?></h1>
    <?php if ($page->text()->isNotEmpty()): ?>
      <div class="copy">
        <?= $page->text()->kirbytext() ?>
      </div>
    <?php endif ?>
  </div>
</main>
