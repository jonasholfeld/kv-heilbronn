<?php
$now = date('Y-m-d');
$sevenDaysLater = date('Y-m-d', strtotime('+7 days'));

$terminePage = page('termine');
$upcomingEvent = null;

if ($terminePage) {
    $events = $terminePage->children()
        ->filter(function ($child) use ($now, $sevenDaysLater) {
            $start = $child->startdatum()->toDate('Y-m-d');
            return $start && $start >= $now && $start <= $sevenDaysLater;
        })
        ->sortBy('startdatum', 'asc');

    if ($events->count() > 0) {
        $upcomingEvent = $events->first();
    }
}

$weekdayNames  = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
$monthNames    = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
$weekdayFields = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

if ($upcomingEvent) {
    $startStr  = $upcomingEvent->startdatum()->value();
    $timestamp = strtotime($startStr);
} else {
    $timestamp = time();
}

$dayIndex    = (int)date('N', $timestamp) - 1; // 0 = Monday
$weekdayName = $weekdayNames[$dayIndex];
$day         = (int)date('j', $timestamp);
$monthName   = $monthNames[(int)date('n', $timestamp) - 1];
$year        = date('Y', $timestamp);

$weekdayField = $weekdayFields[$dayIndex];
$openingHours = site()->{$weekdayField}()->value();

$headerParts = [
    strtoupper($weekdayName),
    $day . '. ' . strtoupper($monthName) . ' ' . $year,
];
if ($openingHours) {
    $headerParts[] = strtoupper($openingHours);
}
$headerText = implode(', ', $headerParts);

// Build bullet items
$bullets = [];

if ($upcomingEvent) {
    $bodyHtml = $upcomingEvent->bodytext()->value();

    if (!empty(trim($bodyHtml))) {
        // Standalone <p> tags (strip list content first)
        $noLists = preg_replace('/<(?:ul|ol)[^>]*>.*?<\/(?:ul|ol)>/si', '', $bodyHtml) ?? '';
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $noLists, $pMatches)) {
            foreach ($pMatches[1] as $match) {
                $text = trim(strip_tags($match));
                if ($text !== '') {
                    $bullets[] = $text;
                }
            }
        }

        // List items
        if (preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $bodyHtml, $liMatches)) {
            foreach ($liMatches[1] as $match) {
                $text = trim(strip_tags($match));
                if ($text !== '') {
                    $bullets[] = $text;
                }
            }
        }
    }
} else {
    $message = $openingHours
        ? site()->regularOpen()->value()
        : site()->regularClosed()->value();

    if (!empty($message)) {
        $bullets[] = $message;
    }
}

$besuchPage = page('besuch');
?>
<div class="home-overlay" id="home-overlay" role="dialog" aria-modal="true" aria-label="<?= t('ui.current_information') ?>">
    <div class="home-overlay__main">
        <p class="home-overlay__header"><?= esc($headerText) ?></p>
        <?php if (!empty($bullets)): ?>
        <ul class="home-overlay__bullets">
            <?php foreach ($bullets as $bullet): ?>
            <li><p><?= esc($bullet) ?></p></li>
            <?php endforeach ?>
        </ul>
        <?php endif ?>
    </div>

    <div class="home-overlay__footer">
        <div class="home-overlay__footer-col">
            <h2><?= t('ui.address') ?></h2>
                <?= $site->adresse()->kt() ?>
        </div>
        <div class="home-overlay__footer-col">
            <h2><?= t('ui.opening_hours') ?></h2>
            <?php if ($besuchPage): ?>
                <?= $besuchPage->offnungszeiten()->kt() ?>
            <?php endif ?>
        </div>
        <div class="home-overlay__footer-col">
            <h2><?= t('ui.directions') ?></h2>
            <?php if ($besuchPage): ?>
                <p><a href="<?= $besuchPage->url() ?>"><?= t('ui.directions_to_kunstverein') ?></a></p>
            <?php endif ?>
        </div>
    </div>
</div>
