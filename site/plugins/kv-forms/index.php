<?php

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Page;
use Kirby\Content\Field;
use Kirby\Data\Yaml;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

// Forms of the site. Each one is built in the Panel (tab "Formular" on its
// config page) and opens as an overlay on its pages (snippet `form-overlay`):
//
// - edition:        order form of the editions; config on the shop page
// - katalog:        order form of the catalogues; config on the shop page as
//                   well, in fields prefixed with `katalog`
// - mitgliedschaft: membership application on the kunstverein page;
//                   config on the same page
// - reise:          registration for upcoming trips; config on the reisen page
//
// A form posts to /{lang}/formular/{form}, which validates the input against
// the blocks of the form builder, sends it to the configured recipient plus a
// confirmation to the sender and redirects back to the page with `?formular`,
// where the result is shown instead of the form.

const KV_FORMS_SESSION_RESULT = 'kv.forms.result';
const KV_FORMS_SESSION_TIME = 'kv.forms.time';
// Bots submit right after loading the token
const KV_FORMS_MIN_SECONDS = 3;
// Same rules as in src/js/form-overlay.js
const KV_FORMS_TEL_PATTERN = '#^[0-9+()/ \-]+$#';
const KV_FORMS_TEL_MIN_DIGITS = 5;
const KV_FORMS_BIC_PATTERN = '/^[A-Z]{6}[A-Z0-9]{2}(?:[A-Z0-9]{3})?$/';
const KV_FORMS_NUMBER_MAX = 999;
const KV_FORMS_UPLOAD_MAX_BYTES = 5 * 1024 * 1024;
const KV_FORMS_UPLOAD_TYPES = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
];

// Prefix of the catalogue form's fields on the shop page (`katalogFormular`,
// `katalogFormularTitel`, …), see the blueprint
// `fields/formular-einstellungen-katalog` below
const KV_FORMS_KATALOG_PREFIX = 'katalog';

/**
 * The fields of a form (`formular`, `formularTitel`, … as in
 * fields/formular.yml and fields/formular-einstellungen.yml) on its config
 * page. With a prefix, two forms can be configured on the same page.
 */
final class KvFormConfig
{
    public function __construct(
        public readonly Page $page,
        private readonly string $prefix = ''
    ) {
    }

    public function __call(string $name, array $arguments): Field
    {
        return $this->page->content()->get(
            $this->prefix === '' ? $name : $this->prefix . ucfirst($name)
        );
    }
}

function kvFormsConfigPage(string $id, string $prefix = ''): KvFormConfig|null
{
    $page = page($id);
    return $page ? new KvFormConfig($page, $prefix) : null;
}

/**
 * The forms: where they are configured, on which pages they may be sent
 * from, how those pages are named in the mails and what is shown above the
 * fields
 */
function kvForms(): array
{
    static $forms = null;

    $shopItem = fn (string $template) => fn (Page $page) =>
        $page->parent()?->id() === 'shop' &&
        $page->intendedTemplate()->name() === $template;

    return $forms ??= [
        'edition' => [
            'config'  => fn () => kvFormsConfigPage('shop'),
            'accepts' => $shopItem('edition'),
            'title'   => fn (Page $page) => $page->orderTitle(),
            'intro'   => fn (Page $page) => $page->orderDescription(),
            'heading' => 'ui.order_form',
            'open'    => 'ui.order',
        ],
        'katalog' => [
            'config'  => fn () => kvFormsConfigPage('shop', KV_FORMS_KATALOG_PREFIX),
            'accepts' => $shopItem('katalog'),
            'title'   => fn (Page $page) => $page->orderTitle(),
            'intro'   => fn (Page $page) => $page->orderDescription(),
            'heading' => 'ui.order_form',
            'open'    => 'ui.order',
        ],
        'mitgliedschaft' => [
            'config'  => fn () => kvFormsConfigPage('kunstverein'),
            'accepts' => fn (Page $page) => $page->intendedTemplate()->name() === 'kunstverein',
            'title'   => fn (Page $page) => t('ui.membership'),
            'intro'   => fn (Page $page) => '',
            'heading' => 'ui.form_heading_membership',
            'open'    => 'ui.form_open_membership',
        ],
        'reise' => [
            'config'  => fn () => kvFormsConfigPage('reisen'),
            'accepts' => fn (Page $page) =>
                $page->intendedTemplate()->name() === 'reise' &&
                $page->reiseIsUpcoming(),
            'title'   => fn (Page $page) => implode(', ', array_filter([
                $page->title()->value(),
                $page->reiseStart()->toDate('d.m.Y'),
            ])),
            'intro'   => function (Page $page) {
                $category = $page->category()->value() === 'atelierbesuch' ? t('ui.studio_visit') : t('ui.art_trip');
                $date     = $page->reiseStart()->toDate('d.m.Y');
                return '<p class="form-overlay__intro-title">' . $page->title()->html() . '</p>' .
                    ($date ? '<p>' . esc($category) . ' am ' . esc($date) . '</p>' : '') .
                    $page->anmeldung()->kt();
            },
            'heading' => 'ui.form_heading_travel',
            'open'    => 'ui.registration',
        ],
    ];
}

