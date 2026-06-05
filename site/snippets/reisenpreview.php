<?php $category = t('ui.travels'); ?>
<div data-category="<?= $category ?>" class="scroll-top-element infobox-wrapper infobox-wrapper--reisen home-item" style="--color: <?= $reisenPage->color()->esc() ?>;">
  <header class="content-wrapper">  
    <div class="infobox-info">
        <p class="infobox__title"><?= site()->reisenTitle()->esc() ?></p>
        <?php if (site()->reisenText()->isNotEmpty()): ?>
          <div class="infobox__text"><?= site()->reisenText()->value() ?></div>
        <?php endif ?>
        <a href="<?= $reisenPage->url() ?>" class="infobox__link"><?= t('ui.all_travels_overview') ?></a>
      </div>
      <div class="infobox-wrapper__label-wrapper">
          <span class="section-label"><a href="<?= $reisenPage->url() ?>"><?= t('ui.art_trips') ?></a></span>
          <span class="section-label"><a href="<?= $reisenPage->url() ?>"><?= t('ui.studio_visits') ?></a></span>
      </div>
  </header>
</div>
