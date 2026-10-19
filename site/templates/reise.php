<?php
$reisenPage = $page->parent();
$cat = $page->category()->value();
$reiseColor = $cat === 'atelierbesuch'
    ? ($reisenPage && !$reisenPage->atelierbesuchcolor()->isEmpty() ? (string)$reisenPage->atelierbesuchcolor() : '#ff8c5a')
    : ($reisenPage && !$reisenPage->kunstreisecolor()->isEmpty() ? (string)$reisenPage->kunstreisecolor() : '#cad9cc');
$catLabel = $cat === 'atelierbesuch' ? t('ui.studio_visit') : t('ui.art_trip');
$dateStr = $page->reiseStart()->toDate('d.m.Y');
$columnMode = $page->galerie()->toFiles()->count() == 0 ? 'no-images' : 'images';
// Registration for upcoming trips, built on the reisen page
$hasForm = $page->reiseIsUpcoming() && $reisenPage && $reisenPage->formular()->toBlocks()->isNotEmpty();
$formResult = $page->formResult('reise');
?>
<?php snippet('head', ['colorPage' => $reiseColor]) ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="single-reise-page<?= $formResult ? ' is-form-open' : '' ?>" data-form-host style="--reise-color: <?= $reiseColor ?>">
    <aside class="reisen-sidebar sidebar-small">
        <div class="reisen-page-info page-info"><?= t('ui.travels') ?></div>
        <button class="menu-button-js reisen-pill reisen-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="reisen-pill reisen-pill--dark reisen-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="single-reise-page__content">
        
            <div class="single-reise-page__text">
                <div class="scroll-container">
                    <div class="single-reise-page__text-container <?= $columnMode ?>">
                        <h1 class="single-reise-page__title"><?= $page->title()->html() ?></h1>
                        <?php if ($dateStr): ?>
                        <ul class="reise-info-list">
                            <li><?= esc($catLabel) ?> am <?= esc($dateStr) ?></li>
                        </ul>
                        <?php endif ?>
                        <?php if($columnMode != 'no-images'): ?>
                            <div class="single-reise-page__description">
                                <?= $page->beschreibung()->kt() ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($columnMode != 'no-images'): ?>
                        <?php if ($page->reiseplan()->isNotEmpty()): ?>
                        <div class="single-reise-page__text-container"><?= $page->reiseplan()->kt() ?></div>
                        <?php endif ?>
                        <?php if ($page->anmeldung()->isNotEmpty()): ?>
                        <div class="single-reise-page__text-container"><?= $page->anmeldung()->kt() ?></div>
                        <?php endif ?>
                    <?php endif ?>
                </div>
                <div class="single-reise-page__buttons-wrapper-outer">
                    <a class="reise-back-link bubble" href="<?= page('reisen')->url() ?>"><?= t('ui.travels') ?></a>
                    <?php if ($hasForm): ?>
                        <button class="reise-form-open bubble" type="button" data-form-open>
                            <?= esc($reisenPage->formularOeffnen()->or(t('ui.registration'))->value()) ?>
                        </button>
                    <?php endif ?>
                </div>
        </div>

        <div class="single-reise-page__images <?= $columnMode ?>">
            <div class="scroll-container">
                <?php if($columnMode == 'no-images'): ?>
                        <?php if ($dateStr): ?>
                        <?php endif ?>
                        <div class="single-reise-page__description">
                            <?= $page->beschreibung()->kt() ?>
                        </div>
                        <?php if ($page->reiseplan()->isNotEmpty()): ?>
                        <div class="single-reise-page__text-container"><?= $page->reiseplan()->kt() ?></div>
                        <?php endif ?>
                        <?php if ($page->anmeldung()->isNotEmpty()): ?>
                        <div class="single-reise-page__text-container"><?= $page->anmeldung()->kt() ?></div>
                        <?php endif ?>
                <?php else: ?>
                <?php $galerie = $page->galerie()->toFiles(); ?>
                <?php foreach ($galerie as $img): ?>
                    <div class="single-reise-page__image">
                        <div class="inner-image-wrapper">
                            <?php snippet('image', [
                                'file'     => $img,
                                'alt'      => $img->alt()->value(),
                                'sizes'    => '50vw',
                                'priority' => $img->isFirst($galerie),
                            ]) ?>
                            <?php snippet('image-credits', ['file' => $img]) ?>
                        </div>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
            </div>
        </div>
    </div>
    <?php // Mobile only: the last boxes, below the images ?>
    <div class="single-reise-page__mobile-links">
        <div><a class="reise-back-link bubble" href="<?= page('reisen')->url() ?>"><?= t('ui.travels') ?></a></div>
        <div><a class="reise-home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a></div>
    </div>
    <?php if ($hasForm || $formResult): ?>
        <?php snippet('form-overlay', ['form' => 'reise', 'result' => $formResult]) ?>
    <?php endif ?>
</main>
<?php snippet('page-end') ?>
