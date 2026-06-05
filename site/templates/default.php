<?php snippet('head') ?>

<main class="page">
  <div class="page__inner">
    <p class="eyebrow">Kirby 5 + Vite + SCSS</p>
    <h1><?= $page->title()->esc() ?></h1>
    <?php if ($page->text()->isNotEmpty()): ?>
      <div class="copy">
        <?= $page->text()->kirbytext() ?>
      </div>
    <?php endif ?>

    <button class="demo-button" type="button" data-demo-toggle>
      Interaktion testen
    </button>
  </div>
</main>

<?php snippet('foot') ?>
