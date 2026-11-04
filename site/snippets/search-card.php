<?php
// A search result in the colour of its section (template `suche`)
$template  = $item->intendedTemplate()->name();
$url       = $item->url();
$whiteFont = false;
$names     = [];
$dates     = [];

switch ($template) {
    case 'ausstellung':
        $category  = t('ui.exhibition');
        $color     = $item->color()->or('#dce0e3')->value();
        $whiteFont = $item->whiteFont()->toBool();
        $names     = [$item->kuenstler()->value(), $item->title()->value()];
        $startStr  = $item->startdatum()->toDate('d.m.Y');
        $endStr    = $item->enddatum()->toDate('d.m.Y');
        $dates[]   = ($startStr ?: '') . ($startStr && $endStr ? ' – ' : '') . ($endStr ?: '');
        if ($eroTs = $item->eroffnungsdatum()->toDate()) {
            $eroDay    = $isDE ? $days[date('w', $eroTs)]       : $daysEn[date('w', $eroTs)];
            $eroMonth  = $isDE ? $months[date('n', $eroTs) - 1] : $monthsEn[date('n', $eroTs) - 1];
            $eroDayNum = date('j', $eroTs);
            $eroHour   = date('H', $eroTs);
            $dates[]   = $isDE
                ? "{$openingLabel}: {$eroDay}, {$eroDayNum}. {$eroMonth}, {$eroHour} Uhr"
                : "{$openingLabel}: {$eroDay}, {$eroMonth} {$eroDayNum}, {$eroHour}:00";
        }
        break;

    case 'reise':
        $isAtelier = $item->category()->value() === 'atelierbesuch';
        $category  = $isAtelier ? t('ui.studio_visit') : t('ui.art_trip');
        $color     = $isAtelier ? $colors['atelierbesuch'] : $colors['kunstreise'];
        $names     = [$item->title()->value()];
        $startStr  = $item->reiseStart()->toDate('d.m.Y');
        $endStr    = $item->reiseEnde()->toDate('d.m.Y');
        $dates[]   = $item->reiseDatumText()->isNotEmpty()
            ? $item->reiseDatumText()->value()
            : ($startStr ?: '') . ($startStr && $endStr && $endStr !== $startStr ? ' – ' . $endStr : '');
        break;

    case 'termin':
        // Like on the Termine page: leads to the linked exhibition or trip
        $category = $item->eventCategory()->or(t('ui.event'))->value();
        $color    = $colors['termin'];
        $linked   = $item->ausstellung()->toPages()->first() ?? $item->reise()->toPages()->first();
        $url      = $linked?->url() ?? page('termine')->url();
        $names    = [Str::excerpt($item->bodytext()->value(), 140)];
        $timeStr  = $item->eventTime()->isNotEmpty() ? $item->eventTime()->toDate('H:i') : '';
        $dates[]  = $item->startdatum()->toDate('d.m.Y') . ($timeStr ? ', ' . $timeStr . ' Uhr' : '');
        break;

    case 'edition':
    case 'katalog':
        $category = $template === 'edition' ? t('ui.edition') : t('ui.catalogue');
        $color    = $item->color()->or('#dce0e3')->value();
        $names    = [$item->kuenstler()->value(), $item->title()->value()];
        break;

    default:
        $category = $item->title()->value();
        $color    = $item->color()->or('#dce0e3')->value();
        $names    = [Str::excerpt(KvSearch::plain($item->content()->get(KvSearch::FIELDS[$template][1] ?? 'title')), 140)];
}

$names = array_filter($names, fn ($name) => trim((string)$name) !== '');
$dates = array_filter($dates, fn ($date) => trim($date) !== '');
?>
<a href="<?= $url ?>" class="search-card<?php e($whiteFont, ' white-font') ?>" style="--color: <?= esc($color, 'attr') ?>; --index: <?= (int)($index ?? 0) ?>">
    <p class="search-card__category"><?= esc($category) ?></p>
    <div class="search-card__details">
        <div class="search-card__names">
            <?php foreach ($names as $name): ?>
                <p><?= esc($name) ?></p>
            <?php endforeach ?>
        </div>
        <div class="search-card__dates">
            <?php foreach ($dates as $date): ?>
                <p><?= esc($date) ?></p>
            <?php endforeach ?>
        </div>
        <span class="search-card__link"><?= t('ui.more_information') ?></span>
    </div>
</a>
