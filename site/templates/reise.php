<?php
$reisenPage = $page->parent();
$cat = $page->category()->value();
$reiseColor = $cat === 'atelierbesuch'
    ? ($reisenPage && !$reisenPage->atelierbesuchcolor()->isEmpty() ? (string)$reisenPage->atelierbesuchcolor() : '#ff8c5a')
    : ($reisenPage && !$reisenPage->kunstreisecolor()->isEmpty() ? (string)$reisenPage->kunstreisecolor() : '#cad9cc');
$catLabel = $cat === 'atelierbesuch' ? t('ui.studio_visit') : t('ui.art_trip');
$dateStr = $page->reiseStart()->toDate('d.m.Y');
$columnMode = $page->galerie()->toFiles()->count() == 0 ? 'no-images' : 'images';
?>
<?php snippet('head') ?>
<?php snippet('vite', ['entry' => 'src/js/reise.js']) ?>
<style>body { --colorPage: <?= $reiseColor ?> !important; }</style>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="single-reise-page" style="--reise-color: <?= $reiseColor ?>">
    <aside class="reisen-sidebar sidebar-small">
        <div class="reisen-page-info page-info"><?= t('ui.travels') ?></div>
        <button class="menu-button-js reisen-pill reisen-pill--dark" type="button"><?= t('ui.menu') ?></button>
        <a class="reisen-pill reisen-pill--dark reisen-home-link" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>

    <div class="single-reise-page__content">
        <div class="single-reise-page__text">
            <div class="single-reise-page__text-container <?= $columnMode ?>">
                <h1 class="single-reise-page__title"><?= $page->title()->html() ?></h1>
                <?php if($columnMode != 'no-images'): ?>
                    <?php if ($dateStr): ?>
                    <ul class="reise-info-list">
                        <li><?= esc($catLabel) ?> am <?= esc($dateStr) ?></li>
                    </ul>
                    <?php endif ?>
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

        <div class="single-reise-page__images">
            <?php if($columnMode == 'no-images'): ?>
                    <?php if ($dateStr): ?>
                    <ul class="reise-info-list">
                        <li><?= esc($catLabel) ?> am <?= esc($dateStr) ?></li>
                    </ul>
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
            <?php
            $titelbild = $page->titelbild()->toFiles()->first();
            $galerie = $page->galerie()->toFiles();
            ?>
            <?php if ($titelbild): ?>
                <div class="single-reise-page__image">
                    <div class="inner-image-wrapper">
                        <img src="<?= $titelbild->resize(1200)->url() ?>" alt="<?= esc($titelbild->alt()) ?>">
                        <?php if ($titelbild->credits()->isNotEmpty()): ?>
                            <div class="credits-wrapper bubble">
                                <p>Credits</p>
                                <div class="credits-collapse-wrapper">
                                    <div class="credits-content-wrapper">
                                        <?= $titelbild->credits()->kt() ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            <?php endif ?>
            <?php foreach ($galerie as $img): ?>
                <div class="single-reise-page__image">
                    <div class="inner-image-wrapper">
                        <img src="<?= $img->resize(1200)->url() ?>" alt="<?= esc($img->alt()) ?>">
                        <?php if ($img->credits()->isNotEmpty()): ?>
                            <div class="credits-wrapper bubble">
                                <p>Credits</p>
                                <div class="credits-collapse-wrapper">
                                    <div class="credits-content-wrapper">
                                        <?= $img->credits()->kt() ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif ?>
        </div>
    </div>
</main>