function kvFormConfig(string $form): KvFormConfig|null
{
    $definition = kvForms()[$form] ?? null;
    return $definition ? $definition['config']() : null;
}

/**
 * IBAN check digits (ISO 13616, mod 97)
 */
function kvFormsIsIban(string $iban): bool
{
    if (preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban) !== 1) {
        return false;
    }

    $rearranged = substr($iban, 4) . substr($iban, 0, 4);
    $remainder  = 0;
    foreach (str_split($rearranged) as $char) {
        $digits = ctype_alpha($char) ? (string)(ord($char) - 55) : $char;
        foreach (str_split($digits) as $digit) {
            $remainder = ($remainder * 10 + (int)$digit) % 97;
        }
    }

    return $remainder === 1;
}

/**
 * Y-m-d (date input) or d.m.Y (typed by hand) to a timestamp
 */
function kvFormsParseDate(string $value): int|null
{
    foreach (['Y-m-d', 'd.m.Y', 'j.n.Y'] as $format) {
        $date = DateTime::createFromFormat('!' . $format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->getTimestamp();
        }
    }
    return null;
}

/**
 * Validates the posted form against the blocks of the form builder. Adds the
 * lines for the mails, errors, the sender's name and email and the uploads to
 * `$order` and returns how many fields were filled in
 */
