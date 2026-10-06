<?php $category = t('ui.travels'); ?>
<div data-category="<?= $category ?>" class="scroll-top-element infobox-wrapper infobox-wrapper--reisen home-item" style="--color: <?= $reisenPage->color()->esc() ?>;">
  <header class="content-wrapper">  
    <a href="<?= $reisenPage->url() ?>" class="infobox-info">
        <p class="infobox__title"><?= site()->reisenTitle()->esc() ?></p>
        <?php if (site()->reisenText()->isNotEmpty()): ?>
          <div class="infobox__text"><?= preg_replace('/<\/?a\b[^>]*>/i', '', site()->reisenText()->value()) ?></div>
        <?php endif ?>
        <span class="infobox__link"><?= t('ui.all_travels_overview') ?></span>
      </a>
      <div class="label-wrapper infobox-wrapper__label-wrapper">
          <span class="section-label"><a href="<?= $reisenPage->url() ?>"><?= t('ui.art_trips') ?></a></span>
          <span class="section-label"><a href="<?= $reisenPage->url() ?>"><?= t('ui.studio_visits') ?></a></span>
      </div>
  </header>
</div>
