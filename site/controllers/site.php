<?php
return function () {
    $kirby = kirby();
    $isDE = $kirby->language() == 'de';
    $days   = ['So.', 'Mo.', 'Di.', 'Mi.', 'Do.', 'Fr.', 'Sa.'];
    $months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    $daysEn   = ['Sun.', 'Mon.', 'Tue.', 'Wed.', 'Thu.', 'Fri.', 'Sat.'];
    $monthsEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $openingLabel  = t('ui.opening');
    $moreInfoLabel = t('ui.more_information');
    return [
        'isDE' => $isDE,
        'days' => $days,
        'months' => $months,
        'daysEn' => $daysEn,
        'monthsEn' => $monthsEn,
        'openingLabel' => $openingLabel,
        'moreInfoLabel' => $moreInfoLabel
    ];
};