function kvFormsValidate(Page $page, iterable $blocks, array $input, array $files, array &$order): int
{
    $filled = 0;

    foreach ($blocks as $block) {
        /** @var Block $block */
        $key      = $block->id();
        $label    = $block->label()->value() ?? '';
        $required = $block->required()->toBool();
        $value    = $input[$key] ?? null;
        $error    = function (string $key, array $data = []) use (&$order, $label) {
            $order['errors'][] = tt($key, ['label' => $label] + $data);
        };

        switch ($block->type()) {
            case 'form-group':
                $title = $block->title()->value() ?? '';
                if ($title !== '') {
                    $order['lines'][] = ['heading' => $title];
                }
                $groupFilled = kvFormsValidate($page, $block->blocks()->toBlocks(), $input, $files, $order);
                if ($required && $groupFilled === 0) {
                    $order['errors'][] = tt('ui.form_error_group', ['label' => $title]);
                }
                $filled += $groupFilled;
                break;

            case 'form-editions':
                $images = $page->orderImages()->values();
                if (count($images) === 0) {
                    break;
                }
                $numbers = array_values(array_unique(array_map('intval', (array)($value ?? []))));
                sort($numbers);
                foreach ($numbers as $number) {
                    if ($number < 1 || $number > count($images)) {
                        $error('ui.order_error_invalid');
                        continue 3;
                    }
                }
                if ($numbers === []) {
                    if ($required) {
                        $error('ui.order_error_choose');
                    }
                    break;
                }
                $filled++;
                $order['lines'][] = [
                    'label' => $label,
                    'value' => implode(', ', array_map(
                        fn ($number) => '#' . $number . ' (' . $images[$number - 1]->filename() . ')',
                        $numbers
                    )),
                ];
                break;

            case 'form-prices':
            case 'form-choice':
                $options = $block->type() === 'form-prices'
                    ? array_map(fn ($price) => $price->label()->value(), $page->preise()->toStructure()->values())
                    : $block->options()->split(';');
                if ($options === []) {
                    break;
                }
                if ($value === null || $value === '') {
                    if ($required) {
                        $error('ui.order_error_choose');
                    }
                    break;
                }
                if (!is_string($value) || !ctype_digit($value) || !isset($options[(int)$value])) {
                    $error('ui.order_error_invalid');
                    break;
                }
                $filled++;
                $order['lines'][] = ['label' => $label, 'value' => $options[(int)$value]];
                break;

            case 'form-text':
            case 'form-textarea':
                $value = is_string($value) ? trim($value) : '';
                $type  = $block->type() === 'form-textarea' ? 'textarea' : $block->fieldtype()->or('text')->value();
                $max   = $type === 'textarea' ? 5000 : 300;
                if ($type === 'iban' || $type === 'bic') {
                    $value = strtoupper(preg_replace('/\s+/', '', $value));
                }
                if ($value === '' || ($type === 'number' && $value === '0')) {
                    if ($required) {
                        $error('ui.order_error_required');
                    }
                    break;
                }
                if (Str::length($value) > $max) {
                    $error('ui.order_error_invalid');
                    break;
                }

                $line = ['label' => $label, 'value' => $value];

                switch ($type) {
                    case 'email':
                        if (!V::email($value)) {
                            $error('ui.order_error_email');
                            break 2;
                        }
                        $order['email'] ??= $value;
                        break;
                    case 'name':
                        $order['name'] ??= $value;
                        break;
                    case 'tel':
                        if (
                            preg_match(KV_FORMS_TEL_PATTERN, $value) !== 1 ||
                            preg_match_all('/\d/', $value) < KV_FORMS_TEL_MIN_DIGITS
                        ) {
                            $error('ui.order_error_tel');
                            break 2;
                        }
                        break;
                    case 'number':
                        if (!ctype_digit($value) || (int)$value > KV_FORMS_NUMBER_MAX) {
                            $error('ui.form_error_number');
                            break 2;
                        }
                        break;
                    case 'date':
                        $timestamp = kvFormsParseDate($value);
                        if ($timestamp === null) {
                            $error('ui.form_error_date');
                            break 2;
                        }
                        $minAge = $block->minAge()->toInt();
                        if ($minAge > 0 && $timestamp > strtotime('-' . $minAge . ' years')) {
                            $error('ui.form_error_age', ['age' => $minAge]);
                            break 2;
                        }
                        $line['value'] = date('d.m.Y', $timestamp);
                        break;
                    case 'iban':
                        if (!kvFormsIsIban($value)) {
                            $error('ui.form_error_iban');
                            break 2;
                        }
                        $line['value']     = trim(chunk_split($value, 4, ' '));
                        $line['sensitive'] = true;
                        break;
                    case 'bic':
                        if (preg_match(KV_FORMS_BIC_PATTERN, $value) !== 1) {
                            $error('ui.form_error_bic');
                            break 2;
                        }
                        break;
                }

                $filled++;
                $order['lines'][] = $line;
                break;

            case 'form-consent':
                // Consents of forms built before the toggle existed are required
                $isRequired = $block->required()->or('true')->toBool();
                $isChecked  = empty($value) === false;
                if ($isRequired && !$isChecked) {
                    $order['errors'][] = $block->label()->isNotEmpty()
                        ? tt('ui.form_error_consent_label', ['label' => $label])
                        : t('ui.order_error_consent');
                    break;
                }
                if ($isChecked) {
                    $filled++;
                }
                $order['lines'][] = [
                    'label' => $block->label()->or(Str::short(strip_tags($block->text()->value()), 60))->value(),
                    'value' => $isChecked ? t('ui.form_yes') : t('ui.form_no'),
                ];
                break;

            case 'form-upload':
                $file = [
                    'name'     => $files['name'][$key] ?? null,
                    'tmp_name' => $files['tmp_name'][$key] ?? null,
                    'error'    => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                    'size'     => $files['size'][$key] ?? 0,
                ];
                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                    if ($required) {
                        $error('ui.order_error_required');
                    }
                    break;
                }
                if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $file['size'] > KV_FORMS_UPLOAD_MAX_BYTES) {
                    $error('ui.form_error_upload_size', ['size' => KV_FORMS_UPLOAD_MAX_BYTES / 1024 / 1024]);
                    break;
                }
                if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                    $error('ui.form_error_upload');
                    break;
                }
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                if (!isset(KV_FORMS_UPLOAD_TYPES[$mime])) {
                    $error('ui.form_error_upload_type');
                    break;
                }
                $filename = Str::slug(pathinfo((string)$file['name'], PATHINFO_FILENAME)) ?: 'datei';
                $filename .= '.' . KV_FORMS_UPLOAD_TYPES[$mime];
                $filled++;
                $order['uploads'][] = ['tmp' => $file['tmp_name'], 'name' => $filename];
                $order['lines'][]   = ['label' => $label, 'value' => $filename . ' (' . t('ui.form_attachment') . ')'];
                break;
        }
    }

    return $filled;
}

