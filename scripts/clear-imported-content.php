<?php

declare(strict_types=1);

use Kirby\Cms\App;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$kirby = new App();
$kirby->impersonate('kirby');

$sectionSlugs = ['ausstellungen', 'reisen', 'termine', 'shop', 'künstler', 'kuenstler'];
$deleted = 0;

foreach ($sectionSlugs as $slug) {
    $section = $kirby->site()->find($slug);

    if ($section === null) {
        echo "Section not found, skipping: {$slug}\n";
        continue;
    }

    foreach ($section->childrenAndDrafts() as $child) {
        $childTitle = $child->title()->value();
        $childId = $child->id();
        $child->delete(true);
        $deleted++;
        echo "Deleted: {$childId}";

        if ($childTitle !== '') {
            echo " ({$childTitle})";
        }

        echo "\n";
    }
}

echo "Deleted child pages: {$deleted}\n";
