<?php

declare(strict_types=1);

/**
 * Removes fields with `translate: false` from the content files of the
 * secondary languages (e.g. "Color" in shop/…/katalog.en.txt).
 *
 * Kirby only strips them when a translation is saved; when reading, a value
 * left in a translation file (e.g. from the import) still wins over the
 * default language, so the field shows a different value per language and
 * can't be edited in the secondary language.
 *
 * Dry run by default, pass --write to change the files:
 *   php scripts/strip-untranslatable-fields.php [--write]
 */

use Kirby\Cms\App;
use Kirby\Cms\ModelWithContent;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$write = in_array('--write', $argv, true);

$kirby = new App();
$kirby->impersonate('kirby');

/**
 * @return list<string> Lowercase names of the model's untranslatable fields
 */
function untranslatableFields(ModelWithContent $model): array
{
    $names = [];

    foreach ($model->blueprint()->fields() as $field) {
        if (($field['translate'] ?? true) === false) {
            $names[] = strtolower($field['name']);
        }
    }

    return $names;
}

$models = [$kirby->site(), ...$kirby->site()->index(true)->values()];
$changed = 0;

foreach ($models as $model) {
    $untranslatable = untranslatableFields($model);

    if ($untranslatable === []) {
        continue;
    }

    foreach ($kirby->languages() as $language) {
        if ($language->isDefault() === true) {
            continue;
        }

        $version = $model->version();
        $fields  = $version->read($language);

        if ($fields === null) {
            continue;
        }

        $stale = array_values(array_intersect(array_keys($fields), $untranslatable));

        if ($stale === []) {
            continue;
        }

        $changed++;
        $id = $model instanceof \Kirby\Cms\Site ? 'site' : $model->id();
        echo sprintf("%s (%s): %s\n", $id, $language->code(), implode(', ', $stale));

        if ($write === true) {
            // replace() drops the untranslatable fields before writing
            $version->replace($fields, $language);
        }
    }
}

echo $write
    ? "Cleaned {$changed} translation(s).\n"
    : "{$changed} translation(s) to clean. Run with --write to change them.\n";
