<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="besuch-template">
    <aside class="besuch-sidebar sidebar-small">
        <div class="besuch-page-info page-info"><?= t('ui.visit') ?></div>
        <button class="menu-button-js besuch-pill besuch-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="besuch-pill besuch-pill--dark besuch-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="besuch-content">
        <div class="besuch-panel">
            <h2 class="besuch-section-title"><?= t('ui.contact') ?></h2>
            <div class="besuch-section-body">
                <?= $page->kontakt() ?>
            </div>
        </div>

        <div class="besuch-column">
            <div class="besuch-panel besuch-panel--shrink">
                <h2 class="besuch-section-title"><?= t('ui.opening_hours') ?></h2>
                <div class="besuch-section-body">
                    <?= $page->offnungszeiten() ?>
                </div>
            </div>
            <div class="besuch-panel">
                <h2 class="besuch-section-title"><?= t('ui.directions') ?></h2>
                <div class="besuch-section-body">
                    <?= $page->anfahrt() ?>
                </div>
            </div>
        </div>

        <div class="besuch-panel">
            <h2 class="besuch-section-title"><?= t('ui.contacts') ?></h2>
            <div class="besuch-section-body">
                <?= $page->ansprechpartner() ?>
            </div>
        </div>
    </div>
</main>
