<?php $category = t('ui.dates'); ?>
<div data-category="<?= $category ?>" class="scroll-top-element termine-section home-item" style="--color: <?= $termineColor ?>;">
  <header class="content-wrapper">
    <?php foreach ($termineItems as $termin): ?>
      <?php $linkedExhibition = $termin->ausstellung()->toPages()->first() ?>
      <?php if ($linkedExhibition): ?>
        <a href="<?= $linkedExhibition->url() ?>" class="termin-item__wrapper-link" style="--hovercolor: <?= $linkedExhibition->color() ?>;">
      <?php endif ?>
      <?php $linkedReise = $termin->reise()->toPages()->first() ?>
      <?php if ($linkedReise): 
        $reiseColor = $linkedReise->category() == 'kunstreise' ? page('reisen')->kunstreisecolor() : page('reisen')->atelierbesuchcolor();
      ?>
        <a href="<?= $linkedReise->url() ?>" class="termin-item__wrapper-link" style="--hovercolor: <?= $reiseColor ?>;">
      <?php endif ?>
      <?php $tags = $termin->kalender()->split() ?>
      <div class="termin-item">
        <?php if (!empty($tags)): ?>
          <p class="termin__label"><?= esc(strtoupper(implode(', ', array_map('trim', $tags)))) ?></p>
        <?php endif ?>
        <div class="termin__body">
          <div class="termin__content">
            <div class="termin__top-info-row">
              <p class="termin__event-category"><?= $termin->eventCategory()->esc() ?></p>
              <p class="termin__date"><?= $termin->startdatum()->toDate('d.m.Y') ?>, <?= $termin->eventTime()->toDate('H:i') ?> Uhr</p>
            </div>
            <div class="termin__bodytext"><?= $termin->bodytext()->tk() ?></div>
            <?php if ($linkedExhibition): ?>
              <div class="termin__cta-link"><?= t('ui.to_exhibition') ?></div>
            <?php endif ?>
            <?php if ($linkedReise): ?>
              <div class="termin__cta-link"><?= t('ui.to_travel') ?></div>
            <?php endif ?>
          </div>
        </div>
      </div>
      <?php if ($linkedExhibition || $linkedReise): ?>
        </a>
      <?php endif ?>
    <?php endforeach ?>
    <div class="termine-section__label-wrapper">
      <span class="section-label category-label"><?= $category ?></span>
    </div>
  </header>
</div>
