<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<?php
$now = date('Y-m-d');
$ausstellungenPage = page('ausstellungen');
$all = $ausstellungenPage ? $ausstellungenPage->children(): pages();
$getEffectiveEndDate = function ($item, $format = 'Y-m-d') {
  return $item->enddatum()->toDate($format) ?: $item->startdatum()->toDate($format);
};

$vorschau = $all->filter(fn ($item) => ($item->startdatum()->toDate('Y-m-d') ?? '') > $now)
  ->sortBy('startdatum', 'asc');

$aktuell = $all->filter(function ($item) use ($now, $getEffectiveEndDate) {
  $end = $getEffectiveEndDate($item);
  return $end && $end >= $now;
})->sort(function ($a, $b) use ($getEffectiveEndDate) {
  return strcmp($getEffectiveEndDate($a), $getEffectiveEndDate($b));
});

$archiv = $all->filter(function ($item) use ($now, $getEffectiveEndDate) {
  $end = $getEffectiveEndDate($item);
  return $end && $end < $now;
});

$years = $archiv->group(function ($item) use ($getEffectiveEndDate) {
  $endTs = $getEffectiveEndDate($item, null);
  return $endTs ? date('Y', $endTs) : t('ui.without_year');
});

$yearKeys = array_keys($years->toArray());
usort($yearKeys, fn ($a, $b) => (int)$b <=> (int)$a);

$allYears = [];
$allArtists = [];

foreach ($all as $item) {
  $year = trim((string)$item->jahr()->value());
  $artist = trim((string)$item->kuenstler()->value());

  if ($year !== '') {
    $allYears[$year] = true;
  }

  if ($artist !== '') {
    $allArtists[$artist] = true;
  }
}

$allYears = array_keys($allYears);
usort($allYears, fn ($a, $b) => (int)$b <=> (int)$a);

$allArtists = array_keys($allArtists);
sort($allArtists, SORT_NATURAL | SORT_FLAG_CASE);

?>

<main class="ausstellungen-template">
  <aside class="ausstellungen-sidebar sidebar-small">
    <div class="ausstellungen-page-info page-info"><?= t('ui.exhibitions') ?></div>
    <button class="menu-button-js ausstellungen-pill ausstellungen-pill--dark" type="button"><?= t('ui.menu') ?></button>
    <button class="ausstellungen-filter" type="button" aria-expanded="false"><?= t('ui.filter') ?></button>

    <div class="ausstellungen-filter-panels" hidden>
      <section class="ausstellungen-filter-group">
        <button class="ausstellungen-filter-group-title" type="button" data-filter-toggle="year" aria-expanded="false"><?= t('ui.year') ?></button>
        <div class="ausstellungen-filter-options" data-filter-options="year" hidden>
          <button class="ausstellungen-filter-option is-selected" type="button" data-filter-all="year"><?= t('ui.all_years') ?></button>
          <?php foreach ($allYears as $year): ?>
            <button class="ausstellungen-filter-option" type="button" data-filter-value="year" data-value="<?= esc($year, 'attr') ?>"><?= esc($year) ?></button>
          <?php endforeach ?>
        </div>
      </section>

      <section class="ausstellungen-filter-group">
        <button class="ausstellungen-filter-group-title" type="button" data-filter-toggle="artist" aria-expanded="false"><?= t('ui.artists') ?></button>
        <div class="ausstellungen-filter-options" data-filter-options="artist" hidden>
          <button class="ausstellungen-filter-option is-selected" type="button" data-filter-all="artist"><?= t('ui.all_artists') ?></button>
          <?php foreach ($allArtists as $artist): ?>
            <button class="ausstellungen-filter-option" type="button" data-filter-value="artist" data-value="<?= esc($artist, 'attr') ?>"><?= esc($artist) ?></button>
          <?php endforeach ?>
        </div>
      </section>
    </div>

    <a class="ausstellungen-pill ausstellungen-pill--dark ausstellungen-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
  </aside>

  <section class="ausstellungen-content" data-ausstellungen-list>
    <div class="scroll-container">
      <h2 class="ausstellungen-section-title"><?= t('ui.preview') ?></h2>
      <?php foreach ($vorschau as $item): ?>
        <?php snippet('ausstellungen-row', compact('item', 'isDE', 'days', 'daysEn', 'months', 'monthsEn', 'openingLabel', 'moreInfoLabel')) ?>
      <?php endforeach ?>

      <h2 class="ausstellungen-section-title"><?= t('ui.current') ?></h2>
      <?php foreach ($aktuell as $item): ?>
        <?php snippet('ausstellungen-row', compact('item', 'isDE', 'days', 'daysEn', 'months', 'monthsEn', 'openingLabel', 'moreInfoLabel')) ?>
      <?php endforeach ?>

      <?php foreach ($yearKeys as $year): ?>
        <?php $items = $years->get($year); ?>
        <h2 class="ausstellungen-section-title" data-ausstellungen-heading><?= esc($year) ?></h2>
        <?php foreach ($items->sort(function ($a, $b) use ($getEffectiveEndDate) {
          return strcmp($getEffectiveEndDate($b), $getEffectiveEndDate($a));
        }) as $item): ?>
          <?php snippet('ausstellungen-row', compact('item', 'isDE', 'days', 'daysEn', 'months', 'monthsEn', 'openingLabel', 'moreInfoLabel')) ?>
        <?php endforeach ?>
      <?php endforeach ?>
    </div>
  </section>
</main>
