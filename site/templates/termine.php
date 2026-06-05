<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<?php
$terminePage = page('termine');
$all = $terminePage ? $terminePage->children()->published()->sortBy('startdatum', 'desc') : [];

$termineColor = $terminePage && !$terminePage->color()->isEmpty()
    ? (string)$terminePage->color()
    : '#dce0e3';

$reisenPage = page('reisen');
?>

<main class="termine-template">
    <aside class="termine-sidebar sidebar-small">
        <div class="termine-page-info page-info"><?= t('ui.dates') ?></div>
        <button class="menu-button-js termine-pill termine-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="termine-pill termine-pill--dark termine-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <section class="termine-content">
        <?php foreach ($all as $termin): ?>
            <?php
                $linkedAusstellung = $termin->ausstellung()->toPages()->first();
                $linkedReise = $termin->reise()->toPages()->first();
                $hoverColor = '';
                $linkUrl = '';
                $ctaLabel = '';

                if ($linkedAusstellung) {
                    $linkUrl = $linkedAusstellung->url();
                    $hoverColor = !$linkedAusstellung->color()->isEmpty()
                        ? (string)$linkedAusstellung->color()
                        : '';
                    $ctaLabel = t('ui.to_exhibition');
                } elseif ($linkedReise && $reisenPage) {
                    $linkUrl = $linkedReise->url();
                    $cat = $linkedReise->category()->value();
                    $hoverColor = $cat === 'atelierbesuch'
                        ? (string)$reisenPage->atelierbesuchcolor()
                        : (string)$reisenPage->kunstreisecolor();
                    $ctaLabel = t('ui.to_travel');
                }

                $dateStr = $termin->startdatum()->toDate('d.m.Y');
                $timeStr = !$termin->eventTime()->isEmpty() ? $termin->eventTime()->toDate('H:i') : '';
                $hasLink = !empty($linkUrl);
                $inlineStyle = '--color: ' . esc($termineColor, 'attr') . ';';
                if ($hoverColor) {
                    $inlineStyle .= ' --hovercolor: ' . esc($hoverColor, 'attr') . ';';
                }
            ?>
            <?php if ($hasLink): ?>
            <a href="<?= esc($linkUrl) ?>" class="termin-card" style="<?= $inlineStyle ?>">
            <?php else: ?>
            <div class="termin-card" style="<?= $inlineStyle ?>">
            <?php endif ?>
                <div class="termin-card__header">
                    <span class="termin-card__category"><?= $termin->eventCategory()->html() ?></span>
                    <?php if ($dateStr): ?>
                    <span class="termin-card__date"><?= esc($dateStr) ?><?php if ($timeStr): ?>, <?= esc($timeStr) ?> Uhr<?php endif ?></span>
                    <?php endif ?>
                </div>
                <?php if ($termin->bodytext()->isNotEmpty()): ?>
                <div class="termin-card__bodytext"><?= $termin->bodytext()->kt() ?></div>
                <?php endif ?>
                <?php if ($hasLink): ?>
                <div class="termin-card__cta"><?= esc($ctaLabel) ?></div>
                <?php endif ?>
            <?php if ($hasLink): ?></a><?php else: ?></div><?php endif ?>
        <?php endforeach ?>
    </section>
</main>

<?php snippet('foot') ?>
