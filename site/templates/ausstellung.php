<?php snippet('head') ?>
<?php snippet('vite', ['entry' => 'src/js/ausstellung.js']) ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>
<?php
$renderPdfPreview = static function ($file, int $maxWidth = 2000): ?array {
    if (
        $file->extension() !== 'pdf' ||
        extension_loaded('imagick') !== true ||
        class_exists('Imagick') !== true
    ) {
        return null;
    }

    $mediaRoot = kirby()->root('media') . '/pdf-previews/' . $file->parent()->id();
    $mediaUrl  = kirby()->url('media') . '/pdf-previews/' . $file->parent()->id();
    $hash      = md5($file->root() . '|' . $file->modified() . '|' . $maxWidth);
    $filename  = pathinfo($file->filename(), PATHINFO_FILENAME) . '-' . $hash . '.jpg';
    $targetRoot = $mediaRoot . '/' . $filename;
    $targetUrl  = $mediaUrl . '/' . $filename;

    try {
        if (is_file($targetRoot) !== true) {
            if (PHP_SAPI !== 'cli') {
                return null;
            }

            if (is_dir($mediaRoot) !== true) {
                mkdir($mediaRoot, 0775, true);
            }

            $imagick = new Imagick();
            $imagick->setResolution(96, 96);
            $imagick->readImage($file->root() . '[0]');
            $imagick->setImageBackgroundColor('white');
            $imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(82);
            $imagick->thumbnailImage(min($maxWidth, 1400), 0);
            $imagick->writeImage($targetRoot);
            $imagick->clear();
            $imagick->destroy();
        }

        [$width, $height] = getimagesize($targetRoot) ?: [0, 0];

        if ($width < 1 || $height < 1) {
            return null;
        }

        return [
            'file' => $file,
            'url' => $targetUrl,
            'width' => $width,
            'height' => $height,
        ];
    } catch (Throwable $e) {
        return null;
    }
};

$prepareGalleryItem = static function ($file) use ($renderPdfPreview): ?array {
    if ($file->isResizable()) {
        return [
            'file' => $file,
            'url' => $file->resize(2000)->url(),
            'width' => $file->width(),
            'height' => $file->height(),
        ];
    }

    return $renderPdfPreview($file);
};

$galleryItems = array_values(array_filter(array_map(
    $prepareGalleryItem,
    $page->galerie()->toFiles()->values()
)));
?>

