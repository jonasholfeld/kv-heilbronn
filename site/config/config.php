<?php

$debug = getenv('KIRBY_DEBUG');
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocalHost = in_array($host, ['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], true);

return [
    'debug' => $debug !== false
        ? filter_var($debug, FILTER_VALIDATE_BOOL)
        : $isLocalHost,
];
