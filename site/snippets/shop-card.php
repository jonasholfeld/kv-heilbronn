<?php $itemColor = $item->color()->isEmpty() ? '#dce0e3' : $item->color()->value() ?>
<a class="shop-item-card" href="<?= $item->url() ?>" style="--item-color: <?= esc($itemColor) ?>">
    <div class="shop-item-card__image-wrapper">
        <?php $img = $item->titelbild()->toFiles()->first() ?? $item->galerie()->toFiles()->first() ?>
        <?php if($img): ?>
            <img src="<?= $img->resize(1000)->url() ?>" alt="<?= $img->alt()->or($item->kuenstler())->esc() ?>">
        <?php endif ?>
    </div>
    <div class="shop-item-card__info">
        <?php if($item->kuenstler()->isNotEmpty()): ?>
            <p><?= $item->kuenstler()->html() ?></p>
        <?php endif ?>
        <p><?= $item->title()->html() ?></p>
        <span class="shop-item-card__link"><?= esc($moreInfoLabel) ?></span>
    </div>
</a>