<main class="single-ausstellung-page" style="--ausstellung-color: <?= $page->color()->isEmpty() ? '#dce0e3' : $page->color() ?>">
    <div class="single-ausstellung-page__top-info-box">
        <div class="single-ausstellung-page__top-info-box__artist-title">
            <span><?= $page->kuenstler() ?></span>
            <span><?= $page->title()->html() ?></span>
        </div>
        <div class="single-ausstellung-page__top-info-box__date">
            <?php $eroTs = $page->eroffnungsdatum()->toDate(); ?>
            <?php $startStr = $page->startdatum()->toDate('d.m.Y'); $endStr = $page->enddatum()->toDate('d.m.Y'); ?>
            <?php if ($startStr || $endStr): ?>
            <p class="ausstellungen-row-dates"><?= esc(($startStr ?: '') . ($startStr && $endStr ? ' – ' : '') . ($endStr ?: '')) ?></p>
            <?php endif ?>
            <?php if ($eroTs): ?>
            <?php
                $eroDay    = $isDE ? $days[date('w', $eroTs)]       : $daysEn[date('w', $eroTs)];
                $eroMonth  = $isDE ? $months[date('n', $eroTs) - 1] : $monthsEn[date('n', $eroTs) - 1];
                $eroDayNum = date('j', $eroTs);
                $eroHour   = date('H', $eroTs);
                $eroStr    = $isDE
                ? "{$openingLabel}: {$eroDay}, {$eroDayNum}. {$eroMonth}, {$eroHour} Uhr"
                : "{$openingLabel}: {$eroDay}, {$eroMonth} {$eroDayNum}, {$eroHour}:00";
            ?>
            <p class="ausstellungen-row-opening"><?= esc($eroStr) ?></p>
            <?php endif ?>
        </div>
    </div>
    <side>
        <div class="single-ausstellung-page__small-top-info-box-wrapper">
            <div class="single-ausstellung-page__small-top-info-box">
                <span><?= $page->kuenstler() ?></span>
                <span><?= $page->title()->html() ?></span>
            </div>
        </div>
        <div class="single-ausstellung-page__buttons-wrapper">
            <button class="toggle-text-mode-js bubble">Text</button>
            <button class="menu-button-js bubble"><?= t('ui.menu') ?></button>
        </div>
        <?= snippet('side-bar-text-mode', ['goback' => '/ausstellungen']) ?>
    </side>
    <div class="single-ausstellung-page__text-container">
        <div class="inner-text-wrapper">
            <div class="scroll-container">
                <div class="text-wrapper-white-bg">
                    <div class="first-block-wrapper">
                        <h2>
                            <span><?= $page->kuenstler() ?></span>
                            <span><?= $page->title()->html() ?></span>
                        </h2>
                        <div class="single-ausstellung-page__text-container__dateime">
                            <ul>
                            <?php $eroTs = $page->eroffnungsdatum()->toDate(); ?>
                            <?php $startStr = $page->startdatum()->toDate('d.m.Y'); $endStr = $page->enddatum()->toDate('d.m.Y'); ?>
                            <?php if ($startStr || $endStr): ?>
                            <li><p class="ausstellungen-row-dates"><?= esc(($startStr ?: '') . ($startStr && $endStr ? ' – ' : '') . ($endStr ?: '')) ?></p></li>
                            <?php endif ?>
                            <?php if ($eroTs): ?>
                            <?php
                                $eroDay    = $isDE ? $days[date('w', $eroTs)]       : $daysEn[date('w', $eroTs)];
                                $eroMonth  = $isDE ? $months[date('n', $eroTs) - 1] : $monthsEn[date('n', $eroTs) - 1];
                                $eroDayNum = date('j', $eroTs);
                                $eroHour   = date('H', $eroTs);
                                $eroStr    = $isDE
                                ? "{$openingLabel}: {$eroDay}, {$eroDayNum}. {$eroMonth}, {$eroHour} Uhr"
                                : "{$openingLabel}: {$eroDay}, {$eroMonth} {$eroDayNum}, {$eroHour}:00";
                            ?>
                            <li><p class="ausstellungen-row-opening"><?= esc($eroStr) ?></p></li>
                            <?php endif ?>
                            </ul>
                        </div>
                    </div>
                    <?php if ( $page->galerie()->toFiles()->count() > 0): ?>
                        <?= $page->beschreibung()->toBlocks() ?>
                    <?php endif; ?>
                </div>
                <?php if($page->logos()->isNotEmpty() || $page->logoskooperation()->isNotEmpty()): ?>
                <div class="logo-wrapper-white-bg">
                        <?php if($page->logos()->isNotEmpty()): ?>
                            <div class="logo-wrapper">
                                <span><?= t('ui.sponsored') ?></span>
                                <div class="logo-images-wrapper">
                                    <?php foreach ($page->logos()->toFiles() as $logo): ?>
                                        <?php if(!$logo->linkurl()->isEmpty()): ?>
                                            <a href="<?= $logo->linkurl() ?>" target="_blank" rel="noopener noreferrer">
                                                <img src="<?= $logo->resize(1000)->url() ?>" alt="<?= esc($logo->alt()) ?>">
                                            </a>
                                        <?php else: ?>
                                            <img src="<?= $logo->resize(1000)->url() ?>" alt="<?= esc($logo->alt()) ?>">
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        <?php endif ?>
                        <?php if($page->logoskooperation()->isNotEmpty()): ?>
                            <div class="logo-wrapper">
                                <span><?= $page->kooperationtext()->esc() ?></span>
                                <div class="logo-images-wrapper">
                                    <?php foreach ($page->logoskooperation()->toFiles() as $logo): ?>
                                        <?php if(!$logo->linkurl()->isEmpty()): ?>
                                            <a href="<?= $logo->linkurl() ?>" target="_blank" rel="noopener noreferrer">
                                                <img src="<?= $logo->resize(1000)->url() ?>" alt="<?= esc($logo->alt()) ?>">
                                            </a>
                                        <?php else: ?>
                                            <img src="<?= $logo->resize(1000)->url() ?>" alt="<?= esc($logo->alt()) ?>">
                                        <?php endif ?>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        <?php endif ?>
                </div>
                <?php endif ?>
            </div>
        </div>
        <div class="single-ausstellung-page__buttons-wrapper-outer">
            <?php if($page->galerie()->toFiles()->count() < 4): ?>
                <a class="ausstellungen-back-link bubble" href="<?= page('ausstellungen')->url() ?>"><?= t('ui.exhibitions') ?></a>
            <?php else: ?>
                <button class="close-text-mode-js bubble"><?= t('ui.close') ?></button>
            <?php endif ?>
        
                <?php $katalogPage = $page->katalog()->toPage(); $editionPage = $page->edition()->toPage(); ?>
                <?php if ($katalogPage || $editionPage): ?>
                    <div class="single-ausstellung-page__text-container__links">
                        <?php if ($katalogPage): ?>
                            <a href="<?= $katalogPage->url() ?>" class="bubble">Katalog</a>
                        <?php endif ?>
                        <?php if ($editionPage): ?>
                            <a href="<?= $editionPage->url() ?>" class="bubble">Edition</a>
                        <?php endif ?>
                    </div>
                <?php endif ?>
                
        </div>
    </div>
    <div class="single-ausstellung-page__images-wrapper">
        <div class="scroll-container">
            <?php if ( $page->galerie()->toFiles()->count() == 0): ?>
                <div class="single-ausstellung-page__text-container__text">
                    <?= $page->beschreibung()->kt() ?>
                </div>
            <?php endif; ?>
            <?php for ($i = 0, $count = count($galleryItems); $i < $count; $i++): ?>
                <?php
                    $imageItem = $galleryItems[$i];
                    $nextImageItem = $galleryItems[$i + 1] ?? null;
                    $image = $imageItem['file'];
                    $nextImage = $nextImageItem['file'] ?? null;
                    $isPortrait = $imageItem['height'] > $imageItem['width'];
                    $canCouple = $image->canBeCoupled()->toBool();
                    $nextIsPortrait = $nextImageItem && $nextImageItem['height'] > $nextImageItem['width'];
                    $nextCanCouple = $nextImage && $nextImage->canBeCoupled()->toBool();
                    $shouldCouple = $canCouple && $nextCanCouple && $isPortrait && $nextIsPortrait;
                    $ratioClass = $isPortrait ? 'portrait' : 'landscape';
                ?>

                <?php if ($shouldCouple): ?>
                    <div class="image-coupler">
                        <div class="single-ausstellung-page__images-wrapper__image">
                            <div class="inner-image-wrapper">
                                <img class="<?= $ratioClass ?>" src="<?= $imageItem['url'] ?>" alt="<?= esc($image->alt()) ?>">
                                <?php
                                    $imageCredits = array_filter([
                                        $image->title()->isNotEmpty() ? $image->title()->esc() : null,
                                        $image->caption()->isNotEmpty() ? $image->caption()->esc() : null,
                                        $image->credit()->isNotEmpty() ? $image->credit()->esc() : null,
                                    ]);
                                ?>
                                <?php if (!empty($imageCredits)): ?>
                                    <div class="credits-wrapper bubble">
                                        <p>Credits</p>
                                        <div class="credits-collapse-wrapper">
                                            <div class="credits-content-wrapper">
                                                <?= implode('<br>', $imageCredits) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="single-ausstellung-page__images-wrapper__image">
                            <div class="inner-image-wrapper">
                                <img class="<?= $ratioClass ?>" src="<?= $nextImageItem['url'] ?>" alt="<?= esc($nextImage->alt()) ?>">
                                <?php
                                    $nextImageCredits = array_filter([
                                        $nextImage->title()->isNotEmpty() ? $nextImage->title()->esc() : null,
                                        $nextImage->caption()->isNotEmpty() ? $nextImage->caption()->esc() : null,
                                        $nextImage->credit()->isNotEmpty() ? $nextImage->credit()->esc() : null,
                                    ]);
                                ?>
                                <?php if (!empty($nextImageCredits)): ?>
                                    <div class="credits-wrapper bubble">
                                        <p>Credits</p>
                                        <div class="credits-collapse-wrapper">
                                            <div class="credits-content-wrapper">
                                                <?= implode('<br>', $nextImageCredits) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <?php $i++; ?>
                <?php else: ?>
                    <div class="single-ausstellung-page__images-wrapper__image">
                        <div class="inner-image-wrapper">
                            <img class="<?= $ratioClass ?>" src="<?= $imageItem['url'] ?>" alt="<?= esc($image->alt()) ?>">
                            <?php
                                $imageCredits = array_filter([
                                    $image->title()->isNotEmpty() ? $image->title()->esc() : null,
                                    $image->caption()->isNotEmpty() ? $image->caption()->esc() : null,
                                    $image->credit()->isNotEmpty() ? $image->credit()->esc() : null,
                                ]);
                            ?>
                            <?php if (!empty($imageCredits)): ?>
                                <div class="credits-wrapper bubble">
                                    <p>Credits</p>
                                    <div class="credits-collapse-wrapper">
                                        <div class="credits-content-wrapper">
                                            <?= implode('<br>', $imageCredits) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endif ?>
            <?php endfor ?>
        </div>
    </div>
    <div class="bottom-button-wrapper">
        <a class="ausstellungen-back-link bubble" href="<?= page('ausstellungen')->url() ?>"><?= t('ui.exhibitions') ?></a>
        <a class="ausstellungen-home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </div>
</main>
