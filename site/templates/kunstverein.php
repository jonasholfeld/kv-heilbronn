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
            <?php if($page->satzungpdf()->isNotEmpty()): ?>
                <div class="kunstverein-block satzung">
                    <a class="bubble" href="<?= $page->satzungpdf()->toFile()->url() ?>" target="_blank"><?= t('ui.statute') ?> <?= t('ui.satzung') ?></a>
                </div>
            <?php endif ?>
        </div>

        <?php if ($gallery->count() > 0): ?>
        <div class="kunstverein-images">
            <div class="scroll-container">
            <?php foreach ($gallery as $img): ?>
                <div class="kunstverein-image">
                    <img src="<?= $img->resize(1200)->url() ?>" alt="<?= esc($img->alt()) ?>">
                </div>
            <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>
    </div>
</main>
