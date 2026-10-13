<?php
/**
 * Fields of a form (see form-overlay), one `.kv-form__item` per block;
 * groups render their own blocks with this snippet again
 *
 * @var \Kirby\Cms\Blocks $blocks
 * @var \Kirby\Cms\Page   $page   page the form is sent from
 */

foreach ($blocks as $block):
    $type     = $block->type();
    $name     = 'fields[' . $block->id() . ']';
    $label    = $block->label()->value() ?? '';
    $required = $block->required()->toBool();
    $marker   = $required ? '*' : '';
    // `short` is the toggle of the first (shop) form
    $width    = $block->width()->or($block->short()->toBool() ? 'auto' : 'full')->value();
?>

<?php if ($type === 'form-group'): ?>
    <?php $title = $block->title()->value() ?? '' ?>
    <div class="kv-form__item kv-form__group" data-label="<?= esc($title, 'attr') ?>" <?= $required ? 'data-required' : '' ?>>
        <?php if ($title !== ''): ?>
            <p class="kv-form__group-title"><?= esc($title) ?><?= $marker ?></p>
        <?php endif ?>
        <?php snippet('form-fields', ['blocks' => $block->blocks()->toBlocks(), 'page' => $page]) ?>
    </div>

<?php elseif ($type === 'form-editions'): ?>
    <?php $images = $page->orderImages() ?>
    <div class="kv-form__item">
        <?php if ($images->isNotEmpty()): ?>
            <fieldset class="kv-form__editions" data-label="<?= esc($label, 'attr') ?>" <?= $required ? 'data-required' : '' ?>>
                <legend class="kv-form__visually-hidden"><?= esc($label) ?></legend>
                <?php if ($block->text()->isNotEmpty()): ?>
                    <p class="kv-form__editions-text"><?= $block->text()->html() ?><?= $marker ?></p>
                <?php endif ?>
                <div class="kv-form__edition-list">
                    <?php foreach ($images->values() as $index => $image): ?>
                        <label class="kv-form__edition">
                            <input type="checkbox" name="<?= $name ?>[]" value="<?= $index + 1 ?>">
                            <span class="kv-form__edition-image">
                                <?php snippet('image', [
                                    'file'  => $image,
                                    'alt'   => $image->alt()->or($page->kuenstler())->value(),
                                    // Three per row on mobile (see _mobile-forms.scss)
                                    'sizes' => '(max-aspect-ratio: 4/5) 30vw, 9vw',
                                ]) ?>
                            </span>
                            <span class="kv-form__edition-number">#<?= $index + 1 ?></span>
                        </label>
                    <?php endforeach ?>
                </div>
                <p
                    class="kv-form__editions-selection"
                    aria-live="polite"
                    data-none="<?= esc(t('ui.order_selected_none'), 'attr') ?>"
                    data-one="<?= esc(t('ui.order_selected_one'), 'attr') ?>"
                    data-many="<?= esc(t('ui.order_selected_many'), 'attr') ?>"
                    data-form-selection
                ><?= t('ui.order_selected_none') ?></p>
            </fieldset>
        <?php endif ?>
        <?php if ($page->bestellHinweis()->isNotEmpty()): ?>
            <div class="kv-form__hint"><?= $page->bestellHinweis()->permalinksToUrls() ?></div>
        <?php endif ?>
    </div>

<?php elseif ($type === 'form-prices' || $type === 'form-choice'): ?>
    <?php
    $options = $type === 'form-prices'
        ? array_values($page->preise()->toStructure()->toArray(fn ($price) => $price->label()->value()))
        : $block->options()->split(';');
    ?>
    <?php if ($options !== []): ?>
        <div class="kv-form__item kv-form__item--<?= $width ?>">
            <fieldset
                class="kv-form__choice<?= $block->stacked()->toBool() ? ' kv-form__choice--stacked' : '' ?>"
                data-label="<?= esc($label, 'attr') ?>"
                <?= $required ? 'data-required' : '' ?>
            >
                <legend class="kv-form__visually-hidden"><?= esc($label) ?></legend>
                <?php foreach ($options as $index => $option): ?>
                    <label class="kv-form__pill kv-form__option">
                        <input
                            type="radio"
                            name="<?= $name ?>"
                            value="<?= $index ?>"
                            <?= $index === 0 && $block->preselect()->toBool() ? 'checked' : '' ?>
                        >
                        <span><?= esc($option) ?></span>
                    </label>
                <?php endforeach ?>
            </fieldset>
        </div>
    <?php endif ?>

