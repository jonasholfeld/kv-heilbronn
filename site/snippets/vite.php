<?php

$entry = $entry ?? null;

if (!$entry) {
    return;
}

$devServer = 'http://127.0.0.1:5173';
$manifestPath = dirname(__DIR__, 2) . '/assets/dist/.vite/manifest.json';
$forceDev = getenv('VITE_DEV_SERVER');
$devServerHost = parse_url($devServer, PHP_URL_HOST) ?: '127.0.0.1';
$devServerPort = (int) (parse_url($devServer, PHP_URL_PORT) ?: 5173);
$devServerReachable = false;

if ($forceDev !== false) {
    $devServerReachable = filter_var($forceDev, FILTER_VALIDATE_BOOL);
} else {
    $socket = @fsockopen($devServerHost, $devServerPort, $errno, $errstr, 0.2);

    if (is_resource($socket)) {
        $devServerReachable = true;
        fclose($socket);
    }
}

$isDev = option('debug') === true && $devServerReachable;

if ($isDev):
?>
  <script type="module" src="<?= $devServer ?>/@vite/client"></script>
  <script type="module" src="<?= $devServer ?>/<?= $entry ?>"></script>
<?php
    return;
endif;

if (!is_file($manifestPath)) {
    return;
}

$manifest = json_decode(file_get_contents($manifestPath), true);
$chunk = $manifest[$entry] ?? null;

if (!$chunk) {
    return;
}

foreach ($chunk['css'] ?? [] as $cssFile): ?>
  <link rel="stylesheet" href="<?= url('assets/dist/' . $cssFile) ?>">
<?php endforeach; ?>

<script type="module" src="<?= url('assets/dist/' . $chunk['file']) ?>"></script>
