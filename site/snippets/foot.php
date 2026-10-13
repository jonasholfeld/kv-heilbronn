<footer>
    <?php // Mobile only: the header logo collapses while this one is in view ?>
    <a class="footer-logo" href="<?= site()->url() ?>">
        <?php snippet('logo') ?>
    </a>
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
        <p><?= t('ui.directions_to_kunstverein') ?></p>
    </div>
    <div class="footer-membership">
        <h2><?= t('ui.membership') ?></h2>
        <p><a>Digitaler Antrag</a>
        <a>Satzung</a></p>
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
