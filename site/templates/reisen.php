<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<?php
$now = date('Y-m-d');
$reisenPage = page('reisen');
$all = $reisenPage ? $reisenPage->children()->published() : pages();

$kunstreiseColor = $reisenPage && !$reisenPage->kunstreisecolor()->isEmpty()
    ? (string)$reisenPage->kunstreisecolor()
    : '#cad9cc';
$atelierbesuchColor = $reisenPage && !$reisenPage->atelierbesuchcolor()->isEmpty()
    ? (string)$reisenPage->atelierbesuchcolor()
    : '#ff8c5a';

$vorschau = $all->filter(fn ($item) => ($item->reiseStart()->toDate('Y-m-d') ?? '') > $now)
    ->sortBy('reiseStart', 'asc');

$archiv = $all->filter(function ($item) use ($now) {
    $start = $item->reiseStart()->toDate('Y-m-d');
    return !$start || $start <= $now;
});

$years = $archiv->group(function ($item) {
    $ts = $item->reiseStart()->toDate();
    return $ts ? date('Y', $ts) : 'Ohne Jahr';
});

$yearKeys = array_keys($years->toArray());
usort($yearKeys, fn ($a, $b) => (int)$b <=> (int)$a);

function reiseCardColor(string $category, string $kunstreiseColor, string $atelierbesuchColor): string {
    return $category === 'atelierbesuch' ? $atelierbesuchColor : $kunstreiseColor;
}

function reiseCategoryLabel(string $category): string {
    return $category === 'atelierbesuch' ? t('ui.studio_visit') : t('ui.art_trip');
}
?>

<main class="reisen-template">
    <aside class="reisen-sidebar sidebar-small">
        <div class="reisen-page-info page-info"><?= t('ui.travels') ?></div>
        <button class="menu-button-js reisen-pill reisen-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="reisen-pill reisen-pill--dark reisen-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <section class="reisen-content">
        <h2 class="reisen-section-title"><?= t('ui.preview') ?></h2>
        <?php foreach ($vorschau as $item): ?>
            <?php
                $cat = $item->category()->value();
                $cardColor = reiseCardColor($cat, $kunstreiseColor, $atelierbesuchColor);
                $catLabel = reiseCategoryLabel($cat);
                $dateStr = $item->reiseStart()->toDate('d.m.Y');
            ?>
            <a href="<?= $item->url() ?>" class="reise-card" style="--reise-card-color: <?= esc($cardColor, 'attr') ?>">
                <div class="reise-card__header">
                    <span class="reise-card__title"><?= $item->title()->html() ?></span>
                    <span class="reise-card__category"><?= esc(strtoupper($catLabel)) ?></span>
                </div>
                <?php if ($dateStr): ?>
                <ul class="reise-card__dates">
                    <li><p><?= esc($dateStr) ?></p></li>
                </ul>
                <?php endif ?>
                <div class="reise-card__footer">
                    <span class="reise-card__mehr"><?= t('ui.more_information') ?></span>
                    <span class="reise-card__anmeldung"><?= t('ui.registration') ?></span>
                </div>
            </a>
        <?php endforeach ?>

        <?php foreach ($yearKeys as $year): ?>
            <?php $items = $years->get($year); ?>
            <h2 class="reisen-section-title"><?= esc($year) ?></h2>
            <?php foreach ($items->sortBy('reiseStart', 'desc') as $item): ?>
                <?php
                    $cat = $item->category()->value();
                    $cardColor = reiseCardColor($cat, $kunstreiseColor, $atelierbesuchColor);
                    $catLabel = reiseCategoryLabel($cat);
                    $dateStr = $item->reiseStart()->toDate('d.m.Y');
                ?>
                <a href="<?= $item->url() ?>" class="reise-card" style="--reise-card-color: <?= esc($cardColor, 'attr') ?>">
                    <div class="reise-card__header">
                        <span class="reise-card__title"><?= $item->title()->html() ?></span>
                        <span class="reise-card__category"><?= esc(strtoupper($catLabel)) ?></span>
                    </div>
                    <?php if ($dateStr): ?>
                    <ul class="reise-card__dates">
                        <li><p><?= esc($dateStr) ?></p></li>
                    </ul>
                    <?php endif ?>
                    <div class="reise-card__footer">
                        <span class="reise-card__mehr"><?= t('ui.more_information') ?></span>
                    </div>
                </a>
            <?php endforeach ?>
        <?php endforeach ?>
    </section>
</main>

<?php snippet('foot') ?>
