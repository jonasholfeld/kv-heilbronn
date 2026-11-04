<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Content\Field;
use Kirby\Toolkit\Str;

// Global search (template `suche`). Kirby's native search picks the pages
// whose fields contain one of the search words; they are ranked by how often
// the words occur in the text. Pages without an exact hit can still turn up
// through a near match of single words (typos), always below the exact hits.
class KvSearch
{
    // The fields searched per template. Titles of dates are just the date.
    public const FIELDS = [
        'ausstellung' => ['title', 'kuenstler', 'beschreibung', 'kooperationtext'],
        'reise'       => ['title', 'reiseDatumText', 'beschreibung', 'reiseplan'],
        'termin'      => ['eventCategory', 'bodytext', 'kalender'],
        'edition'     => ['title', 'kuenstler', 'beschreibung'],
        'katalog'     => ['title', 'kuenstler', 'beschreibung'],
        'kunstverein' => ['title', 'blockseditor'],
        'besuch'      => ['title', 'kontakt', 'offnungszeiten', 'anfahrt', 'ansprechpartner'],
    ];

    // Headings the templates add to a page (translation keys), searched
    // along with its fields: e.g. "Öffnungszeiten" leads to Besuch
    public const HEADINGS = [
        'kunstverein' => ['ui.kunstverein', 'ui.membership', 'ui.statute', 'ui.form_open_membership'],
        'besuch'      => ['ui.visit', 'ui.contact', 'ui.opening_hours', 'ui.directions', 'ui.contacts'],
    ];

    // A heading stands for a whole section about the word, so a hit in it
    // counts as this many hits in the text
    public const HEADING_WEIGHT = 10;

    // Near matches: a search word of at least this many characters may differ
    // from a word in the text by this many letters (shorter words only match
    // exactly)
    public const FUZZY = [
        5 => 1,
        9 => 2,
    ];

    public const MIN_LENGTH = 2;

    /**
     * Pages matching `$query`, best first, each as
     * ['page' => Page, 'score' => int, 'exact' => bool]
     */
    public static function find(string $query): array
    {
        $query = Str::lower(trim($query));
        $words = static::words($query);
        if ($words === []) {
            return [];
        }

        $candidates = static::candidates();
        $fields     = array_merge(...array_values(static::FIELDS));
        $found      = $candidates->search($query, [
            'fields'    => $fields,
            'minlength' => static::MIN_LENGTH,
        ]);

        $results = [];
        foreach ($candidates as $page) {
            $text     = static::text($page);
            $headings = static::headings($page);

            // Kirby searches the stored values, which for blocks include
            // their JSON keys; only hits in the readable text count. The
            // headings are not stored with the page.
            $score  = $found->has($page->id()) ? static::exactHits($text, $query, $words) : 0;
            $score += static::HEADING_WEIGHT * static::exactHits($headings, $query, $words);
            $exact  = $score > 0;
            if (!$exact) {
                $score = static::fuzzyHits($text . ' ' . $headings, $words);
            }

            if ($score > 0) {
                $results[] = ['page' => $page, 'score' => $score, 'exact' => $exact];
            }
        }

        usort($results, fn ($a, $b) =>
            [$b['exact'], $b['score'], static::date($b['page'])] <=>
            [$a['exact'], $a['score'], static::date($a['page'])]
        );

        return $results;
    }

    // Everything that has a page of its own, plus the upcoming dates (past
    // ones are no longer listed anywhere)
    public static function candidates(): Pages
    {
        $today = date('Y-m-d');
        $pages = site()->index()->published()->filter(
            fn ($page) => isset(static::FIELDS[$page->intendedTemplate()->name()])
        );

        return $pages->filter(function ($page) use ($today) {
            if ($page->intendedTemplate()->name() !== 'termin') {
                return true;
            }
            $start = $page->startdatum()->toDate('Y-m-d');
            $end   = $page->enddatum()->toDate('Y-m-d');
            return $end ? $end >= $today : $start && $start >= $today;
        });
    }

    // The search words: the query split at white space, without too short
    // ones
    public static function words(string $query): array
    {
        $words = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn ($word) => Str::length($word) >= static::MIN_LENGTH);

        return array_values(array_unique($words));
    }

    // The readable text of the searched fields, in lower case
    public static function text(Page $page): string
    {
        $fields = static::FIELDS[$page->intendedTemplate()->name()] ?? ['title'];
        $parts  = array_map(fn ($name) => static::plain($page->content()->get($name)), $fields);

        return Str::lower(implode(' ', $parts));
    }

    // The template's headings in the current language, in lower case
    public static function headings(Page $page): string
    {
        $keys = static::HEADINGS[$page->intendedTemplate()->name()] ?? [];

        return Str::lower(implode(' ', array_map(fn ($key) => t($key), $keys)));
    }

    // Plain text of a field: blocks rendered, HTML stripped
    public static function plain(Field $field): string
    {
        $value = $field->value() ?? '';
        if (str_starts_with(ltrim($value), '[{')) {
            $value = $field->toBlocks()->toHtml();
        }
        // A space for every tag, so words of adjacent paragraphs stay apart
        $value = strip_tags(str_replace('<', ' <', $value));

        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // How often the search words occur, plus how often the whole query does
    // when it has several words
    public static function exactHits(string $text, string $query, array $words): int
    {
        $hits = array_sum(array_map(fn ($word) => substr_count($text, $word), $words));
        if (count($words) > 1) {
            $hits += substr_count($text, $query);
        }

        return $hits;
    }

    // How many words of the text are close to a search word
    public static function fuzzyHits(string $text, array $words): int
    {
        $tokens = array_count_values(preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
        $hits   = 0;

        foreach ($words as $word) {
            $maxEdits = static::maxEdits($word);
            if ($maxEdits === 0) {
                continue;
            }
            // levenshtein() counts bytes, so umlauts are spelled out first
            $ascii = Str::ascii($word);
            foreach ($tokens as $token => $count) {
                $token = Str::ascii((string)$token);
                if (abs(strlen($token) - strlen($ascii)) > $maxEdits) {
                    continue;
                }
                if (levenshtein($ascii, $token) <= $maxEdits) {
                    $hits += $count;
                }
            }
        }

        return $hits;
    }

    public static function maxEdits(string $word): int
    {
        $edits = 0;
        foreach (static::FUZZY as $length => $allowed) {
            if (Str::length($word) >= $length) {
                $edits = $allowed;
            }
        }

        return $edits;
    }

    // For equal scores the more recent page comes first
    public static function date(Page $page): string
    {
        return match ($page->intendedTemplate()->name()) {
            'ausstellung', 'termin' => $page->startdatum()->toDate('Y-m-d') ?? '',
            'reise'                 => $page->reiseStart()->toDate('Y-m-d') ?? '',
            default                 => '',
        };
    }
}

App::plugin('kv/search', []);
