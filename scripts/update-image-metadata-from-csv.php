<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$kirby = new App();
$kirby->impersonate('kirby');

$websiteRoot = dirname(__DIR__);
$projectRoot = dirname($websiteRoot);

function discoverCsvPath(string $projectRoot): string
{
    $preferred = [
        $projectRoot . '/website/assets/images-final-export.csv',
        $projectRoot . '/website/assets/images-export.csv',
        $projectRoot . '/Export/images-export.csv',
        $projectRoot . '/Export/images-final-export.csv',
    ];

    foreach ($preferred as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    $matches = array_merge(
        glob($projectRoot . '/website/assets/*images*.csv') ?: [],
        glob($projectRoot . '/Export/*images*.csv') ?: []
    );
    usort(
        $matches,
        static fn (string $a, string $b): int => filemtime($b) <=> filemtime($a)
    );

    if ($matches !== []) {
        return $matches[0];
    }

    throw new RuntimeException('No images CSV found in website/assets/ or Export/.');
}

function isJunkMetadataValue(string $value): bool
{
    $value = trim($value);

    if ($value === '') {
        return true;
    }

    $patterns = [
        '/^Processed with VSCO\b/i',
        '/^IMG[_\-\s]?\d+/i',
        '/^DSC[_\-\s]?\d+/i',
        '/^Screenshot\b/i',
        '/^OLYMPUS DIGITAL CAMERA$/i',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $value) === 1) {
            return true;
        }
    }

    return false;
}

function csvRows(string $file, string $delimiter = ';'): Generator
{
    $handle = fopen($file, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Cannot open CSV file: {$file}");
    }

    $header = fgetcsv($handle, 0, $delimiter);
    if ($header === false) {
        fclose($handle);
        return;
    }

    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    }

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if ($row === [null]) {
            continue;
        }

        yield array_combine($header, $row) ?: [];
    }

    fclose($handle);
}

function filenameFromImageUrl(string $imageUrl): ?string
{
    $path = parse_url($imageUrl, PHP_URL_PATH);
    $relativePath = ltrim((string)$path, '/');

    if ($relativePath === '') {
        return null;
    }

    $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
    $stem = pathinfo($relativePath, PATHINFO_FILENAME);
    $filename = $stem . '-' . substr(sha1($relativePath), 0, 8);

    if ($extension !== '') {
        $filename .= '.' . $extension;
    }

    return $filename;
}

function collectSitePages(App $kirby): array
{
    $pages = [];

    $walk = static function (Page $page) use (&$walk, &$pages): void {
        $pages[] = $page;
        foreach ($page->childrenAndDrafts() as $child) {
            $walk($child);
        }
    };

    foreach ($kirby->site()->childrenAndDrafts() as $page) {
        $walk($page);
    }

    return $pages;
}

function fileContentForUpdate(array $row): array
{
    $content = [
        'title' => trim((string)($row['title'] ?? '')),
        'caption' => trim((string)($row['caption'] ?? '')),
        'credit' => trim((string)($row['credit'] ?? '')),
    ];

    return array_filter(
        $content,
        static fn (string $value): bool => isJunkMetadataValue($value) === false
    );
}

function needsUpdate(File $file, array $content, string $languageCode): bool
{
    foreach ($content as $field => $value) {
        if ($file->content($languageCode)->get($field)->value() !== $value) {
            return true;
        }
    }

    return false;
}

$csvPath = $argv[1] ?? discoverCsvPath($projectRoot);
$mode = $argv[2] ?? '--write';
$dryRun = $mode === '--dry-run';

if (is_file($csvPath) === false) {
    throw new RuntimeException("CSV file not found: {$csvPath}");
}

$pages = collectSitePages($kirby);
$pagesByFilename = [];

foreach ($pages as $page) {
    foreach ($page->files() as $file) {
        $pagesByFilename[$file->filename()][] = $file;
    }
}

$languageCode = 'de';
$stats = [
    'rows' => 0,
    'with_metadata' => 0,
    'matched_files' => 0,
    'updated_files' => 0,
    'unchanged_files' => 0,
    'missing_files' => 0,
    'ambiguous_matches' => 0,
];

foreach (csvRows($csvPath) as $row) {
    $stats['rows']++;

    $imageUrl = trim((string)($row['image_url'] ?? ''));
    $filename = filenameFromImageUrl($imageUrl);
    $content = fileContentForUpdate($row);

    if ($filename === null || $content === []) {
        continue;
    }

    $stats['with_metadata']++;
    $matches = $pagesByFilename[$filename] ?? [];

    if ($matches === []) {
        $stats['missing_files']++;
        continue;
    }

    if (count($matches) > 1) {
        $stats['ambiguous_matches']++;
    }

    foreach ($matches as $file) {
        $stats['matched_files']++;

        if (needsUpdate($file, $content, $languageCode) === false) {
            $stats['unchanged_files']++;
            continue;
        }

        if ($dryRun === false) {
            $file->update($content, $languageCode);
        }

        $stats['updated_files']++;
    }
}

echo ($dryRun ? "Dry run" : "Update") . " completed.\n";
echo "CSV: {$csvPath}\n";
foreach ($stats as $label => $count) {
    echo $label . ': ' . $count . "\n";
}
