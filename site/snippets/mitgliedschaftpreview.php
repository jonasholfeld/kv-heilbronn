<?php $category = t('ui.kunstverein'); ?>
<div data-category="<?= $category ?>" class="scroll-top-element infobox-wrapper infobox-wrapper--mitgliedschaft home-item" style="--color: <?= site()->mitgliedschaftColor()->esc() ?>;">
  <header class="content-wrapper">
    <div class="infobox-info">
        <?php if (site()->mitgliedschaftTextTitle()->isNotEmpty()): ?>
          <p class="infobox__title"><?= site()->mitgliedschaftTextTitle()->esc() ?></p>
        <?php endif ?>
        <?php if (site()->mitgliedschaftText()->isNotEmpty()): ?>
          <div class="infobox__text"><?= site()->mitgliedschaftText()->value() ?></div>
        <?php endif ?>
      </div>
      <div class="infobox-wrapper__label-wrapper">
        <span class="section-label category-label"><?= $category ?></span>
        <span class="section-label"><a href="<?= page('kunstverein')->url() ?>#mitgliedschaft"><?= t('ui.membership') ?></a></span>
      </div>
  </header>  
</div>
