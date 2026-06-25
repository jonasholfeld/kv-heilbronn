<?php
$filterYear = trim((string)$item->jahr()->value());
if ($filterYear === '') {
  $endTs = $item->enddatum()->toDate();
  $filterYear = $endTs ? date('Y', $endTs) : '';
}

$rowImages = $item->galerie()->toFiles()->limit(3);
$eroTs = $item->eroffnungsdatum()->toDate();
$whiteFontClass = $item->whiteFont()->toBool() ? 'white-font' : '';
?>
<div style="--hoverColor: <?= $item->color()->isEmpty() ? '#cfd2d6' : $item->color() ?>" class="ausstellungen-row <?= $whiteFontClass ?>" data-ausstellung-row data-year="<?= esc($filterYear, 'attr') ?>" data-artist="<?= esc(trim((string)$item->kuenstler()->value()), 'attr') ?>">
  <button class="ausstellungen-row-header" type="button" aria-expanded="false">
    <span><?= $item->kuenstler()->or('Künstler*in')->html() ?></span>
    <span><?= $item->title()->html() ?></span>
  </button>
  <div class="ausstellungen-row-body">
    <?php $startStr = $item->startdatum()->toDate('d.m.Y'); $endStr = $item->enddatum()->toDate('d.m.Y'); ?>
    <?php if ($startStr || $endStr): ?>
      <p class="ausstellungen-row-dates"><?= esc(($startStr ?: '') . ($startStr && $endStr ? ' – ' : '') . ($endStr ?: '')) ?></p>
    <?php endif ?>
    <?php if ($eroTs): ?>
      <?php
        $eroDay    = $isDE ? $days[date('w', $eroTs)]       : $daysEn[date('w', $eroTs)];
        $eroMonth  = $isDE ? $months[date('n', $eroTs) - 1] : $monthsEn[date('n', $eroTs) - 1];
        $eroDayNum = date('j', $eroTs);
        $eroHour   = date('H', $eroTs);
        $eroStr    = $isDE
          ? "{$openingLabel}: {$eroDay}, {$eroDayNum}. {$eroMonth}, {$eroHour} Uhr"
          : "{$openingLabel}: {$eroDay}, {$eroMonth} {$eroDayNum}, {$eroHour}:00";
      ?>
      <p class="ausstellungen-row-opening"><?= esc($eroStr) ?></p>
    <?php endif ?>
    <?php if ($rowImages->count() > 0): ?>
      <div class="ausstellungen-row-images">
        <?php foreach ($rowImages as $img): ?>
          <img src="<?= $img->resize(500)->url() ?>" alt="<?= $img->alt()->or($item->kuenstler())->esc() ?>">
        <?php endforeach ?>
      </div>
    <?php endif ?>
    <a class="ausstellungen-row-link" href="<?= $item->url() ?>"><?= esc($moreInfoLabel) ?></a>
  </div>
</div>
