<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Str;

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

$normalizedDir = $argv[1] ?? $projectRoot . '/Export/2604/normalized';
$downloadsDir = $argv[2] ?? $projectRoot . '/Export/2604/downloads';

if (is_dir($normalizedDir) === false) {
    throw new RuntimeException("Normalized CSV directory not found: {$normalizedDir}");
}

if (is_dir($downloadsDir) === false) {
    throw new RuntimeException("Downloads directory not found: {$downloadsDir}");
}

function csvRows(string $file): Generator
{
    $handle = fopen($file, 'rb');

    if ($handle === false) {
        throw new RuntimeException("Cannot open CSV file: {$file}");
    }

    $header = fgetcsv($handle);

    if ($header === false) {
        fclose($handle);
        return;
    }

    if (isset($header[0])) {
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$header[0]);
    }

    while (($row = fgetcsv($handle)) !== false) {
        if ($row === [null] || $row === false) {
            continue;
        }

        yield array_combine($header, $row) ?: [];
    }

    fclose($handle);
}

function splitPipe(?string $value): array
{
    $parts = array_map('trim', explode('|', (string)$value));
    return array_values(array_filter($parts, static fn ($item) => $item !== ''));
}

function parseDate(?string $value): string
{
    $value = trim((string)$value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d{8}$/', $value) === 1) {
        return substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
    }

    return $value;
}

function parseTags(?string $value): string
{
    $value = trim((string)$value);

    if ($value === '') {
        return '';
    }

    $parts = preg_split('/[|,;]+/', $value) ?: [];
    $parts = array_map('trim', $parts);
    $parts = array_values(array_filter($parts, static fn ($item) => $item !== ''));

    return implode(', ', $parts);
}

function parseTimeFromTitle(string $title): string
{
    if (preg_match('/\b(\d{1,2})(?:[.:](\d{1,2}))?\s*uhr\b/i', $title, $matches) !== 1) {
        return '';
    }

    $hours = max(0, min(23, (int)$matches[1]));
    $minutes = isset($matches[2]) ? (int)$matches[2] : 0;
    $minutes = max(0, min(59, $minutes));

    if ($minutes > 30) {
        $minutes = 30;
    } elseif ($minutes > 0 && $minutes < 30) {
        $minutes = 30;
    }

    return sprintf('%02d:%02d', $hours, $minutes);
}

function hasHtml(string $value): bool
{
    return preg_match('/<\/?[a-zA-Z][^>]*>/', $value) === 1;
}

function hasBlockHtml(string $value): bool
{
    return preg_match('/<(p|ul|ol|li|blockquote|h[1-6]|hr|pre|table|figure|div)\b/i', $value) === 1;
}

function replaceTextNodeLineBreaks(DOMNode $node, DOMDocument $document): void
{
    for ($child = $node->firstChild; $child !== null; $child = $next) {
        $next = $child->nextSibling;

        if ($child instanceof DOMText) {
            if (str_contains($child->nodeValue, "\n") === false) {
                continue;
            }

            $segments = preg_split('/\n/', $child->nodeValue) ?: [];
            $fragment = $document->createDocumentFragment();

            foreach ($segments as $index => $segment) {
                if ($segment !== '') {
                    $fragment->appendChild($document->createTextNode($segment));
                }

                if ($index < count($segments) - 1) {
                    $fragment->appendChild($document->createElement('br'));
                }
            }

            $child->parentNode?->replaceChild($fragment, $child);
            continue;
        }

        if ($child instanceof DOMElement && in_array(strtolower($child->tagName), ['pre', 'code'], true) === true) {
            continue;
        }

        replaceTextNodeLineBreaks($child, $document);
    }
}

