<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<?php
$query   = trim((string)get('q', ''));
$results = $query !== '' ? KvSearch::find($query) : [];

$reisenPage = page('reisen');
$colors = [
    'termin'        => page('termine')?->color()->or('#dce0e3')->value(),
    'kunstreise'    => $reisenPage?->kunstreisecolor()->or('#cad9cc')->value(),
    'atelierbesuch' => $reisenPage?->atelierbesuchcolor()->or('#ff8c5a')->value(),
];
?>

<main class="suche-template">
    <aside class="suche-sidebar sidebar-small">
        <div class="suche-page-info page-info"><a href="<?= $page->url() ?>"><?= t('ui.search') ?></a></div>
        <button class="menu-button-js suche-pill suche-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="suche-pill suche-pill--dark suche-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <section class="suche-content">
        <form class="suche-form" action="<?= $page->url() ?>" method="get" role="search">
            <div class="suche-form__field">
                <?php // The hint stands in for the placeholder, which can't wrap (mobile) ?>
                <input class="suche-form__input" type="search" name="q" value="<?= esc($query, 'attr') ?>" placeholder=" " aria-label="<?= esc(t('ui.search_placeholder'), 'attr') ?>" autocomplete="off">
                <span class="suche-form__hint" aria-hidden="true"><?= esc(t('ui.search_placeholder')) ?></span>
            </div>
        </form>

        <div class="suche-results" id="suche-results">
            <div class="scroll-container">
                <?php foreach ($results as $index => $result): ?>
                    <?php snippet('search-card', [
                        'item'         => $result['page'],
                        'index'        => $index,
                        'colors'       => $colors,
                        'isDE'         => $isDE,
                        'days'         => $days,
                        'daysEn'       => $daysEn,
                        'months'       => $months,
                        'monthsEn'     => $monthsEn,
                        'openingLabel' => $openingLabel,
                    ]) ?>
                <?php endforeach ?>
                <?php if ($query !== '' && $results === []): ?>
                    <p class="suche-empty"><?= esc(tt('ui.search_no_results', ['query' => $query])) ?></p>
                <?php endif ?>
            </div>
        </div>
    </section>
</main>
<?php snippet('page-end') ?>
