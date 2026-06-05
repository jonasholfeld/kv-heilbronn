<?php
$category = t('ui.exhibitions');
$previewHeading = $exhiClass === 'archive' ? t('ui.from_archives') : t('ui.exhibition_preview');
?>

<div data-category="<?= $category ?>" class="exhibition-wrapper exhibition-wrapper--<?= $exhiClass ?> home-item" style="--color: <?= $exhibition->color()->esc() ?>;">
    <div class="inner-home-item">
    <section class="exhibition-info exhibition-info--<?= $exhiClass ?>">
        <p class="exhibition__label exhibition__label--<?= $exhiClass ?>"><?= $previewHeading ?></p>
        <div class="exhibition__details exhibition__details--<?= $exhiClass ?>">
            <div class="exhibition__names exhibition__names--<?= $exhiClass ?>">
                <p><?= $exhibition->kuenstler()->esc() ?></p>
                <p><?= $exhibition->title()->esc() ?></p>
            </div>
            <div class="exhibition__dates exhibition__dates--<?= $exhiClass ?>">
                <p>
                    <?= $exhibition->startdatum()->toDate('d.m.Y') ?>
                    &ndash;
                    <?= $exhibition->enddatum()->toDate('d.m.Y') ?>
                </p>
                <?php if ($exhibition->eroffnungsdatum()->isNotEmpty()): ?>
                    <?php
                    $ts = $exhibition->eroffnungsdatum()->toDate();
                    $days   = ['So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.'];
                    $months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
                    ?>
                    <p>Eröffnung: <?= $days[date('w', $ts)] ?>, <?= date('j', $ts) ?>. <?= $months[date('n', $ts) - 1] ?>, <?= date('H', $ts) ?> Uhr</p>
                <?php endif ?>
            </div>
            <a href="<?= $exhibition->url() ?>" class="exhibition__link exhibition__link--<?= $exhiClass ?>"><?= t('ui.more_information') ?></a>
        </div>
    </section>

    <div class="exhibition-wrapper__label-wrapper exhibition-wrapper__label-wrapper--<?= $exhiClass ?>">
        <p class="section-label category-label"><a href="<?= page('ausstellungen')->url() ?>"><?= $category ?></a></p>
        <p class="section-label"><a href="<?= page('ausstellungen')->url() ?>"><?= $exhiLabel ?></a></p>
    </div>
    
    <?php
    $selectedHomepageImages = $exhibition->homepageImages()->toFiles()->limit(3);
    $images = $selectedHomepageImages->isNotEmpty()
        ? $selectedHomepageImages
        : $exhibition->galerie()->toFiles()->limit(3);
    $imageCount = max(1, min(3, $images->count()));
    $singleOrientationClass = '';
    if ($images->count() === 1) {
        $singleImage = $images->first();
        if ($singleImage && $singleImage->isPortrait()) {
            $singleOrientationClass = ' exhibition-gallery--single-portrait';
        } elseif ($singleImage && $singleImage->isLandscape()) {
            $singleOrientationClass = ' exhibition-gallery--single-landscape';
        }
    }
    ?>
    <?php if ($images->count() > 0): ?>
        <section class="exhibition-gallery exhibition-gallery--count-<?= $imageCount ?><?= $singleOrientationClass ?>">
            <?php foreach ($images as $image): ?>
                <div class="exhibition-gallery__item">
                    <img
                        src="<?= $image->url() ?>"
                        alt="<?= $image->alt()->or($exhibition->kuenstler())->esc() ?>">
                </div>
            <?php endforeach ?>
        </section>
    <?php endif ?>
    </div>
</div>
