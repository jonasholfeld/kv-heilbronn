<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Filesystem\F;

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
        '/^Bildunterschrift$/i',
        // single tokens like file names or numbers ("369", "05_documenta_…")
        '/^[\w\-.]+$/u',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $value) === 1) {
            return true;
        }
    }

    return false;
}

function normalizeMetadataValue(?string $value): string
{
    $value = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = strip_tags($value);
    $value = preg_replace('/\s+/u', ' ', $value);
    return trim((string)$value);
}

function preferredMetadataValue(array $row, array $candidates): string
{
    foreach ($candidates as $column) {
        $value = normalizeMetadataValue($row[$column] ?? '');
        if ($value === '' || isJunkMetadataValue($value) === true) {
            continue;
        }

        return $value;
    }

    return '';
}

function csvRows(string $file, string $delimiter = ';'): Generator
{
    $handle = fopen($file, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Cannot open CSV file: {$file}");
    }

    $header = fgetcsv($handle, 0, $delimiter, '"', '\\');
    if ($header === false) {
        fclose($handle);
        return;
    }

    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    }

    while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
        if ($row === [null]) {
            continue;
        }

        yield array_combine($header, $row) ?: [];
    }

    fclose($handle);
}

function writeCsv(string $path, array $rows, array $header): void
{
    $handle = fopen($path, 'wb');
    if ($handle === false) {
        throw new RuntimeException("Cannot write CSV file: {$path}");
    }

    fputcsv($handle, $header, ';', '"', '\\');

    foreach ($rows as $row) {
        $ordered = [];
        foreach ($header as $column) {
            $ordered[] = $row[$column] ?? '';
        }
        fputcsv($handle, $ordered, ';', '"', '\\');
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

/**
 * All usable metadata values of a CSV row, in display order. post_excerpt is
 * the WordPress caption ("Beschriftung"), but most images keep their caption
 * in post_content ("Beschreibung"), so both are used.
 */
function csvMetadataValues(array $row): array
{
    $values = [];

    foreach (['wp_post_excerpt', 'caption', 'wp_post_content', 'credit'] as $column) {
        $value = normalizeMetadataValue($row[$column] ?? '');
        if (isJunkMetadataValue($value) === false) {
            $values[] = $value;
        }
    }

    return $values;
}

function comparableText(string $value): string
{
    $value = mb_strtolower(normalizeMetadataValue($value));
    return trim((string)preg_replace('/[\s.,;:]+$/u', '', $value));
}

/**
 * Joins values into one credit text, one value per line. Values that are
 * empty, junk, duplicates or contained in a longer value are dropped.
 */
function mergeCreditValues(array $values): string
{
    $lines = [];

    foreach ($values as $value) {
        foreach (preg_split('/\R/u', (string)$value) as $line) {
            $line = trim($line);
            if ($line !== '' && isJunkMetadataValue($line) === false) {
                $lines[] = $line;
            }
        }
    }

    $kept = [];
    foreach ($lines as $index => $line) {
        $comparable = comparableText($line);

        foreach ($lines as $otherIndex => $other) {
            if ($otherIndex === $index) {
                continue;
            }

            $otherComparable = comparableText($other);
            $isContained = $comparable !== $otherComparable && str_contains($otherComparable, $comparable);
            $isEarlierDuplicate = $comparable === $otherComparable && $otherIndex < $index;

            if ($isContained || $isEarlierDuplicate) {
                continue 2;
            }
        }

        $kept[] = $line;
    }

    return implode("\n", $kept);
}

/**
 * Title and caption are merged into the single credit field, together with
 * the CSV values, so that nothing entered in the Panel gets lost.
 */
function fileContentForUpdate(File $file, string $languageCode, array $csvValues): array
{
    $content = $file->content($languageCode);

    return [
        'title' => '',
        'caption' => '',
        'credit' => mergeCreditValues([
            ...$csvValues,
            $content->get('title')->value(),
            $content->get('caption')->value(),
            $content->get('credit')->value(),
        ]),
    ];
}

function needsUpdate(File $file, array $content, string $languageCode): bool
{
    foreach ($content as $field => $value) {
        if ((string)$file->content($languageCode)->get($field)->value() !== $value) {
            return true;
        }
    }

    return false;
}

/**
 * Kirby stores uploads under a sanitized (lowercase) name, so lookups use
 * the same sanitizing. The second key ignores the hash suffix for files that
 * were imported without one.
 */
function lookupKeys(string $filename): array
{
    $safeName = F::safeName($filename);
    $extension = F::extension($safeName);
    $stem = F::name($safeName);

    return [
        $safeName,
        preg_replace('/-[0-9a-f]{8}$/', '', $stem) . ($extension !== '' ? '.' . $extension : ''),
    ];
}

function languagesWithOwnContent(File $file, App $kirby): array
{
    $codes = [];

    foreach ($kirby->languages() as $language) {
        if ($language->isDefault() || is_file($file->root() . '.' . $language->code() . '.txt')) {
            $codes[] = $language->code();
        }
    }

    return $codes;
}

function fileIdList(array $files): string
{
    return implode('|', array_map(
        static fn (File $file): string => $file->id(),
        $files
    ));
}

function uniqueFilesByRoot(array $files): array
{
    $unique = [];

    foreach ($files as $file) {
        $id = $file->id();
        $segments = explode('/', $id);

        if (isset($segments[0])) {
            $segments[0] = preg_replace('/^\d+_/', '', $segments[0]);
        }

        $normalizedId = implode('/', $segments);
        $key = $file->root() . '|' . $normalizedId;
        $unique[$key] = $file;
    }

    return array_values($unique);
}

$csvPath = $argv[1] ?? discoverCsvPath($projectRoot);
$mode = $argv[2] ?? '--write';
$dryRun = $mode === '--dry-run';

if (is_file($csvPath) === false) {
    throw new RuntimeException("CSV file not found: {$csvPath}");
}

$pages = collectSitePages($kirby);
$filesByKey = [];
$allFiles = [];

foreach ($pages as $page) {
    foreach ($page->files() as $file) {
        $allFiles[$file->id()] = $file;
        foreach (array_unique(lookupKeys($file->filename())) as $key) {
            $filesByKey[$key][] = $file;
        }
    }
}

$languageCode = $kirby->defaultLanguage()?->code() ?? 'de';
$stats = [
    'rows' => 0,
    'with_metadata' => 0,
    'matched_rows' => 0,
    'missing_files' => 0,
    'ambiguous_matches' => 0,
    'files' => count($allFiles),
    'updated_files' => 0,
    'unchanged_files' => 0,
];
$debugDir = $websiteRoot . '/assets/image-metadata-debug';
$missingRows = [];
$ambiguousRows = [];
$matchedRows = [];
$csvValuesByFileId = [];

foreach (csvRows($csvPath) as $row) {
    $stats['rows']++;

    $imageUrl = trim((string)($row['image_url'] ?? ''));
    $filename = filenameFromImageUrl($imageUrl);
    $csvValues = csvMetadataValues($row);

    if ($filename === null || $csvValues === []) {
        continue;
    }

    $stats['with_metadata']++;
    $matches = [];
    foreach (lookupKeys($filename) as $key) {
        $matches = uniqueFilesByRoot($filesByKey[$key] ?? []);
        if ($matches !== []) {
            break;
        }
    }

    $debugRow = [
        'attachment_id' => trim((string)($row['attachment_id'] ?? '')),
        'image_url' => $imageUrl,
        'expected_filename' => $filename,
        'credit' => mergeCreditValues($csvValues),
    ];

    if ($matches === []) {
        $stats['missing_files']++;
        $missingRows[] = $debugRow;
        continue;
    }

    $stats['matched_rows']++;

    if (count($matches) > 1) {
        $stats['ambiguous_matches']++;
        $ambiguousRows[] = $debugRow + [
            'match_count' => (string)count($matches),
            'matched_file_ids' => fileIdList($matches),
        ];
    }

    foreach ($matches as $file) {
        $csvValuesByFileId[$file->id()] = [...($csvValuesByFileId[$file->id()] ?? []), ...$csvValues];
    }
}

foreach ($allFiles as $id => $file) {
    $updated = false;
    // in WordPress, shop images carry the whole product text incl. prices
    $usesCsv = str_starts_with($file->page()->id(), 'shop/') === false;

    foreach (languagesWithOwnContent($file, $kirby) as $code) {
        $csvValues = $usesCsv && $code === $languageCode ? ($csvValuesByFileId[$id] ?? []) : [];
        $content = fileContentForUpdate($file, $code, $csvValues);

        if (needsUpdate($file, $content, $code) === false) {
            continue;
        }

        $matchedRows[] = [
            'file_id' => $id,
            'language' => $code,
            'from_csv' => $csvValues !== [] ? 'yes' : 'no',
            'old_title' => (string)$file->content($code)->get('title')->value(),
            'old_caption' => (string)$file->content($code)->get('caption')->value(),
            'old_credit' => (string)$file->content($code)->get('credit')->value(),
            'new_credit' => $content['credit'],
        ];

        if ($dryRun === false) {
            $file = $file->update($content, $code);
        }

        $updated = true;
    }

    $stats[$updated ? 'updated_files' : 'unchanged_files']++;
}

if (is_dir($debugDir) === false) {
    mkdir($debugDir, 0777, true);
}

$missingPath = $debugDir . '/missing-image-metadata-matches.csv';
$ambiguousPath = $debugDir . '/ambiguous-image-metadata-matches.csv';
$matchedPath = $debugDir . '/changed-files.csv';

writeCsv($missingPath, $missingRows, ['attachment_id', 'image_url', 'expected_filename', 'credit']);
writeCsv($ambiguousPath, $ambiguousRows, ['attachment_id', 'image_url', 'expected_filename', 'match_count', 'matched_file_ids', 'credit']);
writeCsv($matchedPath, $matchedRows, ['file_id', 'language', 'from_csv', 'old_title', 'old_caption', 'old_credit', 'new_credit']);

echo ($dryRun ? "Dry run" : "Update") . " completed.\n";
echo "CSV: {$csvPath}\n";
foreach ($stats as $label => $count) {
    echo $label . ': ' . $count . "\n";
}
echo "debug_missing: {$missingPath}\n";
echo "debug_ambiguous: {$ambiguousPath}\n";
echo "debug_changed: {$matchedPath}\n";
