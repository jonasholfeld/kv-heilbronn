<?php

use Kirby\Cms\App;

// Responsive image helpers, shared by the `image` snippet and
// scripts/generate-thumbs.php so both produce the exact same thumbs.
App::plugin('kv/images', [
    'fileMethods' => [
        // The configured srcset widths up to the original width (Kirby never
        // upscales, so larger entries would only duplicate the original) plus
        // the original width itself when it falls between two entries
        'srcsetWidths' => function (?int $max = null): array {
            $widths = option('thumbs.srcsets.default', []);
            $limit  = min($this->width(), $max ?? PHP_INT_MAX, max($widths));
            $result = array_filter($widths, fn ($width) => $width < $limit);
            $result[] = $limit;

            return array_values($result);
        },
        'srcsetThumbs' => function (?int $max = null): array {
            $thumbs = [];

            foreach ($this->srcsetWidths($max) as $width) {
                $thumbs[$width] = $this->thumb(['width' => $width, 'format' => 'webp']);
            }

            return $thumbs;
        },
    ],
]);
