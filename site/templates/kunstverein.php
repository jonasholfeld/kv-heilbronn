<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<?php
$gallery = $page->gallery()->toFiles();
$blocks = $page->blockseditor()->toBlocks();
$formResult = $page->formResult('mitgliedschaft');
$hasForm = $page->formular()->toBlocks()->isNotEmpty();
$satzung = $page->satzungpdf()->toFile();
?>

<main class="kunstverein-template<?= $formResult ? ' is-form-open' : '' ?>" data-form-host>
    <aside class="kunstverein-sidebar sidebar-small">
        <div class="kunstverein-page-info page-info"><?= t('ui.kunstverein') ?></div>
        <button class="menu-button-js kunstverein-pill kunstverein-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="kunstverein-pill kunstverein-pill--dark kunstverein-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="kunstverein-content">
        <div class="kunstverein-text">
            <div class="kunstverein-blocks">
                <?php foreach ($blocks as $block): ?>
                    <div class="kunstverein-block">
                        <?= $block ?>
                    </div>
                <?php endforeach ?>
                <?php // Mobile only: Satzung and form in boxes of their own ?>
                <?php if ($satzung): ?>
                    <div class="kunstverein-block satzung">
                        <a class="bubble" href="<?= $satzung->url() ?>" target="_blank"><?= t('ui.statute') ?></a>
                    </div>
                <?php endif ?>
                <?php if ($hasForm): ?>
                    <div class="kunstverein-block kunstverein-block--form">
                        <button class="kunstverein-block__link" type="button" data-form-open>
                            <?= esc($page->formularOeffnen()->or(t('ui.form_open_membership'))->value()) ?>
                        </button>
                    </div>
                <?php endif ?>
            </div>
            <?php if ($satzung || $hasForm): ?>
                <?php // Desktop only: fixed below the scrolling text blocks ?>
                <div class="kunstverein-buttons">
                    <?php if ($satzung): ?>
                        <a class="bubble" href="<?= $satzung->url() ?>" target="_blank"><?= t('ui.statute') ?></a>
                    <?php endif ?>
                    <?php if ($hasForm): ?>
                        <button class="bubble kunstverein-form-open" type="button" data-form-open>
                            <?= esc($page->formularOeffnen()->or(t('ui.form_open_membership'))->value()) ?>
                        </button>
                    <?php endif ?>
                </div>
            <?php endif ?>
        </div>

        <?php if ($gallery->count() > 0): ?>
        <div class="kunstverein-images">
            <div class="scroll-container">
            <?php foreach ($gallery as $img): ?>
                <div class="kunstverein-image">
                    <?php snippet('image', [
                        'file'     => $img,
                        'alt'      => $img->alt()->value(),
                        'sizes'    => '50vw',
                        'priority' => $img->isFirst($gallery),
                    ]) ?>
                </div>
            <?php endforeach ?>
            </div>
        </div>
        <?php endif ?>
        <?php // Mobile only: the last box ?>
        <div class="kunstverein-mobile-links">
            <a class="kunstverein-home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
        </div>
    </div>
    <?php if ($hasForm || $formResult): ?>
        <?php snippet('form-overlay', ['form' => 'mitgliedschaft', 'result' => $formResult]) ?>
    <?php endif ?>
</main>
<?php snippet('page-end') ?>
