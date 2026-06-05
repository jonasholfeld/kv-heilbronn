<?php
    $languageCode = $kirby->languageCode();
    $homeUrl = $languageCode ? site()->url($languageCode) : site()->url();
    $gobackUrl = $goback;

    if (is_string($gobackUrl) && str_starts_with($gobackUrl, '/')) {
        $gobackUrl = rtrim($homeUrl, '/') . $gobackUrl;
    }
?>
<div class="single-ausstellung-page__text-mode-buttons-wrapper sidebar-text-mode">
    <button class="menu-button-js bubble"><?= t('ui.menu') ?></button>
    <a href="<?= $gobackUrl ?>" class="go-back-button-js bubble"><?= t('ui.exhibitions') ?></a>
    <a href="<?= $homeUrl ?>" class="home-button-js bubble"><?= t('ui.homepage') ?></a>
</div>
