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
    <?php if ($closeButton ?? false): ?>
        <?php // Mobile only: closes the text mode from the top bar ?>
        <button class="close-text-mode-js sidebar-text-mode__close bubble"><?= t('ui.close') ?></button>
    <?php endif ?>
    <a href="<?= $homeUrl ?>" class="home-button-js bubble"><?= t('ui.homepage') ?></a>
</div>
