<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>
<div class="shop-layout">

<main class="shop-overview">
    <aside class="shop-sidebar sidebar-small">
        <div class="shop-page-info page-info"><?= t('ui.shop') ?></div>
        <button class="menu-button-js bubble bubble-inverted" type="button"><?= t('ui.menu') ?></button>
        <a class="shop-pill shop-pill--dark shop-home-link home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>
    <?php $editionen = $page->children()->filterBy('template', 'edition') ?>
    <?php $kataloge  = $page->children()->filterBy('template', 'katalog') ?>
    <?php
    // „Editionen / Jahresgaben“: on mobile the parts can go on lines of their own
    $tabLabel = fn (string $label) => implode('<span class="shop-tabs__slash"> / </span>', array_map(
        fn ($part) => '<span class="shop-tabs__part">' . esc($part) . '</span>',
        explode(' / ', $label)
    ));
    ?>

    <?php // Mobile only: first a choice between the sections, then tabs above the chosen one (src/js/shop.js) ?>
    <nav class="shop-tabs" aria-label="<?= t('ui.shop') ?>">
        <button class="shop-tabs__tab" type="button" data-shop-tab="editionen" aria-controls="editionen"><?= $tabLabel(t('ui.editions')) ?></button>
        <button class="shop-tabs__tab" type="button" data-shop-tab="kataloge" aria-controls="kataloge"><?= $tabLabel(t('ui.catalogues')) ?></button>
    </nav>

    <section class="shop-section" id="editionen">
        <h2 class="shop-section-title"><?= t('ui.editions') ?></h2>
        <?php foreach($editionen as $item): ?>
            <?php snippet('shop-card', compact('item', 'moreInfoLabel')) ?>
        <?php endforeach ?>
    </section>

    <section class="shop-section" id="kataloge">
        <h2 class="shop-section-title"><?= t('ui.catalogues') ?></h2>
        <?php foreach($kataloge as $item): ?>
            <?php snippet('shop-card', compact('item', 'moreInfoLabel')) ?>
        <?php endforeach ?>
    </section>
</main>
</div>
<?php snippet('page-end') ?>