/**
 * The lines as HTML for the confirmation mail; IBANs are shortened there
 */
function kvFormsLinesHtml(array $lines): string
{
    $html = [];
    foreach ($lines as $line) {
        if (isset($line['heading'])) {
            $html[] = '<br>' . esc(Str::upper($line['heading']));
            continue;
        }
        $value = $line['value'];
        if (!empty($line['sensitive'])) {
            $raw   = str_replace(' ', '', $value);
            $value = Str::substr($raw, 0, 4) . ' … ' . Str::substr($raw, -4);
        }
        $html[] = esc($line['label']) . ': ' . nl2br(esc($value));
    }
    return implode('<br>', $html);
}

/**
 * Stores the result for the next page view and redirects back to the page
 */
function kvFormsFinish(string $form, Page|null $page, string $languageCode, string $status, array $messages = []): Response
{
    $kirby = App::instance();
    $kirby->session()->set(KV_FORMS_SESSION_RESULT, [
        'form'     => $form,
        'status'   => $status,
        'messages' => $messages,
    ]);

    $target = $page ?? $kirby->site()->homePage();

    return Response::redirect($target->url($languageCode) . '?formular', 303);
}

/**
 * Sends the form: first to the configured recipient (without it nothing has
 * happened), then the confirmation to the sender
 */
function kvFormsSend(string $form, KvFormConfig $config, Page $page, Kirby\Cms\Language $language, array $order): array
{
    $kirby    = App::instance();
    $from     = option('kv.forms.from');
    $fromName = option('kv.forms.fromName');
    $title    = kvForms()[$form]['title']($page);
    $heading  = $config->formularTitel()->or(t(kvForms()[$form]['heading']))->value();

    // Uploads go out with their original (cleaned) name
    $attachments = [];
    $tmpDir      = null;
    if (!empty($order['uploads'])) {
        $tmpDir = sys_get_temp_dir() . '/kv-forms-' . bin2hex(random_bytes(8));
        mkdir($tmpDir, 0700);
        foreach ($order['uploads'] as $upload) {
            $path = $tmpDir . '/' . $upload['name'];
            copy($upload['tmp'], $path);
            $attachments[] = $path;
        }
    }

    try {
        $kirby->email([
            'from'        => $from,
            'fromName'    => $fromName,
            'to'          => $config->formularEmpfaenger()->value(),
            'replyTo'     => $order['email'] ?? $from,
            'subject'     => trim($config->formularBetreff()->or($heading)->value() . ': ' . $title),
            'template'    => 'form',
            'attachments' => $attachments,
            'data'        => [
                'heading'  => $heading,
                'title'    => $title,
                'page'     => $page,
                'order'    => $order,
                'language' => $language,
            ],
        ]);
    } finally {
        if ($tmpDir !== null) {
            array_map('unlink', glob($tmpDir . '/*'));
            rmdir($tmpDir);
        }
    }

    $messages = [];
    if ($order['email'] !== null && $config->bestaetigungText()->isNotEmpty()) {
        try {
            $lines = kvFormsLinesHtml($order['lines']);
            $kirby->email([
                'from'     => $from,
                'fromName' => $fromName,
                'to'       => $order['email'],
                'replyTo'  => $config->formularEmpfaenger()->value(),
                'subject'  => $config->bestaetigungBetreff()->or($heading)->value(),
                'template' => 'form-confirmation',
                'data'     => [
                    // {bestellung} is the name used by the first (shop) form
                    'body' => str_replace(
                        ['{name}', '{titel}', '{angaben}', '{bestellung}'],
                        [esc($order['name'] ?? ''), esc($title), $lines, $lines],
                        $config->bestaetigungText()->permalinksToUrls()->value()
                    ),
                ],
            ]);
        } catch (Throwable) {
            $messages[] = t('ui.order_error_confirmation');
        }
    }

    return $messages;
}

