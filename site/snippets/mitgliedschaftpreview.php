<?php $category = t('ui.kunstverein'); ?>
<div data-category="<?= $category ?>" class="scroll-top-element infobox-wrapper infobox-wrapper--mitgliedschaft home-item" style="--color: <?= site()->mitgliedschaftColor()->esc() ?>;">
  <header class="content-wrapper">
    <a href="<?= page('kunstverein')->url() ?>#mitgliedschaft" class="infobox-info">
        <?php if (site()->mitgliedschaftTextTitle()->isNotEmpty()): ?>
          <p class="infobox__title"><?= site()->mitgliedschaftTextTitle()->esc() ?></p>
        <?php endif ?>
        <?php if (site()->mitgliedschaftText()->isNotEmpty()): ?>
          <div class="infobox__text"><?= preg_replace('/<\/?a\b[^>]*>/i', '', site()->mitgliedschaftText()->value()) ?></div>
        <?php endif ?>
        <span class="infobox__link"><?= t('ui.more_information') ?></span>
      </a>
      <div class="label-wrapper infobox-wrapper__label-wrapper">
        <span class="section-label category-label"><?= $category ?></span>
        <span class="section-label"><a href="<?= page('kunstverein')->url() ?>#mitgliedschaft"><?= t('ui.membership') ?></a></span>
      </div>
  </header>  
</div>
