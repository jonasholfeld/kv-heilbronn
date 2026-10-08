<?php

use Kirby\Cms\App;
use Kirby\Cms\Block;
use Kirby\Cms\Page;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

// Order form of the shop items (edition, katalog). The form itself is built
// in the Panel on the shop page (tab "Bestellformular"); images, description
// and prices come from the item the visitor ordered from.
//
// The form posts to /{lang}/shop/bestellen, which validates the input, sends
// the order to the shop's recipient plus a confirmation to the customer and
// redirects back to the item with `?bestellung`, where the result is shown
// (see the `order-form` snippet).

const KV_SHOP_ORDER_SESSION = 'kv.shopOrder';
const KV_SHOP_ORDER_TEMPLATES = ['edition', 'katalog'];
// Bots submit right after loading the token
const KV_SHOP_ORDER_MIN_SECONDS = 3;
// Same rules as in src/js/order-form.js
const KV_SHOP_ORDER_TEL_PATTERN = '#^[0-9+()/ \-]+$#';
const KV_SHOP_ORDER_TEL_MIN_DIGITS = 5;

/**
 * Validates the posted form against the blocks of the form builder and
 * returns the order lines plus any errors
 */
function kvShopOrderValidate(Page $item, iterable $blocks, array $input): array
{
    $lines    = [];
    $errors   = [];
    $name     = null;
    $email    = null;
    $images   = $item->orderImages()->values();
    $prices   = $item->preise()->toStructure()->values();

    foreach ($blocks as $block) {
        /** @var Block $block */
        $key      = $block->id();
        $label    = $block->label()->value() ?? '';
        $required = $block->required()->toBool();
        $value    = $input[$key] ?? null;

        switch ($block->type()) {
            case 'form-editions':
                if (count($images) === 0) {
                    break;
                }
                $numbers = array_values(array_unique(array_map('intval', (array)($value ?? []))));
                sort($numbers);
                foreach ($numbers as $number) {
                    if ($number < 1 || $number > count($images)) {
                        $errors[] = tt('ui.order_error_invalid', ['label' => $label]);
                        continue 3;
                    }
                }
                if ($required && $numbers === []) {
                    $errors[] = tt('ui.order_error_choose', ['label' => $label]);
                    break;
                }
                if ($numbers !== []) {
                    $lines[] = [
                        'label' => $label,
                        'value' => implode(', ', array_map(
                            fn ($number) => '#' . $number . ' (' . $images[$number - 1]->filename() . ')',
                            $numbers
                        )),
                    ];
                }
                break;

            case 'form-prices':
            case 'form-choice':
                $options = $block->type() === 'form-prices'
                    ? array_map(fn ($price) => $price->label()->value(), $prices)
                    : $block->options()->split();
                if ($options === []) {
                    break;
                }
                if ($value === null || $value === '') {
                    if ($required) {
                        $errors[] = tt('ui.order_error_choose', ['label' => $label]);
                    }
                    break;
                }
                if (!is_string($value) || !ctype_digit($value) || !isset($options[(int)$value])) {
                    $errors[] = tt('ui.order_error_invalid', ['label' => $label]);
                    break;
                }
                $lines[] = ['label' => $label, 'value' => $options[(int)$value]];
                break;

            case 'form-text':
            case 'form-textarea':
                $value = is_string($value) ? trim($value) : '';
                $max   = $block->type() === 'form-textarea' ? 5000 : 300;
                if ($value === '') {
                    if ($required) {
                        $errors[] = tt('ui.order_error_required', ['label' => $label]);
                    }
                    break;
                }
                if (Str::length($value) > $max) {
                    $errors[] = tt('ui.order_error_invalid', ['label' => $label]);
                    break;
                }
                $type = $block->fieldtype()->value();
                if ($type === 'email') {
                    if (!V::email($value)) {
                        $errors[] = tt('ui.order_error_email', ['label' => $label]);
                        break;
                    }
                    $email ??= $value;
                }
                if (
                    $type === 'tel' &&
                    (preg_match(KV_SHOP_ORDER_TEL_PATTERN, $value) !== 1 || preg_match_all('/\d/', $value) < KV_SHOP_ORDER_TEL_MIN_DIGITS)
                ) {
                    $errors[] = tt('ui.order_error_tel', ['label' => $label]);
                    break;
                }
                if ($type === 'name') {
                    $name ??= $value;
                }
                $lines[] = ['label' => $label, 'value' => $value];
                break;

            case 'form-consent':
                if (empty($value)) {
                    $errors[] = t('ui.order_error_consent');
                }
                break;
        }
    }

    return compact('lines', 'errors', 'name', 'email');
}

/**
 * Replaces the placeholders of the confirmation mail text (writer HTML)
 */
function kvShopOrderConfirmationHtml(string $html, Page $item, array $order): string
{
    $summary = implode('<br>', array_map(
        fn ($line) => esc($line['label']) . ': ' . nl2br(esc($line['value'])),
        $order['lines']
    ));

    return str_replace(
        ['{name}', '{titel}', '{bestellung}'],
        [esc($order['name'] ?? ''), esc($item->orderTitle()), $summary],
        $html
    );
}

