<?php

/**
 * Creates the order form of the catalogues (shop page, tab „Bestellformular
 * Kataloge“) as a copy of the editions' form: the same fields without the
 * edition selection, the same settings. Languages whose catalogue form is
 * already filled are skipped.
 *
 *   php scripts/create-katalog-form.php            # shows what would be done
 *   php scripts/create-katalog-form.php --write    # saves it
 */

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Toolkit\Str;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

const PREFIX = 'katalog';
// Settings of fields/formular-einstellungen.yml (without the line)
const SETTINGS = [
    'formularTitel',
    'formularOeffnen',
    'formularButton',
    'formularIntro',
    'formularEmpfaenger',
    'formularBetreff',
    'formularErfolg',
    'formularFehler',
    'bestaetigungBetreff',
    'bestaetigungText',
];

/**
 * Drops the edition selection and gives every block (also the ones in
 * groups) a new id
 */
function catalogueBlocks(array $blocks): array
{
    $result = [];
    foreach ($blocks as $block) {
        if (($block['type'] ?? null) === 'form-editions') {
            continue;
        }
        $block['id'] = Str::uuid();
        if (is_array($block['content']['blocks'] ?? null)) {
            $block['content']['blocks'] = catalogueBlocks($block['content']['blocks']);
        }
        $result[] = $block;
    }
    return $result;
}

$write = in_array('--write', $argv, true);

$kirby = new App();
$kirby->impersonate('kirby');

$shop = $kirby->page('shop');
if (!$shop instanceof Page) {
    fwrite(STDERR, "Shop page not found.\n");
    exit(1);
}

// Default language first: it holds the untranslated settings (recipient,
// subject)
$languages = $kirby->languages()->sortBy('isDefault', 'desc');

foreach ($languages as $language) {
    $code = $language->code();
    // The language's own fields: what's missing keeps falling back to the
    // default language, as in the editions' form
    $own  = array_change_key_case($shop->version('latest')->read($code) ?? []);

    if (trim((string)($own[strtolower(PREFIX . 'Formular')] ?? '')) !== '') {
        echo "[{$code}] Catalogue form already filled, skipped.\n";
        continue;
    }

    $blocks = json_decode($own['formular'] ?? '', true);
    if (!is_array($blocks) || $blocks === []) {
        echo "[{$code}] Editions form is empty, nothing to copy.\n";
        continue;
    }

    $data = [PREFIX . 'Formular' => json_encode(catalogueBlocks($blocks), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
    foreach (SETTINGS as $field) {
        if (isset($own[strtolower($field)])) {
            $data[PREFIX . ucfirst($field)] = $own[strtolower($field)];
        }
    }

    echo "[{$code}] Catalogue form:\n";
    foreach (catalogueBlocks($blocks) as $block) {
        $label = $block['content']['label'] ?? $block['content']['title'] ?? '';
        echo "  - {$block['type']}" . ($label !== '' ? ": {$label}" : '') . "\n";
    }

    if ($write) {
        $shop = $shop->update($data, $code);
        echo "[{$code}] Saved.\n";
    }
}

if (!$write) {
    echo "\nNothing saved. Run again with --write to save.\n";
}
