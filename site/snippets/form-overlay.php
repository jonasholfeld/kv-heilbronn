<?php
/**
 * Form overlay (shop order, membership, trip registration), see the kv-forms
 * plugin. The fields are built in the Panel on the form's config page.
 *
 * Opened by any element with `data-form-open`; replaces everything next to
 * the sidebar of the `[data-form-host]` element it is placed in.
 *
 * @var string          $form   key in kvForms()
 * @var \Kirby\Cms\Page $page   page the form is sent from
 * @var array|null      $result result of the last submission, shown instead
 *                              of the form (`?formular` view)
 */

$definition = kvForms()[$form] ?? null;
$config     = $definition ? $definition['config']() : null;
if (!$config) {
    return;
}

$result      = $result ?? null;
$languageUrl = kirby()->language()?->url() ?? kirby()->url();
$heading     = $config->formularTitel()->or(t($definition['heading']))->value();
$intro       = $definition['intro']($page) . $config->formularIntro()->permalinksToUrls()->value();
// The shop item pages are reached from the shop; the others close onto themselves
$closeUrl    = $form === 'shop' ? (page('shop') ?? $page)->url() : $page->url();
$messages    = [
    'required'     => t('ui.order_error_required'),
    'choose'       => t('ui.order_error_choose'),
    'email'        => t('ui.order_error_email'),
    'tel'          => t('ui.order_error_tel'),
    'consent'      => t('ui.order_error_consent'),
    'consentLabel' => t('ui.form_error_consent_label'),
    'number'       => t('ui.form_error_number'),
    'date'         => t('ui.form_error_date'),
    'age'          => t('ui.form_error_age'),
    'iban'         => t('ui.form_error_iban'),
    'bic'          => t('ui.form_error_bic'),
    'group'        => t('ui.form_error_group'),
    'uploadSize'   => tt('ui.form_error_upload_size', ['size' => KV_FORMS_UPLOAD_MAX_BYTES / 1024 / 1024]),
    'uploadType'   => t('ui.form_error_upload_type'),
];
?>
<section class="form-overlay form-overlay--<?= $form ?>" aria-labelledby="form-overlay-title" data-form-overlay>
    <header class="form-overlay__header">
        <h2 id="form-overlay-title" tabindex="-1"><?= esc($heading) ?></h2>
        <?php if ($result): ?>
            <a class="form-overlay__close" href="<?= $closeUrl ?>"><?= t('ui.close') ?></a>
        <?php else: ?>
            <button class="form-overlay__close" type="button" data-form-close><?= t('ui.close') ?></button>
        <?php endif ?>
    </header>

    <?php if ($result): ?>
        <?php $isSuccess = ($result['status'] ?? null) === 'success' ?>
        <div class="form-overlay__message" role="<?= $isSuccess ? 'status' : 'alert' ?>">
            <?php if ($isSuccess): ?>
                <?= $config->formularErfolg()->isNotEmpty() ? $config->formularErfolg()->permalinksToUrls() : '<p>' . t('ui.order_success') . '</p>' ?>
            <?php else: ?>
                <?= $config->formularFehler()->isNotEmpty() ? $config->formularFehler()->permalinksToUrls() : '<p>' . t('ui.order_error') . '</p>' ?>
            <?php endif ?>
            <?php if (!empty($result['messages'])): ?>
                <div class="form-overlay__details">
                    <?php foreach ($result['messages'] as $message): ?>
                        <p><?= esc($message) ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </div>
    <?php else: ?>
        <?php if (trim($intro) !== ''): ?>
            <div class="form-overlay__info"><?= $intro ?></div>
        <?php endif ?>

        <form
            class="kv-form"
            method="post"
            enctype="multipart/form-data"
            action="<?= $languageUrl ?>/formular/<?= $form ?>"
            data-token-url="<?= $languageUrl ?>/formular/token"
            data-messages="<?= esc(json_encode($messages, JSON_UNESCAPED_UNICODE), 'attr') ?>"
            data-upload-max="<?= KV_FORMS_UPLOAD_MAX_BYTES ?>"
            novalidate
            data-form
        >
            <input type="hidden" name="page" value="<?= esc($page->id(), 'attr') ?>">
            <input type="hidden" name="csrf" value="" data-form-csrf>
            <div class="kv-form__honeypot" aria-hidden="true">
                <label><?= t('ui.order_honeypot') ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <?php snippet('form-fields', ['blocks' => $config->formular()->toBlocks(), 'page' => $page]) ?>

            <div class="kv-form__item kv-form__item--submit">
                <button class="kv-form__submit" type="submit" disabled data-form-submit>
                    <?= esc($config->formularButton()->or(t('ui.order_submit'))->value()) ?>
                </button>
            </div>
        </form>
    <?php endif ?>
</section>
