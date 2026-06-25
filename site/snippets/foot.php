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
        <p><?= t('ui.directions_to_kunstverein') ?></p>
    </div>
    <div class="footer-membership">
        <h2><?= t('ui.membership') ?></h2>
        <p><a>Digitaler Antrag</a>
        <a>Satzung</a></p>
    </div>
</footer>
</body>
</html>
