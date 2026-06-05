<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="shop-item-page" style="--color: <?= $page->color()->or('#000000') ?>">
    <aside class="shop-sidebar sidebar-small">
        <div class="shop-page-info page-info"><?= t('ui.shop') ?></div>
        <button class="menu-button-js bubble bubble-inverted" type="button"><?= t('ui.menu') ?></button>
        <a class="shop-pill shop-pill--dark shop-home-link home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>
    <div class="shop-item-page__left">
        <div class="shop-item-page__image-container">
            <?php $img = $page->titelbild()->toFiles()->first() ?? $page->galerie()->toFiles()->first() ?>
            <?php if($img): ?>
                <img src="<?= $img->resize(800)->url() ?>" alt="<?= $img->alt()->or($page->kuenstler())->esc() ?>">
            <?php endif ?>
        </div>
    </div>
    <div class="shop-item-page__right">
        <h2>
            <?php if($page->kuenstler()->isNotEmpty()): ?>
                <span><?= $page->kuenstler()->html() ?></span>
            <?php endif ?>
            <span><?= $page->title()->html() ?></span>
        </h2>
        <div class="shop-item-page__description">
            <?= $page->beschreibung()->kt() ?>
        </div>
        <div class="shop-item-page__order">
            <button class="bestellen-btn"><?= t('ui.order') ?></button>
        </div>
        <a class="schließen-btn" href="<?= page('shop')->url() ?>"><?= t('ui.close') ?></a>
    </div>
</main>

<?php snippet('foot') ?>
