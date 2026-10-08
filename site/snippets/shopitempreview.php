<?php $category = 'shop'; ?>
<div data-category="<?= $category ?>" class=" scroll-top-element shop-item-wrapper home-item" style="--color: <?= $item->color()->esc() ?>;">
  <header class="content-wrapper">  
    <div class="shop-item-body">
        <a href="<?= $item->url() ?>" class="shop-item-info">
          <p class="shop-item__label"><?= esc(strtoupper($itemLabel)) ?></p>
          <div class="shop-item__names">
            <?php if ($item->kuenstler()->isNotEmpty()): ?>
              <p><?= $item->kuenstler()->esc() ?></p>
            <?php endif ?>
            <p><?= $item->title()->esc() ?></p>
          </div>
          <?php $shopPage = page('shop') ?>
          <?php if ($shopPage): ?>
            <span class="shop-item__link"><?= t('ui.more_information') ?></span>
          <?php endif ?>
        </a>
        <div class="shop-item-image">

          <?php $img = $item->galerie()->toFiles()->first() ?>
          <?php if ($img): ?>
            <?php snippet('image', [
              'file'  => $img,
              'alt'   => $img->alt()->or($item->kuenstler())->value(),
              'sizes' => '50vw',
            ]) ?>
          <?php endif ?>
        </div>
      </div>
      <div class="label-wrapper shop-item-wrapper__label-wrapper">
        <span class="section-label category-label"><a href="<?= $shopPage->url() ?>"><?= $category ?></a></span>
        <span class="section-label"><a href="<?= $shopPage->url() ?>"><?= esc(strtoupper($itemLabel)) ?></a></span>
      </div>
  </header>
</div>
