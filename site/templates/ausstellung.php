<?php snippet('head') ?>
<?php snippet('vite', ['entry' => 'src/js/ausstellung.js']) ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

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
        <?php $minHeightClass = ''; 
            if($page->logoskooperation()->isNotEmpty()):
                $minHeightClass = 'has-logos-kooperation';
            endif;
            if($page->logos()->isNotEmpty()):
                $minHeightClass .= ' has-logos';
            endif;
        ?>
        <div class="inner-text-wrapper <?= $minHeightClass ?>">
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
            <?php if ( $page->galerie()->toFiles()->count() > 0): ?>
                <div class="single-ausstellung-page__text-container__text">
                    <?= $page->beschreibung()->kt() ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="single-ausstellung-page__buttons-wrapper-outer">
                    <button class="close-text-mode-js bubble"><?= t('ui.close') ?></button>
        
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
    <div class="single-ausstellung-page__images-wrapper">
        <div class="scroll-container">
            <?php if ( $page->galerie()->toFiles()->count() == 0): ?>
                <div class="single-ausstellung-page__text-container__text">
                    <?= $page->beschreibung()->kt() ?>
                </div>
            <?php endif; ?>
            <?php $images = $page->galerie()->toFiles(); ?>
            <?php for ($i = 0, $count = $images->count(); $i < $count; $i++): ?>
                <?php
                    $image = $images->nth($i);
                    $nextImage = $images->nth($i + 1);
                    $isPortrait = $image->height() > $image->width();
                    $canCouple = $image->canBeCoupled()->toBoolean();
                    $nextIsPortrait = $nextImage && $nextImage->height() > $nextImage->width();
                    $nextCanCouple = $nextImage && $nextImage->canBeCoupled()->toBoolean();
                    $shouldCouple = $canCouple && $nextCanCouple && $isPortrait && $nextIsPortrait;
                    $ratioClass = $isPortrait ? 'portrait' : 'landscape';
                ?>

                <?php if ($shouldCouple): ?>
                    <div class="image-coupler">
                        <div class="single-ausstellung-page__images-wrapper__image">
                            <div class="inner-image-wrapper">
                                <img class="<?= $ratioClass ?>" src="<?= $image->resize(2000)->url() ?>" alt="<?= esc($image->alt()) ?>">
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
                                <img class="<?= $ratioClass ?>" src="<?= $nextImage->resize(2000)->url() ?>" alt="<?= esc($nextImage->alt()) ?>">
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
                            <img class="<?= $ratioClass ?>" src="<?= $image->resize(2000)->url() ?>" alt="<?= esc($image->alt()) ?>">
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