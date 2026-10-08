<?php

$debug = getenv('KIRBY_DEBUG');
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocalHost = in_array($host, ['127.0.0.1:8000', 'localhost:8000', '127.0.0.1', 'localhost'], true);
$isDebug = $debug !== false
    ? filter_var($debug, FILTER_VALIDATE_BOOL)
    : $isLocalHost;

return [
    'debug' => $isDebug,

    // Rendered HTML is cached and flushed automatically on every Panel change
    'cache' => [
        'pages' => [
            'active' => !$isDebug,
            'ignore' => fn ($page) => $page->template()->name() === 'error',
        ],
    ],

    'hooks' => [
        // These templates sort content by today's date (current/upcoming/archive),
        // so their cached HTML must not outlive the day
        'page.render:before' => function (string $contentType, array $data, Kirby\Cms\Page $page) {
            if (in_array($page->intendedTemplate()->name(), ['home', 'ausstellungen', 'termine', 'reisen'], true)) {
                kirby()->response()->expires(strtotime('tomorrow'));
            }

            return $data;
        },
    ],

    // These also apply to resizing uploads (file blueprint `create`), so the
    // WebP format is set per thumb in site/plugins/kv-images instead of here
    'thumbs' => [
        'quality' => 78,
        // Widths for the `image` snippet
        'srcsets' => [
            'default' => [400, 800, 1200, 1600, 2000],
        ],
    ],
];
