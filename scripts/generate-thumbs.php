<?php

declare(strict_types=1);

// Generates all responsive image thumbs ahead of time, so no visitor has to
// wait for them being created on their first request.
// Uses the same widths/options as the `image` snippet (site/plugins/kv-images).
//
// Usage: php scripts/generate-thumbs.php

use Kirby\Cms\App;
use Kirby\Filesystem\F;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

ini_set('memory_limit', '-1');

$kirby = new App();
$kirby->impersonate('kirby');

$created = 0;
$existing = 0;
$failed = 0;

foreach ($kirby->site()->index() as $page) {
    foreach ($page->images() as $file) {
        if ($file->isResizable() === false) {
            continue;
        }

        foreach ($file->srcsetThumbs() as $thumb) {
            $root = $thumb->root();

            if (is_file($root)) {
                $existing++;
                continue;
            }

            try {
                $thumb->save();
                // Same cleanup as Kirby does after generating a thumb on request
                F::remove(dirname($root) . '/.jobs/' . basename($root) . '.json');
                $created++;
                echo 'Created ' . $page->id() . '/' . basename($root) . "\n";
            } catch (Throwable $e) {
                $failed++;
                F::remove($root);
                fwrite(STDERR, 'Failed ' . $file->id() . ': ' . $e->getMessage() . "\n");
            }
        }
    }
}

echo "\nDone: {$created} created, {$existing} already existed, {$failed} failed.\n";
