<?php $itemColor = $item->color()->isEmpty() ? '#dce0e3' : $item->color()->value() ?>
<a class="shop-item-card" href="<?= $item->url() ?>" style="--item-color: <?= esc($itemColor) ?>">
    <div class="shop-item-card__image-wrapper">
        <?php $img = $item->galerie()->toFiles()->first() ?>
        <?php if($img): ?>
            <?php snippet('image', [
                'file'  => $img,
                'alt'   => $img->alt()->or($item->kuenstler())->value(),
                'sizes' => '44vw',
            ]) ?>
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
