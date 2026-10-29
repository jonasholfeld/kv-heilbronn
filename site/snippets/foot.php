<footer>
    <div class="footer-contact">
        <h2><?= t('ui.contact') ?></h2>
        <?= page('besuch')->kontakt()->kt() ?>
        <div class="imprint-list">
            <ul>
                <li><a href="<?= page('impressum')->url() ?>"><?= t('ui.imprint') ?></a></li>
                <li><a href="<?= page('impressum')->url() ?>"><?= t('ui.privacy_policy') ?></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-openings">
        <h2><?= t('ui.opening_hours') ?></h2>
        <?= page('besuch')->offnungszeiten()->kt() ?>
        <p><a href="<?= page('besuch')->url() ?>"><?= t('ui.directions_to_kunstverein') ?></a></p>
    </div>
    <?php
    $kunstvereinPage = page('kunstverein');
    $satzung = $kunstvereinPage?->satzungpdf()->toFile();
    ?>
    <div class="footer-membership">
        <h2><?= t('ui.membership') ?></h2>
        <?php // Opens the membership form on /kunstverein (form-overlay.js) ?>
        <p><a href="<?= $kunstvereinPage?->url() ?>#formular">Digitaler Antrag</a>
        <?php if ($satzung): ?>
        <a href="<?= $satzung->url() ?>" target="_blank">Satzung</a>
        <?php endif ?></p>
    </div>
    <?php // Mobile only: the imprint links get a box of their own ?>
    <div class="footer-imprint">
        <p>
            <a href="<?= page('impressum')->url() ?>"><?= t('ui.imprint') ?></a>
            <a href="<?= page('impressum')->url() ?>"><?= t('ui.privacy_policy') ?></a>
        </p>
    </div>
</footer>
<?php snippet('page-end') ?>
