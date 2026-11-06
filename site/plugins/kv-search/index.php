<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Content\Field;
use Kirby\Toolkit\Str;

// Global search (template `suche`). A page is found when every search word
// occurs in it: words of up to two letters as a whole word ("de" in "Solweig
// de Barry"), of three letters at the start of a word, longer ones anywhere
// in a word ("reise" in "Kunstreise"). A search word found nowhere may be
// matched by a near word instead (typos); such pages rank below the exact
// hits. Ranked by how often the words occur; whole words, hits in titles and
// names, the whole query in a row and the headings of a page count more.
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

    // Fields whose hits count TITLE_WEIGHT times
    public const TITLE_FIELDS = ['title', 'kuenstler'];
    public const TITLE_WEIGHT = 5;

    // Headings the templates add to a page (translation keys), searched
    // along with its fields: e.g. "Öffnungszeiten" leads to Besuch
    public const HEADINGS = [
        'kunstverein' => ['ui.kunstverein', 'ui.membership', 'ui.statute', 'ui.form_open_membership'],
        'besuch'      => ['ui.visit', 'ui.contact', 'ui.opening_hours', 'ui.directions', 'ui.contacts'],
    ];

    // A heading stands for a whole section about the word, so a hit in it
    // counts as this many hits in the text
    public const HEADING_WEIGHT = 10;

    // The whole query of several words in a row counts as this many hits
    public const PHRASE_WEIGHT = 10;

    // Search words up to this length only match whole words; one letter
    // longer, the start of words
    public const WHOLE_WORD_LENGTH = 2;
    public const PREFIX_LENGTH     = 3;

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
        $words = static::words(Str::lower(trim($query)));
        if ($words === []) {
            return [];
        }
        $phrase = implode(' ', $words);

        // The exact hits of every word on every page
        $pages = [];
        $found = [];
        foreach (static::candidates() as $page) {
            $title    = static::tokens(static::text($page, true));
            $text     = static::text($page);
            $tokens   = static::tokens($text);
            $headings = static::tokens(static::headings($page));

            $hits = [];
            foreach ($words as $word) {
                $hits[$word] = static::TITLE_WEIGHT * static::hits($title, $word)
                             + static::hits($tokens, $word)
                             + static::HEADING_WEIGHT * static::hits($headings, $word);
                if ($hits[$word] > 0) {
                    $found[$word] = true;
                }
            }
            $pages[] = compact('page', 'text', 'tokens', 'headings', 'hits');
        }

        $results = [];
        foreach ($pages as ['page' => $page, 'text' => $text, 'tokens' => $tokens, 'headings' => $headings, 'hits' => $hits]) {
            $score = 0;
            $exact = true;
            foreach ($words as $word) {
                $wordHits = $hits[$word];
                // Near matches only for a word found nowhere as it is
                if ($wordHits === 0 && !isset($found[$word])) {
                    $exact    = false;
                    $wordHits = static::fuzzyHits($tokens + $headings, $word);
                }
                // Every word has to be there
                if ($wordHits === 0) {
                    continue 2;
                }
                $score += $wordHits;
            }

            if (count($words) > 1) {
                $score += static::PHRASE_WEIGHT * substr_count(static::squash($text), $phrase);
            }

            $results[] = ['page' => $page, 'score' => $score, 'exact' => $exact];
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

    // The search words: the query split at white space and punctuation,
    // without too short ones
    public static function words(string $query): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', $query, -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn ($word) => Str::length($word) >= static::MIN_LENGTH);

        return array_values(array_unique($words));
    }

    // The readable text of the searched fields (or of the title fields
    // only), in lower case
    public static function text(Page $page, bool $titleOnly = false): string
    {
        $fields = static::FIELDS[$page->intendedTemplate()->name()] ?? ['title'];
        if ($titleOnly) {
            $fields = array_intersect($fields, static::TITLE_FIELDS);
        }
        $parts = array_map(fn ($name) => static::plain($page->content()->get($name)), $fields);

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

    // The words of a text with how often each occurs
    public static function tokens(string $text): array
    {
        return array_count_values(preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    // The text's words separated by single spaces, for finding the query
    public static function squash(string $text): string
    {
        return implode(' ', preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    // How often a search word occurs among the words of a text (see the
    // top for how a word matches); as a whole word it counts twice
    public static function hits(array $tokens, string $word): int
    {
        $whole = 2 * ($tokens[$word] ?? 0);
        $length = Str::length($word);
        if ($length <= static::WHOLE_WORD_LENGTH) {
            return $whole;
        }

        $hits = 0;
        foreach ($tokens as $token => $count) {
            $token = (string)$token;
            if ($token === $word) {
                continue;
            }
            $match = $length <= static::PREFIX_LENGTH
                ? str_starts_with($token, $word)
                : str_contains($token, $word);
            if ($match) {
                $hits += $count;
            }
        }

        return $whole + $hits;
    }

    // How many words of the text are close to the search word
    public static function fuzzyHits(array $tokens, string $word): int
    {
        $maxEdits = static::maxEdits($word);
        if ($maxEdits === 0) {
            return 0;
        }

        // levenshtein() counts bytes, so umlauts are spelled out first
        $ascii = Str::ascii($word);
        $hits  = 0;
        foreach ($tokens as $token => $count) {
            $token = Str::ascii((string)$token);
            if (abs(strlen($token) - strlen($ascii)) > $maxEdits) {
                continue;
            }
            if (levenshtein($ascii, $token) <= $maxEdits) {
                $hits += $count;
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
