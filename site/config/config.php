<?php

$debug = getenv('KIRBY_DEBUG');

return [
    'debug' => $debug !== false && filter_var($debug, FILTER_VALIDATE_BOOL),
];
