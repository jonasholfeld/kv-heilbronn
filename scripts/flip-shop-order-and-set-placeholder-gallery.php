<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Data\Yaml;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

const LANGUAGE_CODE = 'de';
const PLACEHOLDER_FILENAME = 'platzhalter-bild-shop-1000x.png';

/**
 * @return list<Page>
 */
function listedShopChildren(Page $shopPage): array
{
    return $shopPage->children()->listed()->sortBy('num', 'asc')->values();
}

function ensurePlaceholderFile(Page $page, string $sourcePath): array
{
    $existingFile = $page->file(PLACEHOLDER_FILENAME);

    if ($existingFile instanceof File) {
        return [$existingFile, false];
    }

    $createdFile = $page->createFile([
        'source'   => $sourcePath,
        'filename' => PLACEHOLDER_FILENAME,
        'parent'   => $page,
    ]);

    return [$createdFile, true];
}

function updateGalerie(Page $page, string $filename): Page
{
    return $page->update([
        'galerie' => Yaml::encode([$filename]),
    ], LANGUAGE_CODE);
}

$kirby = new App();
$kirby->impersonate('kirby');

$websiteRoot = dirname(__DIR__);
$placeholderPath = $websiteRoot . '/' . PLACEHOLDER_FILENAME;

if (is_file($placeholderPath) === false) {
    fwrite(STDERR, "Placeholder image not found: {$placeholderPath}\n");
    exit(1);
}

$shopPage = $kirby->site()->find('shop');

if ($shopPage === null) {
    fwrite(STDERR, "Shop page not found.\n");
    exit(1);
}

$listedChildren = listedShopChildren($shopPage);

if ($listedChildren === []) {
    echo "Shop has no listed children.\n";
    exit(0);
}

$originalOrder = array_map(static fn (Page $page) => $page->id(), $listedChildren);
$reversedOrder = array_reverse($originalOrder);

$reordered = 0;
$filesCreated = 0;
$filesReused = 0;
$pageErrors = [];

foreach ($reversedOrder as $index => $pageId) {
    try {
        $page = $kirby->page($pageId);

        if ($page === null) {
            throw new RuntimeException("Page not found while reordering: {$pageId}");
        }

        $page = $page->changeSort($index + 1);
        $reordered++;

        [$placeholderFile, $wasCreated] = ensurePlaceholderFile($page, $placeholderPath);

        if ($wasCreated === true) {
            $filesCreated++;
        } else {
            $filesReused++;
        }

        updateGalerie($page, $placeholderFile->filename());
    } catch (Throwable $exception) {
        $pageErrors[] = sprintf('%s: %s', $pageId, $exception->getMessage());
    }
}

echo "Source image: {$placeholderPath}\n";
echo "Shop page: {$shopPage->id()}\n";
echo "Listed children processed: " . count($reversedOrder) . "\n";
echo "Children reordered: {$reordered}\n";
echo "Files created: {$filesCreated}\n";
echo "Files reused: {$filesReused}\n";

if ($pageErrors !== []) {
    echo "Errors:\n";

    foreach ($pageErrors as $error) {
        echo "- {$error}\n";
    }

    exit(1);
}

echo "Done.\n";
