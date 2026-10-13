<?php
/**
 * Form submission to the Kunstverein (plain text), see site/plugins/kv-forms
 *
 * @var string              $heading  name of the form
 * @var string              $title    page the form was sent from
 * @var \Kirby\Cms\Page     $page
 * @var array               $order    see kvFormsValidate()
 * @var \Kirby\Cms\Language $language
 */
?>
Neue Nachricht über das Formular „<?= $heading ?>“

<?= $title ?>

<?= $page->url($language->code()) ?>

<?php foreach ($order['lines'] as $line): ?>
<?php if (isset($line['heading'])): ?>

<?= Kirby\Toolkit\Str::upper($line['heading']) ?>

<?php else: ?>
<?= $line['label'] ?>: <?= $line['value'] ?>

<?php endif ?>
<?php endforeach ?>
<?php if (!empty($order['uploads'])): ?>

Hochgeladene Dateien sind angehängt.
<?php endif ?>

Sprache des Formulars: <?= $language->name() ?>

Antworten auf diese E-Mail gehen direkt an die Absender:in.
