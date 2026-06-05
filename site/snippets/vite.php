<?php

$entry = $entry ?? null;

if (!$entry) {
    return;
}

$devServer = 'http://127.0.0.1:5173';
$manifestPath = dirname(__DIR__, 2) . '/assets/dist/.vite/manifest.json';
$isDev = option('debug') === true && @fopen($devServer . '/@vite/client', 'r') !== false;

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