function normalizeHtmlLineBreaks(string $value): string
{
    $document = new DOMDocument('1.0', 'UTF-8');
    $wrapperId = 'kirby-import-wrapper';

    libxml_use_internal_errors(true);
    $document->loadHTML(
        '<?xml encoding="utf-8" ?><div id="' . $wrapperId . '">' . $value . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $wrapper = $document->getElementById($wrapperId);

    if ($wrapper === null) {
        return $value;
    }

    replaceTextNodeLineBreaks($wrapper, $document);

    $html = '';
    foreach ($wrapper->childNodes as $child) {
        $html .= $document->saveHTML($child);
    }

    return $html;
}

function wrapParagraphs(string $value): string
{
    $paragraphs = preg_split('/\n\s*\n/', $value) ?: [];
    $paragraphs = array_map('trim', $paragraphs);
    $paragraphs = array_values(array_filter($paragraphs, static fn ($item) => $item !== ''));

    $html = array_map(
        static function (string $paragraph): string {
            return '<p>' . str_replace("\n", "<br>\n", $paragraph) . '</p>';
        },
        $paragraphs
    );

    return implode("\n\n", $html);
}

function writerHtml(?string $value): string
{
    $value = str_replace(["\r\n", "\r"], "\n", trim((string)$value));

    if ($value === '') {
        return '';
    }

    if (hasHtml($value) === true) {
        if (hasBlockHtml($value) === true) {
            return normalizeHtmlLineBreaks($value);
        }

        return wrapParagraphs($value);
    }

    return wrapParagraphs(htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8'));
}

function yamlList(array $values): string
{
    $values = array_values(array_filter($values, static fn ($item) => $item !== ''));

    if ($values === []) {
        return '';
    }

    return Yaml::encode($values);
}

function splitEnglishDescription(string $value): array
{
    $document = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="utf-8" ?><div id="split-root">' . $value . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $document->getElementById('split-root');
    if ($root === null) {
        return [$value, ''];
    }

    $englishChunks = [];
    $xpath = new DOMXPath($document);
    foreach ($xpath->query('//em') as $node) {
        if (!($node instanceof DOMElement)) {
            continue;
        }

        $text = trim(preg_replace('/\s+/u', ' ', $node->textContent ?? '') ?? '');
        if ($text === '') {
            continue;
        }

        $lower = ' ' . mb_strtolower($text) . ' ';
        $englishHints = [' the ', ' and ', ' of ', ' at ', ' with ', ' from ', ' for ', ' in ', ' to ', ' is ', ' are ', ' this ', ' that '];
        $hintCount = 0;
        foreach ($englishHints as $hint) {
            if (str_contains($lower, $hint)) {
                $hintCount++;
            }
        }

        if ($hintCount < 2) {
            continue;
        }

        $chunk = '';
        foreach ($node->childNodes as $child) {
            $chunk .= $document->saveHTML($child);
        }
        $englishChunks[] = $chunk;
        $node->parentNode?->removeChild($node);
    }

    $german = '';
    foreach ($root->childNodes as $child) {
        $german .= $document->saveHTML($child);
    }

    $english = implode("\n\n", array_values(array_filter($englishChunks, static fn ($item) => trim($item) !== '')));
    return [trim($german), trim($english)];
}

function mediaMetadataByUrl(string $normalizedDir): array
{
    $manifestFile = $normalizedDir . '/media-manifest.csv';

    if (is_file($manifestFile) === false) {
        return [];
    }

    $metadata = [];
    foreach (csvRows($manifestFile) as $row) {
        $imageUrl = trim((string)($row['image_url'] ?? ''));
        if ($imageUrl === '') {
            continue;
        }

        $metadata[$imageUrl] = [
            'title' => trim((string)($row['image_title'] ?? '')),
            'caption' => trim((string)($row['image_caption'] ?? '')),
            'credit' => trim((string)($row['image_credit'] ?? '')),
        ];
    }

    return $metadata;
}

function normalizedCsvPath(string $normalizedDir, string $prefix): string
{
    $matches = glob($normalizedDir . '/' . $prefix . '*.csv') ?: [];
    $matches = array_values(array_filter($matches, static fn ($path) => str_contains(basename($path), 'normalized') === true));
    if ($matches === []) {
        throw new RuntimeException("Normalized CSV not found for prefix: {$prefix}");
    }

    return $matches[0];
}

function legacyImageSources(array $row): string
{
    $keys = [
        'Image URL',
        'bilder',
        'ausstellungsbilder',
        'kunstreise_bilder',
        'all_image_urls',
    ];

    $lines = [];

    foreach ($keys as $key) {
        $value = trim((string)($row[$key] ?? ''));
        if ($value !== '') {
            $lines[] = $key . ': ' . $value;
        }
    }

    return implode("\n", $lines);
}

function ensureSectionPage(App $kirby, string $slug, string $template, string $title, int $position): Page
{
    $page = $kirby->site()->find($slug);

    if ($page === null) {
        $page = $kirby->site()->createChild([
            'slug'     => $slug,
            'template' => $template,
            'draft'    => false,
            'translations' => [
                [
                    'code'    => 'de',
                    'content' => [
                        'title' => $title,
                        'intro' => '',
                    ],
                ],
            ],
            'content'  => [
                'title' => $title,
                'intro' => '',
            ],
        ]);
    } else {
        $page = $page->update([
            'title' => $title,
            'intro' => $page->content()->get('intro')->value(),
        ], 'de');
    }

    if ($page->isListed() === false || $page->num() !== $position) {
        $page = $page->changeStatus('listed', $position);
    }

    return $page;
}

function ensureChildPage(Page $parent, string $slug, string $template, array $content): Page
{
    $page = $parent->children()->find($slug);

    if ($page === null) {
        $page = $parent->createChild([
            'slug'     => $slug,
            'template' => $template,
            'draft'    => false,
            'translations' => [
                [
                    'code'    => 'de',
                    'content' => $content,
                ],
            ],
            'content'  => $content,
        ]);
    } else {
        $page = $page->update($content, 'de');
    }

    if ($page->isListed() === false) {
        $page = $page->changeStatus('listed');
    }

    return $page;
}

function ensureFiles(Page $page, array $imageUrls, string $downloadsDir, array $fileMetadataByUrl = []): array
{
    $filenames = [];

    foreach ($imageUrls as $imageUrl) {
        $path = parse_url($imageUrl, PHP_URL_PATH);
        $relativePath = ltrim((string)$path, '/');

        if ($relativePath === '') {
            continue;
        }

        $source = $downloadsDir . '/' . $relativePath;

        if (is_file($source) === false) {
            continue;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $stem = pathinfo($source, PATHINFO_FILENAME);
        $filename = $stem . '-' . substr(sha1($relativePath), 0, 8);
        if ($extension !== '') {
            $filename .= '.' . $extension;
        }
        $content = array_filter(
            [
                'title' => trim((string)($fileMetadataByUrl[$imageUrl]['title'] ?? '')),
                'caption' => trim((string)($fileMetadataByUrl[$imageUrl]['caption'] ?? '')),
                'credit' => trim((string)($fileMetadataByUrl[$imageUrl]['credit'] ?? '')),
            ],
            static fn ($item) => $item !== ''
        );

        if ($page->file($filename) === null) {
            $page->createFile([
                'filename' => $filename,
                'source'   => $source,
                'parent'   => $page,
                'translations' => $content !== [] ? [
                    [
                        'code'    => 'de',
                        'content' => $content,
                    ],
                ] : [],
                'content'  => $content,
            ]);
        } elseif ($content !== []) {
            $page->file($filename)?->update($content, 'de');
        }

        if (in_array($filename, $filenames, true) === false) {
            $filenames[] = $filename;
        }
    }

    return $filenames;
}

function exhibitionContent(array $row, array $files): array
{
    [$germanDescription, $englishDescription] = splitEnglishDescription(writerHtml($row['beschreibung'] ?? ''));

    return [
        'title'             => trim((string)($row['Title'] ?? '')),
        'wordpressId'       => trim((string)($row['id'] ?? '')),
        'kuenstler'         => trim((string)($row['kunstler'] ?? '')),
        'jahr'              => trim((string)($row['jahr'] ?? '')),
        'eroffnungsdatum'   => parseDate($row['eroffnungsdatum'] ?? ''),
        'startdatum'        => parseDate($row['startdatum'] ?? ''),
        'enddatum'          => parseDate($row['enddatum'] ?? ''),
        'beschreibung'      => $germanDescription,
        'galerie'           => yamlList($files),
        'legacyBildquellen' => legacyImageSources($row),
    ];
}

function exhibitionContentEnglish(array $row): string
{
    [, $englishDescription] = splitEnglishDescription(writerHtml($row['beschreibung'] ?? ''));
    return $englishDescription;
}

function travelContent(array $row, array $files): array
{
    $isAtelier = array_key_exists('atelierbesuche_beschreibung', $row);

    return [
        'title'             => trim((string)($row['Title'] ?? '')),
        'wordpressId'       => trim((string)($row['id'] ?? '')),
        'kalender'          => parseTags($row['Calendars'] ?? ''),
        'veroeffentlichtAm' => parseDate($row['Date'] ?? ''),
        'reiseDatumText'    => trim((string)($row['kunstreise_datum'] ?? '')),
        'reiseStart'        => parseDate($isAtelier ? ($row['atelierbesuche_startdatum'] ?? '') : ($row['kunstreise_startdatum'] ?? '')),
        'reiseEnde'         => parseDate($isAtelier ? ($row['atelierbesuche_enddatum'] ?? '') : ($row['kunstreise_enddatum'] ?? '')),
        'beschreibung'      => writerHtml($isAtelier ? ($row['atelierbesuche_beschreibung'] ?? '') : ($row['kunstreise_beschreibung'] ?? '')),
        'category'          => $isAtelier ? 'atelierbesuch' : 'kunstreise',
        'galerie'           => yamlList($files),
        'legacyBildquellen' => legacyImageSources($row),
    ];
}

function travelImageUrls(array $row): array
{
    $urls = splitPipe($row['all_image_urls'] ?? '');
    if ($urls !== []) {
        return $urls;
    }

    $urls = splitPipe($row['Image URL'] ?? '');
    foreach (['kunstreise_bilder_urls', 'atelierbesuche_bilder_urls'] as $key) {
        $urls = array_merge($urls, splitPipe($row[$key] ?? ''));
    }

    return array_values(array_unique(array_filter($urls, static fn ($url) => trim((string)$url) !== '')));
}

function eventContent(array $row, array $files, array $exhibitionMap): array
{
    $exhibitionId = trim((string)($row['ausstellung'] ?? ''));
    $linkedPageId = $exhibitionMap[$exhibitionId] ?? '';
    $eventTime = parseTimeFromTitle(trim((string)($row['Title'] ?? '')));

    return [
        'title'             => eventGeneratedTitle($row),
        'bodytext'          => writerHtml($row['Title'] ?? ''),
        'wordpressId'       => trim((string)($row['id'] ?? '')),
        'kalender'          => parseTags($row['Calendars'] ?? ''),
        'eventTime'         => $eventTime,
        'startdatum'        => parseDate($row['termine_startdatum'] ?? ''),
        'enddatum'          => parseDate($row['termine_enddatum'] ?? ''),
        'ausstellung'       => yamlList($linkedPageId !== '' ? [$linkedPageId] : []),
        'notiz'             => '',
        'galerie'           => yamlList($files),
        'legacyBildquellen' => legacyImageSources($row),
    ];
}

function shopContent(array $row, array $files): array
{
    return [
        'title'             => trim((string)($row['Title'] ?? '')),
        'wordpressId'       => trim((string)($row['id'] ?? '')),
        'kuenstler'         => trim((string)($row['kuenstler'] ?? '')),
        'beschreibung'      => writerHtml($row['beschreibung'] ?? ''),
        'galerie'           => yamlList($files),
        'bildAltText'       => trim((string)($row['Image Alt Text'] ?? '')),
        'bildCaption'       => trim((string)($row['Image Caption'] ?? '')),
        'bildBeschreibung'  => trim((string)($row['Image Description'] ?? '')),
        'legacyBildquellen' => legacyImageSources($row),
    ];
}

function firstWords(string $value, int $count): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $words = preg_split('/\s+/u', $value) ?: [];
    $words = array_values(array_filter($words, static fn ($item) => $item !== ''));
    if ($words === []) {
        return '';
    }

    return implode(' ', array_slice($words, 0, $count));
}

function eventGeneratedTitle(array $row): string
{
    $start = parseDate($row['startdatum'] ?? '');
    if ($start === '') {
        $start = parseDate($row['termine_startdatum'] ?? '');
    }
    if ($start !== '') {
        return $start;
    }

    return firstWords(trim((string)($row['Title'] ?? '')), 3);
}

$ausstellungen = ensureSectionPage($kirby, 'ausstellungen', 'ausstellungen', 'Ausstellungen', 1);
$reisen = ensureSectionPage($kirby, 'reisen', 'reisen', 'Reisen', 2);
$termine = ensureSectionPage($kirby, 'termine', 'termine', 'Termine', 3);
$shop = ensureSectionPage($kirby, 'shop', 'shop', 'Shop', 4);

$counts = [
    'ausstellungen' => 0,
    'reisen' => 0,
    'termine' => 0,
    'kataloge' => 0,
    'editionen' => 0,
];

$exhibitionMap = [];
$imageMetadata = mediaMetadataByUrl($normalizedDir);

foreach (csvRows(normalizedCsvPath($normalizedDir, 'Ausstellungen-Export-2026-April-21')) as $row) {
    $title = trim((string)($row['Title'] ?? ''));
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'ausstellung');
    $page = ensureChildPage($ausstellungen, $slug, 'ausstellung', exhibitionContent($row, []));
    $imageUrls = splitPipe($row['all_image_urls'] ?? '');
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page = $page->update(exhibitionContent($row, $files), 'de');
    $englishDescription = exhibitionContentEnglish($row);
    if ($englishDescription !== '') {
        $page = $page->update(['beschreibung' => $englishDescription], 'en');
    }

    if ($wordpressId !== '') {
        $exhibitionMap[$wordpressId] = $page->id();
    }

    $counts['ausstellungen']++;
}

foreach (csvRows(normalizedCsvPath($normalizedDir, 'Kunstreisen-Export-2026-April-21')) as $row) {
    $title = trim((string)($row['Title'] ?? ''));
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'reise');
    $page = ensureChildPage($reisen, $slug, 'reise', travelContent($row, []));
    $imageUrls = travelImageUrls($row);
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page->update(travelContent($row, $files));
    $counts['reisen']++;
}

