<?php
/**
 * Order form overlay of a shop item (edition, katalog), see the
 * kv-shop-order plugin. The fields are built in the Panel on the shop page.
 *
 * @var \Kirby\Cms\Page $page   shop item
 * @var array|null      $result result of the last submission, shown instead
 *                              of the form (`?bestellung` view)
 */

$shop = page('shop');
if (!$shop) {
    return;
}

$result      = $result ?? null;
$languageUrl = kirby()->language()?->url() ?? kirby()->url();
$images      = $page->orderImages();
?>
<section class="order-overlay" aria-labelledby="order-overlay-title" data-order-overlay>
    <header class="order-overlay__header">
        <h2 id="order-overlay-title" tabindex="-1"><?= t('ui.order_form') ?></h2>
        <?php if ($result): ?>
            <a class="order-overlay__close" href="<?= $shop->url() ?>"><?= t('ui.close') ?></a>
        <?php else: ?>
            <button class="order-overlay__close" type="button" data-order-close><?= t('ui.close') ?></button>
        <?php endif ?>
    </header>

    <?php if ($result): ?>
        <?php $isSuccess = ($result['status'] ?? null) === 'success' ?>
        <div class="order-overlay__message" role="<?= $isSuccess ? 'status' : 'alert' ?>">
            <?php if ($isSuccess): ?>
                <?= $shop->bestellErfolg()->isNotEmpty() ? $shop->bestellErfolg()->permalinksToUrls() : '<p>' . t('ui.order_success') . '</p>' ?>
            <?php else: ?>
                <?= $shop->bestellFehler()->isNotEmpty() ? $shop->bestellFehler()->permalinksToUrls() : '<p>' . t('ui.order_error') . '</p>' ?>
            <?php endif ?>
            <?php if (!empty($result['messages'])): ?>
                <div class="order-overlay__details">
                    <?php foreach ($result['messages'] as $message): ?>
                        <p><?= esc($message) ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>
    <?php else: ?>
        <div class="order-overlay__info"><?= $page->orderDescription() ?></div>

        <form
            class="order-form"
            method="post"
            action="<?= $languageUrl ?>/shop/bestellen"
            data-token-url="<?= $languageUrl ?>/shop/bestellen/token"
            data-msg-required="<?= esc(t('ui.order_error_required'), 'attr') ?>"
            data-msg-choose="<?= esc(t('ui.order_error_choose'), 'attr') ?>"
            data-msg-email="<?= esc(t('ui.order_error_email'), 'attr') ?>"
            data-msg-tel="<?= esc(t('ui.order_error_tel'), 'attr') ?>"
            data-msg-consent="<?= esc(t('ui.order_error_consent'), 'attr') ?>"
            novalidate
            data-order-form
        >
            <input type="hidden" name="item" value="<?= esc($page->id(), 'attr') ?>">
            <input type="hidden" name="csrf" value="" data-order-csrf>
            <div class="order-form__honeypot" aria-hidden="true">
                <label><?= t('ui.order_honeypot') ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <?php foreach ($shop->bestellformular()->toBlocks() as $block): ?>
                <?php
                $name     = 'fields[' . $block->id() . ']';
                $label    = $block->label()->value() ?? '';
                $required = $block->required()->toBool();
                ?>
                <?php switch ($block->type()):
                    case 'form-editions': ?>
                        <?php if ($images->isNotEmpty()): ?>
                            <fieldset
                                class="order-form__editions"
                                data-label="<?= esc($label, 'attr') ?>"
                                <?= $required ? 'data-required' : '' ?>
                            >
                                <legend class="order-form__visually-hidden"><?= esc($label) ?></legend>
                                <?php if ($block->text()->isNotEmpty()): ?>
                                    <p class="order-form__editions-text"><?= $block->text()->html() ?><?= $required ? '*' : '' ?></p>
                                <?php endif ?>
                                <div class="order-form__edition-list">
                                    <?php foreach ($images->values() as $index => $image): ?>
                                        <label class="order-form__edition">
                                            <input type="checkbox" name="<?= $name ?>[]" value="<?= $index + 1 ?>">
                                            <span class="order-form__edition-image">
                                                <?php snippet('image', [
                                                    'file'  => $image,
                                                    'alt'   => $image->alt()->or($page->kuenstler())->value(),
                                                    'sizes' => '9vw',
                                                ]) ?>
                                            </span>
                                            <span class="order-form__edition-number">#<?= $index + 1 ?></span>
                                        </label>
                                    <?php endforeach ?>
                                </div>
                                <p
                                    class="order-form__editions-selection"
                                    aria-live="polite"
                                    data-none="<?= esc(t('ui.order_selected_none'), 'attr') ?>"
                                    data-one="<?= esc(t('ui.order_selected_one'), 'attr') ?>"
                                    data-many="<?= esc(t('ui.order_selected_many'), 'attr') ?>"
                                    data-order-selection
                                ><?= t('ui.order_selected_none') ?></p>
                            </fieldset>
                        <?php endif ?>
                        <?php if ($page->bestellHinweis()->isNotEmpty()): ?>
                            <div class="order-form__hint"><?= $page->bestellHinweis()->permalinksToUrls() ?></div>
                        <?php endif ?>
                        <?php break ?>

                    <?php case 'form-prices':
                    case 'form-choice': ?>
                        <?php
                        $options = $block->type() === 'form-prices'
                            ? $page->preise()->toStructure()->toArray(fn ($price) => $price->label()->value())
                            : $block->options()->split();
                        $options = array_values($options);
                        ?>
                        <?php if ($options !== []): ?>
                            <fieldset
                                class="order-form__choice"
                                data-label="<?= esc($label, 'attr') ?>"
                                <?= $required ? 'data-required' : '' ?>
                            >
                                <legend class="order-form__visually-hidden"><?= esc($label) ?></legend>
                                <?php foreach ($options as $index => $option): ?>
                                    <label class="order-form__pill order-form__option">
                                        <input
                                            type="radio"
                                            name="<?= $name ?>"
                                            value="<?= $index ?>"
                                            <?= $required ? 'required' : '' ?>
                                            <?= $index === 0 && $block->preselect()->toBool() ? 'checked' : '' ?>
                                        >
                                        <span><?= esc($option) ?></span>
                                    </label>
                                <?php endforeach ?>
                            </fieldset>
                        <?php endif ?>
                        <?php break ?>

                    <?php case 'form-text': ?>
                        <?php
                        $type        = $block->fieldtype()->or('text')->value();
                        $placeholder = $label . ($required ? '*' : '');
                        $isShort     = $block->short()->toBool();
                        ?>
                        <label class="order-form__pill order-form__field<?= $isShort ? ' order-form__field--short' : '' ?>">
                            <span class="order-form__visually-hidden"><?= esc($placeholder) ?></span>
                            <input
                                type="<?= $type === 'name' ? 'text' : $type ?>"
                                name="<?= $name ?>"
                                placeholder="<?= esc($placeholder, 'attr') ?>"
                                data-label="<?= esc($label, 'attr') ?>"
                                maxlength="300"
                                autocomplete="<?= ['name' => 'name', 'email' => 'email', 'tel' => 'tel'][$type] ?? 'on' ?>"
                                <?php if ($type === 'tel'): ?>
                                    inputmode="tel"
                                <?php endif ?>
                                <?php if ($isShort): ?>
                                    size="<?= mb_strlen($placeholder) + 2 ?>"
                                <?php endif ?>
                                <?= $required ? 'required' : '' ?>
                            >
                        </label>
                        <?php break ?>

                    <?php case 'form-textarea': ?>
                        <label class="order-form__pill order-form__field order-form__field--textarea">
                            <?php $placeholder = $label . ($required ? '*' : '') ?>
                            <span class="order-form__visually-hidden"><?= esc($placeholder) ?></span>
                            <textarea
                                name="<?= $name ?>"
                                placeholder="<?= esc($placeholder, 'attr') ?>"
                                data-label="<?= esc($label, 'attr') ?>"
                                rows="1"
                                maxlength="5000"
                                <?= $required ? 'required' : '' ?>
                            ></textarea>
                        </label>
                        <?php break ?>

                    <?php case 'form-consent': ?>
                        <label class="order-form__consent">
                            <input type="checkbox" name="<?= $name ?>" value="1" required>
                            <span><?= $block->text()->permalinksToUrls() ?></span>
                        </label>
                        <?php break ?>

                    <?php case 'form-spacer': ?>
                        <div class="order-form__spacer"></div>
                        <?php break ?>
                <?php endswitch ?>
            <?php endforeach ?>

            <button class="order-form__submit" type="submit" disabled data-order-submit>
                <?= esc($shop->bestellButton()->or(t('ui.order_submit'))->value()) ?>
            </button>
        </form>
    <?php endif ?>
</section>