/**
 * Stores the result for the next page view and redirects back to the item
 */
function kvShopOrderFinish(Page|null $item, string $languageCode, string $status, array $messages = []): Response
{
    $kirby = App::instance();
    $kirby->session()->set(KV_SHOP_ORDER_SESSION, [
        'status'   => $status,
        'messages' => $messages,
    ]);

    $target = $item ?? page('shop') ?? $kirby->site()->homePage();

    return Response::redirect($target->url($languageCode) . '?bestellung', 303);
}

App::plugin('kv/shop-order', [
    'pageMethods' => [
        // Gallery images, shown as the selectable edition numbers #1, #2, …
        'orderImages' => function () {
            return $this->galerie()->toFiles()->filterBy('type', 'image');
        },
        // `beschreibung` without its empty spacer paragraphs
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
        // Result of the last submission, only read on the `?bestellung` view so
        // regular (cached) page views never start a session
        'orderResult' => function (): array|null {
            if (get('bestellung') === null) {
                return null;
            }
            return App::instance()->session()->pull(KV_SHOP_ORDER_SESSION);
        },
    ],

    'routes' => [
        // The pages are cached, so the CSRF token is fetched when the form opens
        [
            'pattern'  => 'shop/bestellen/token',
            'language' => '*',
            'method'   => 'GET',
            'action'   => function () {
                $kirby = App::instance();
                $kirby->session()->set(KV_SHOP_ORDER_SESSION . '.time', time());

                return Response::json(['token' => csrf()], 200, null, [
                    'Cache-Control' => 'no-store',
                ]);
            },
        ],
        [
            'pattern'  => 'shop/bestellen',
            'language' => '*',
            'method'   => 'POST',
            'action'   => function ($language) {
                $kirby        = App::instance();
                $languageCode = $language->code();
                $input        = $kirby->request()->body()->toArray();
                $shop         = page('shop');
                $item         = page((string)($input['item'] ?? ''));

                if (
                    $shop === null ||
                    $item === null ||
                    $item->parent()?->is($shop) !== true ||
                    in_array($item->intendedTemplate()->name(), KV_SHOP_ORDER_TEMPLATES, true) === false
                ) {
                    return kvShopOrderFinish(null, $languageCode, 'error', [t('ui.order_error_item')]);
                }

                // CSRF token, honeypot and minimum fill-in time
                $openedAt = $kirby->session()->get(KV_SHOP_ORDER_SESSION . '.time');
                if (
                    csrf((string)($input['csrf'] ?? '')) !== true ||
                    is_int($openedAt) === false ||
                    time() - $openedAt < KV_SHOP_ORDER_MIN_SECONDS
                ) {
                    return kvShopOrderFinish($item, $languageCode, 'error', [t('ui.order_error_session')]);
                }
                if (empty($input['website']) === false) {
                    return kvShopOrderFinish($item, $languageCode, 'error', [t('ui.order_error_session')]);
                }

                $order = kvShopOrderValidate($item, $shop->bestellformular()->toBlocks(), $input['fields'] ?? []);
                if ($order['errors'] !== []) {
                    return kvShopOrderFinish($item, $languageCode, 'error', $order['errors']);
                }

                $from     = option('kv.shopOrder.from');
                $fromName = option('kv.shopOrder.fromName');

                // The order itself: without it nothing has happened
                try {
                    $kirby->email([
                        'from'     => $from,
                        'fromName' => $fromName,
                        'to'       => $shop->bestellEmpfaenger()->value(),
                        'replyTo'  => $order['email'] ?? $from,
                        'subject'  => trim($shop->bestellBetreff()->or('Neue Bestellung')->value() . ': ' . $item->orderTitle()),
                        'template' => 'shop-order',
                        'data'     => [
                            'item'     => $item,
                            'order'    => $order,
                            'language' => $language,
                        ],
                    ]);
                } catch (Throwable $exception) {
                    $messages = [t('ui.order_error_mail')];
                    if ($kirby->option('debug') === true) {
                        $messages[] = $exception->getMessage();
                    }
                    return kvShopOrderFinish($item, $languageCode, 'error', $messages);
                }

                // The confirmation: the order is in either way, so a failure here
                // is only mentioned below the success message
                $messages = [];
                if ($order['email'] !== null && $shop->bestaetigungText()->isNotEmpty()) {
                    try {
                        $kirby->email([
                            'from'     => $from,
                            'fromName' => $fromName,
                            'to'       => $order['email'],
                            'replyTo'  => $shop->bestellEmpfaenger()->value(),
                            'subject'  => $shop->bestaetigungBetreff()->or($item->orderTitle())->value(),
                            'template' => 'shop-order-confirmation',
                            'data'     => [
                                'body' => kvShopOrderConfirmationHtml(
                                    $shop->bestaetigungText()->value(),
                                    $item,
                                    $order
                                ),
                            ],
                        ]);
                    } catch (Throwable) {
                        $messages[] = t('ui.order_error_confirmation');
                    }
                }

                // A token is valid for one order only
                $kirby->session()->remove(KV_SHOP_ORDER_SESSION . '.time');

                return kvShopOrderFinish($item, $languageCode, 'success', $messages);
            },
        ],
    ],
]);
