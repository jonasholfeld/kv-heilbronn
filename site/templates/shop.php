<?php snippet('head') ?>
<div class="shop-layout">
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="shop-overview">
    <aside class="shop-sidebar sidebar-small">
        <div class="shop-page-info page-info"><?= t('ui.shop') ?></div>
        <button class="menu-button-js bubble bubble-inverted" type="button"><?= t('ui.menu') ?></button>
        <a class="shop-pill shop-pill--dark shop-home-link home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>
    <?php $editionen = $page->children()->filterBy('template', 'edition') ?>
    <?php $kataloge  = $page->children()->filterBy('template', 'katalog') ?>

    <section class="shop-section">
        <h2 class="shop-section-title"><?= t('ui.editions') ?></h2>
        <?php foreach($editionen as $item): ?>
            <?php snippet('shop-card', compact('item', 'moreInfoLabel')) ?>
        <?php endforeach ?>
    </section>

    <section class="shop-section">
        <h2 class="shop-section-title"><?= t('ui.catalogues') ?></h2>
        <?php foreach($kataloge as $item): ?>
            <?php snippet('shop-card', compact('item', 'moreInfoLabel')) ?>
        <?php endforeach ?>
    </section>
</main>
</div>