<?php elseif ($type === 'form-text'): ?>
    <?php
    $fieldtype   = $block->fieldtype()->or('text')->value();
    $placeholder = $label . $marker;
    $attrs       = [
        'type'         => ['email' => 'email', 'tel' => 'tel'][$fieldtype] ?? 'text',
        'name'         => $name,
        'placeholder'  => $placeholder,
        'data-label'   => $label,
        'data-type'    => $fieldtype,
        'maxlength'    => $fieldtype === 'number' ? 3 : 300,
        'autocomplete' => ['name' => 'name', 'email' => 'email', 'tel' => 'tel'][$fieldtype] ?? 'off',
        'inputmode'    => ['tel' => 'tel', 'number' => 'numeric', 'email' => 'email'][$fieldtype] ?? null,
        'spellcheck'   => in_array($fieldtype, ['iban', 'bic'], true) ? 'false' : null,
        'data-min-age' => $fieldtype === 'date' && $block->minAge()->toInt() > 0 ? $block->minAge()->toInt() : null,
        // Width of auto fields: the placeholder; form-overlay.js measures it
        // exactly, this is the fallback (uppercase is wider than `size` assumes)
        'size'         => $width === 'auto' ? (int)ceil(mb_strlen($placeholder) * 1.4) + 1 : null,
        'required'     => $required,
    ];
    ?>
    <div class="kv-form__item kv-form__item--<?= $width ?>">
        <label class="kv-form__pill kv-form__field">
            <span class="kv-form__visually-hidden"><?= esc($placeholder) ?></span>
            <input <?= attr($attrs) ?>>
        </label>
    </div>

<?php elseif ($type === 'form-textarea'): ?>
    <div class="kv-form__item">
        <label class="kv-form__pill kv-form__field kv-form__field--textarea<?= $block->tall()->toBool() ? ' kv-form__field--tall' : '' ?>">
            <span class="kv-form__visually-hidden"><?= esc($label . $marker) ?></span>
            <textarea <?= attr([
                'name'        => $name,
                'placeholder' => $label . $marker,
                'data-label'  => $label,
                'rows'        => $block->tall()->toBool() ? 6 : 1,
                'maxlength'   => 5000,
                'required'    => $required,
            ]) ?>></textarea>
        </label>
    </div>

<?php elseif ($type === 'form-consent'): ?>
    <?php // Consents of forms built before the toggle existed are required ?>
    <?php $isRequired = $block->required()->or('true')->toBool() ?>
    <div class="kv-form__item">
        <label class="kv-form__consent">
            <input <?= attr([
                'type'       => 'checkbox',
                'name'       => $name,
                'value'      => 1,
                'data-label' => $label,
                'required'   => $isRequired,
            ]) ?>>
            <span><?= $block->text()->permalinksToUrls() ?></span>
        </label>
    </div>

<?php elseif ($type === 'form-upload'): ?>
    <div class="kv-form__item kv-form__item--auto">
        <label class="kv-form__pill kv-form__upload">
            <input <?= attr([
                'type'       => 'file',
                'name'       => $name,
                'accept'     => '.pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png',
                'data-label' => $label,
                'required'   => $required,
            ]) ?>>
            <span data-upload-name data-empty="<?= esc($label . $marker, 'attr') ?>"><?= esc($label . $marker) ?></span>
        </label>
    </div>

<?php elseif ($type === 'form-info'): ?>
    <div class="kv-form__item kv-form__info"><?= $block->text()->permalinksToUrls() ?></div>

<?php elseif ($type === 'form-spacer'): ?>
    <div class="kv-form__item kv-form__spacer"></div>
<?php endif ?>

<?php endforeach ?>
