<?php snippet('head') ?>
<?php snippet('navi', ['includeSiteMenu' => false]) ?>

<main class="shop-item-page" style="--color: <?= $page->color()->or('#000000') ?>">
    <aside class="shop-sidebar sidebar-small">
        <div class="shop-page-info page-info"><?= t('ui.shop') ?></div>
        <button class="menu-button-js bubble bubble-inverted" type="button"><?= t('ui.menu') ?></button>
        <a class="shop-pill shop-pill--dark shop-home-link home-link bubble" href="<?= site()->url() ?>"><?= t('ui.homepage') ?></a>
    </aside>
    <div class="shop-item-page__right">
        <div class="shop-item-page__text">
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
        </div>
        <div class="shop-item-buttons-wrapper">
            <a class="schließen-btn" href="<?= page('shop')->url() ?>"><?= t('ui.shop') ?></a>
        </div>
    </div>
    <div class="shop-item-page__left">
        <div class="scroll-container">
            <?php foreach($page->galerie()->toFiles() as $img): ?>
                <div class="shop-item-page__image-container">
                    <?php if($img): ?>
                        <img src="<?= $img->resize(1500)->url() ?>" alt="<?= $img->alt()->or($page->kuenstler())->esc() ?>">
                        <div class="shop-item-page__image-caption-wrapper">
                            <?php if($img->title()->isNotEmpty()): ?>
                                <div class="shop-item-page__image-title"><?= $img->title()->html() ?></div>
                            <?php endif ?>
                            <?php if($img->caption()->isNotEmpty()): ?>
                                <div class="shop-item-page__image-caption"><?= $img->caption()->html() ?></div>
                            <?php endif ?>
                            <?php if($img->credit()->isNotEmpty()): ?>
                                <div class="shop-item-page__image-credit"><?= $img->credit()->html() ?></div>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</main>
