<!doctype html>
<html lang="<?= kirby()->languageCode() ?? 'de' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $page->isHomePage() ? $site->title()->or('Kunstverein Heilbronn')->esc() : $page->title()->esc() . ' | ' . $site->title()->or('Kunstverein Heilbronn')->esc() ?></title>
  <?php snippet('vite', ['entry' => 'src/js/main.js']) ?>
</head>
<?php
$textModeClass = '';
if($page->template() == 'ausstellung' && $page->galerie()->toFiles()->count() < 4) {
    $textModeClass = 'text-mode';
}
$whiteFontClass = '';
if($page->template() == 'ausstellung' && $page->whiteFont()->toBool()) {
    $whiteFontClass = 'white-font';
}
?>
<body class="pre-init <?= $textModeClass ?> <?= $whiteFontClass ?> <?= $page->isHomePage() ? 'home-body-class' :  $page->template() ?>" style="--colorPage: <?= $page->color()->isEmpty() ? '#dce0e3' : $page->color() ?>">