foreach (csvRows(normalizedCsvPath($normalizedDir, 'Atelierbesuche-Export-2026-May-19-0755')) as $row) {
    $title = trim((string)($row['Title'] ?? ''));
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'reise');
    $page = ensureChildPage($reisen, $slug, 'reise', travelContent($row, []));
    $imageUrls = travelImageUrls($row);
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page->update(travelContent($row, $files));
    $counts['reisen']++;
}

foreach (csvRows(normalizedCsvPath($normalizedDir, 'Termine-Export-2026-April-21')) as $row) {
    $title = eventGeneratedTitle($row);
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'termin');
    $page = ensureChildPage($termine, $slug, 'termin', eventContent($row, [], $exhibitionMap));
    $imageUrls = splitPipe($row['all_image_urls'] ?? '');
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page->update(eventContent($row, $files, $exhibitionMap));
    $counts['termine']++;
}

foreach (csvRows(normalizedCsvPath($normalizedDir, 'Kataloge-Export-2026-April-21')) as $row) {
    $title = trim((string)($row['Title'] ?? ''));
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'katalog');
    $page = ensureChildPage($shop, $slug, 'katalog', shopContent($row, []));
    $imageUrls = splitPipe($row['all_image_urls'] ?? '');
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page->update(shopContent($row, $files));
    $counts['kataloge']++;
}

foreach (csvRows(normalizedCsvPath($normalizedDir, 'Editionen-Export-2026-April-21')) as $row) {
    $title = trim((string)($row['Title'] ?? ''));
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'edition');
    $page = ensureChildPage($shop, $slug, 'edition', shopContent($row, []));
    $imageUrls = splitPipe($row['all_image_urls'] ?? '');
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page->update(shopContent($row, $files));
    $counts['editionen']++;
}

echo "Import completed.\n";
foreach ($counts as $label => $count) {
    echo ucfirst($label) . ': ' . $count . "\n";
}
