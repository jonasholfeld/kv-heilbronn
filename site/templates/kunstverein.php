<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<?php
$gallery = $page->gallery()->toFiles();
$blocks = $page->blockseditor()->toBlocks();
?>

<main class="kunstverein-template">
    <aside class="kunstverein-sidebar sidebar-small">
        <div class="kunstverein-page-info page-info"><?= t('ui.kunstverein') ?></div>
        <button class="menu-button-js kunstverein-pill kunstverein-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="kunstverein-pill kunstverein-pill--dark kunstverein-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="kunstverein-content">
        <div class="kunstverein-blocks">
            <?php foreach ($blocks as $block): ?>
                <div class="kunstverein-block">
                    <?= $block ?>
                </div>
            <?php endforeach ?>
        </div>

        <?php if ($gallery->count() > 0): ?>
        <div class="kunstverein-images">
            <?php foreach ($gallery as $img): ?>
                <div class="kunstverein-image">
                    <img src="<?= $img->resize(1200)->url() ?>" alt="<?= esc($img->alt()) ?>">
                </div>
            <?php endforeach ?>
        </div>
        <?php endif ?>
    </div>
</main>
