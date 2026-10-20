<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="besuch-template">
    <aside class="besuch-sidebar sidebar-small">
        <div class="besuch-page-info page-info"><?= t('ui.imprint') ?></div>
        <button class="menu-button-js besuch-pill besuch-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="besuch-pill besuch-pill--dark besuch-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="besuch-content">
        <?php $fundingLogos = $page->foerderunglogos()->toFiles() ?>
        <div class="besuch-column">
            <div class="besuch-panel">
                <h2 class="besuch-section-title"><?= t('ui.imprint') ?></h2>
                <div class="besuch-section-body">
                    <?= $page->impressum() ?>
                </div>
            </div>

            <?php // Funding note with the city and state logos, set off below ?>
            <?php if ($page->foerderungtext()->isNotEmpty() || $fundingLogos->isNotEmpty()): ?>
            <div class="besuch-panel besuch-panel--shrink impressum-funding">
                <?php if ($page->foerderungtext()->isNotEmpty()): ?>
                <p class="impressum-funding__text"><?= $page->foerderungtext()->html() ?></p>
                <?php endif ?>
                <?php if ($fundingLogos->isNotEmpty()): ?>
                <div class="impressum-funding__logos">
                    <?php foreach ($fundingLogos as $logo): ?>
                        <?php if ($logo->linkurl()->isNotEmpty()): ?>
                            <a href="<?= $logo->linkurl() ?>" target="_blank" rel="noopener noreferrer">
                                <?php snippet('image', ['file' => $logo, 'alt' => $logo->alt()->value(), 'heightVw' => 4, 'max' => 800]) ?>
                            </a>
                        <?php else: ?>
                            <?php snippet('image', ['file' => $logo, 'alt' => $logo->alt()->value(), 'heightVw' => 4, 'max' => 800]) ?>
                        <?php endif ?>
                    <?php endforeach ?>
                </div>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>

        <div class="besuch-panel">
            <h2 class="besuch-section-title"><?= t('ui.privacy') ?></h2>
            <div class="besuch-section-body impressum-privacy">
                <?= $page->datenschutz() ?>
            </div>
        </div>
    </div>
</main>
<?php snippet('page-end') ?>
