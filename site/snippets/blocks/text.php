<?php /** @var \Kirby\Cms\Block $block */ ?>
<div <?= $block->anchor() ? 'id="' . $block->anchor() . '"' : '' ?>><?= $block->text(); ?></div>