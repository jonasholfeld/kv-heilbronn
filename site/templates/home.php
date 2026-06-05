<?php snippet('head') ?>
<?php
$ausstellungen = page('ausstellungen');
$now = date('Y-m-d');
$currentExhibition = null;
$futureEvents = [];
$archiveEvents = [];

if ($ausstellungen) {
  foreach ($ausstellungen->children() as $ausstellung) {
    $start = $ausstellung->startdatum()->toDate('Y-m-d');
    $end   = $ausstellung->enddatum()->toDate('Y-m-d');
    if ($start && $end && $start <= $now && $now <= $end) {
      $currentExhibition = $ausstellung;
    } elseif ($start && $start > $now) {
      $futureEvents[] = $ausstellung;
    } elseif ($end && $end < $now && $ausstellung->canBeDisplayedOnHomepage()->toBool() === true) {
      $archiveEvents[] = $ausstellung;
    }
  }
}

$archiveFallbackExhibition = null;
if (($currentExhibition === null || empty($futureEvents)) && !empty($archiveEvents)) {
  $archiveFallbackExhibition = $archiveEvents[array_rand($archiveEvents)];
}

$initialHeaderColor = null;
if (!empty($futureEvents)) {
  $initialHeaderColor = $futureEvents[0]->color()->value();
} elseif ($currentExhibition) {
  $initialHeaderColor = $currentExhibition->color()->value();
} elseif ($archiveFallbackExhibition) {
  $initialHeaderColor = $archiveFallbackExhibition->color()->value();
}
?>
<?php snippet('navi', ['includeSiteMenu' => true, 'initialHeaderColor' => $initialHeaderColor]) ?>
<?php snippet('home-overlay') ?>

<?php
$terminePage = page('termine');
$termineColor = $terminePage ? $terminePage->color()->esc() : '';
$termineItems = $terminePage
  ? $terminePage->children()->filter(function ($child) use ($now) {
      $start = $child->startdatum()->toDate('Y-m-d');
      return $child->showOnHomepage()->toBool() === true && $start && $start >= $now;
    })->sortBy('startdatum', 'asc')
  : [];
?>

<main class="home">
  <?php if ($currentExhibition): ?>
    <?= snippet('exhibitionpreview', ['exhibition' => $currentExhibition, 'exhiClass' => 'current', 'exhiLabel' => t('ui.current')]) ?>
  <?php endif ?>

  <?php foreach ($futureEvents as $futureEvent): ?>
    <?= snippet('exhibitionpreview', ['exhibition' => $futureEvent, 'exhiClass' => 'future', 'exhiLabel' => t('ui.preview')]) ?>
  <?php endforeach ?>

  <?php if ($archiveFallbackExhibition): ?>
    <?= snippet('exhibitionpreview', ['exhibition' => $archiveFallbackExhibition, 'exhiClass' => 'archive', 'exhiLabel' => t('ui.archive')]) ?>
  <?php endif ?>

  <?php if (site()->mitgliedschaftTextTitle()->isNotEmpty() || site()->mitgliedschaftText()->isNotEmpty()): ?>
    <?= snippet('mitgliedschaftpreview') ?>
  <?php endif ?>

  <?php if (!(count($termineItems) === 0)): ?>
    <?= snippet('terminepreview', ['termineItems' => $termineItems, 'termineColor' => $termineColor]) ?>
  <?php endif ?>

  <?php
  $reisenPage = page('reisen');
  if ($reisenPage): ?>
    <?= snippet('reisenpreview', ['reisenPage' => $reisenPage]) ?>
  <?php endif ?>

  <?php
  $editionId = site()->editionItem()->value();
  $edition = $editionId ? page($editionId) : null;
  if ($edition): ?>
    <?= snippet('shopitempreview', ['item' => $edition, 'itemLabel' => 'Edition']) ?>
  <?php endif ?>

  <?php
  $katalogId = site()->katalogItem()->value();
  $katalog = $katalogId ? page($katalogId) : null;
  if ($katalog): ?>
    <?= snippet('shopitempreview', ['item' => $katalog, 'itemLabel' => 'Katalog']) ?>
  <?php endif ?>
</main>

<?php snippet('foot') ?>
