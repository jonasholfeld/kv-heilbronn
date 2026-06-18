<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="besuch-template">
    <aside class="besuch-sidebar sidebar-small">
        <div class="besuch-page-info page-info"><?= t('ui.imprint') ?></div>
        <button class="menu-button-js besuch-pill besuch-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="besuch-pill besuch-pill--dark besuch-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="besuch-content">
        <div class="besuch-panel">
            <h2 class="besuch-section-title"><?= t('ui.imprint') ?></h2>
            <div class="besuch-section-body">
                <?= $page->impressum() ?>
            </div>
        </div>

        <div class="besuch-panel">
            <h2 class="besuch-section-title"><?= t('ui.privacy') ?></h2>
            <div class="besuch-section-body">
                <?= $page->datenschutz() ?>
            </div>
        </div>
    </div>
</main>
