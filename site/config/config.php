<?php

// Credentials live in /.env (not in git, see .env.example); real environment
// variables take precedence
$envFile = dirname(__DIR__, 2) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (getenv($key) === false) {
            putenv($key . '=' . trim($value, '"\''));
        }
    }
}

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

    // Shop order mails (site/plugins/kv-shop-order)
    'email' => [
        'transport' => [
            'type'     => 'smtp',
            'host'     => getenv('MAIL_HOST') ?: 'localhost',
            'port'     => (int)(getenv('MAIL_PORT') ?: 587),
            'security' => getenv('MAIL_SECURITY') ?: 'tls',
            'auth'     => true,
            'username' => getenv('MAIL_USERNAME') ?: '',
            'password' => getenv('MAIL_PASSWORD') ?: '',
        ],
    ],
    'kv.shopOrder.from'     => getenv('MAIL_FROM') ?: '',
    'kv.shopOrder.fromName' => getenv('MAIL_FROM_NAME') ?: 'Kunstverein Heilbronn',

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
