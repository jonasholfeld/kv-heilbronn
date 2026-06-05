<footer>
    <div class="footer-contact">
        <h2><?= t('ui.contact') ?></h2>
        <?= page('besuch')->kontakt()->kt() ?>
    </div>
    <div class="footer-openings">
        <h2><?= t('ui.opening_hours') ?></h2>
        <?= page('besuch')->offnungszeiten()->kt() ?>
        <p><?= t('ui.directions_to_kunstverein') ?></p>
        <h2><a href="<?= page('impressum')->url() ?>"><?= t('ui.imprint') ?></a></h2>
    </div>
    <div class="footer-membership">
        <h2><?= t('ui.membership') ?></h2>
    </div>
</footer>
</body>
</html>