App::plugin('kv/forms', [
    'blueprints' => [
        // The settings of the catalogue form: fields/formular-einstellungen
        // with prefixed field names
        'fields/formular-einstellungen-katalog' => function (App $kirby) {
            $blueprint = Yaml::read($kirby->root('blueprints') . '/fields/formular-einstellungen.yml');
            $fields    = [];
            foreach ($blueprint['fields'] as $name => $field) {
                $fields[KV_FORMS_KATALOG_PREFIX . ucfirst($name)] = $field;
            }
            $blueprint['fields'] = $fields;
            return $blueprint;
        },
    ],
    'pageMethods' => [
        // Shop: gallery images, shown as the selectable edition numbers #1, #2, …
        'orderImages' => function () {
            return $this->galerie()->toFiles()->filterBy('type', 'image');
        },
        // Shop: `beschreibung` without its empty spacer paragraphs
        'orderDescription' => function (): string {
            $html = $this->beschreibung()->kt()->value();
            return preg_replace('#<p>(?:\s|&nbsp;|\x{00A0}|<br\s*/?>)*</p>#u', '', $html) ?? $html;
        },
        'orderTitle' => function (): string {
            return implode(', ', array_filter([
                $this->kuenstler()->value(),
                $this->title()->value(),
            ]));
        },
        // Reise: registration is open until the day of the trip (same rule as
        // the "Vorschau" on the reisen page)
        'reiseIsUpcoming' => function (): bool {
            return ($this->reiseStart()->toDate('Y-m-d') ?? '') > date('Y-m-d');
        },
        // Result of the last submission of `$form`, only read on the
        // `?formular` view so regular (cached) page views never start a session
        'formResult' => function (string $form): array|null {
            if (get('formular') === null) {
                return null;
            }
            $result = App::instance()->session()->get(KV_FORMS_SESSION_RESULT);
            if (($result['form'] ?? null) !== $form) {
                return null;
            }
            App::instance()->session()->remove(KV_FORMS_SESSION_RESULT);
            return $result;
        },
    ],

    'routes' => [
        // The pages are cached, so the CSRF token is fetched when a form opens
        [
            'pattern'  => 'formular/token',
            'language' => '*',
            'method'   => 'GET',
            'action'   => function () {
                $kirby = App::instance();
                $kirby->session()->set(KV_FORMS_SESSION_TIME, time());

                return Response::json(['token' => csrf()], 200, null, [
                    'Cache-Control' => 'no-store',
                ]);
            },
        ],
        [
            'pattern'  => 'formular/(:any)',
            'language' => '*',
            'method'   => 'POST',
            'action'   => function ($language, string $form) {
                $kirby        = App::instance();
                $languageCode = $language->code();
                $definition   = kvForms()[$form] ?? null;
                $config       = $definition ? $definition['config']() : null;
                $input        = $kirby->request()->body()->toArray();
                $page         = page((string)($input['page'] ?? ''));

                if ($config === null || $page === null || $definition['accepts']($page) !== true) {
                    return kvFormsFinish($form, $page, $languageCode, 'error', [t('ui.order_error_item')]);
                }

                // CSRF token, honeypot and minimum fill-in time
                $openedAt = $kirby->session()->get(KV_FORMS_SESSION_TIME);
                if (
                    csrf((string)($input['csrf'] ?? '')) !== true ||
                    is_int($openedAt) === false ||
                    time() - $openedAt < KV_FORMS_MIN_SECONDS ||
                    empty($input['website']) === false
                ) {
                    return kvFormsFinish($form, $page, $languageCode, 'error', [t('ui.order_error_session')]);
                }

                $order = ['lines' => [], 'errors' => [], 'uploads' => [], 'name' => null, 'email' => null];
                kvFormsValidate(
                    $page,
                    $config->formular()->toBlocks(),
                    $input['fields'] ?? [],
                    $_FILES['fields'] ?? [],
                    $order
                );
                if ($order['errors'] !== []) {
                    return kvFormsFinish($form, $page, $languageCode, 'error', $order['errors']);
                }

                try {
                    $messages = kvFormsSend($form, $config, $page, $language, $order);
                } catch (Throwable $exception) {
                    $messages = [t('ui.order_error_mail')];
                    if ($kirby->option('debug') === true) {
                        $messages[] = $exception->getMessage();
                    }
                    return kvFormsFinish($form, $page, $languageCode, 'error', $messages);
                }

                // A token is valid for one submission only
                $kirby->session()->remove(KV_FORMS_SESSION_TIME);

                return kvFormsFinish($form, $page, $languageCode, 'success', $messages);
            },
        ],
    ],
]);
