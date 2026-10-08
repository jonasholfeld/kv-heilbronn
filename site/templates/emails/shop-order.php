<?php
/**
 * Order mail to the Kunstverein (plain text)
 *
 * @var \Kirby\Cms\Page     $item
 * @var array               $order    see kvShopOrderValidate()
 * @var \Kirby\Cms\Language $language
 */
?>
Neue Bestellung über die Website

Shop-Eintrag: <?= $item->orderTitle() ?>

<?= $item->url($language->code()) ?>


<?php foreach ($order['lines'] as $line): ?>
<?= $line['label'] ?>: <?= $line['value'] ?>

<?php endforeach ?>

Sprache des Formulars: <?= $language->name() ?>

Antworten auf diese E-Mail gehen direkt an die Besteller:in.
