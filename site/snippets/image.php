<?php
/**
 * Responsive <img> for a Kirby image file
 *
 * @var \Kirby\Cms\File $file
 * @var string      $alt
 * @var string|null $sizes    `sizes` attribute (1rem = 1vw on this site)
 * @var float|null  $heightVw rendered height in vw, for images sized by height;
 *                            used to derive `sizes` from the aspect ratio
 * @var int|null    $max      largest thumb width
 * @var string|null $class
 * @var bool        $priority first visible image: load eagerly with high priority
 * @var bool        $defer    no src yet, main.js sets it when needed
 */

$alt      = $alt ?? '';
$max      = $max ?? null;
$class    = $class ?? null;
$priority = $priority ?? false;
$defer    = $defer ?? false;

if (!$file || $file->type() !== 'image') {
    return;
}

// SVGs and other non-resizable images are used as they are
if (!$file->isResizable()) {
    echo '<img ' . attr([
        'class'    => $class,
        'src'      => $file->url(),
        'alt'      => $alt,
        'decoding' => 'async',
        'loading'  => $priority ? null : 'lazy',
    ]) . '>';
    return;
}

$width  = $file->width();
$height = $file->height();

$sizes ??= isset($heightVw) && $height > 0
    ? ceil($heightVw * $width / $height) . 'vw'
    : '100vw';

$thumbs = $file->srcsetThumbs($max);
$srcset = implode(', ', array_map(
    fn ($thumb, $thumbWidth) => $thumb->url() . ' ' . $thumbWidth . 'w',
    $thumbs,
    array_keys($thumbs)
));

// Fallback for browsers without srcset: the first thumb of at least 800px
$fallback = end($thumbs)->url();
foreach ($thumbs as $thumbWidth => $thumb) {
    if ($thumbWidth >= 800) {
        $fallback = $thumb->url();
        break;
    }
}

$attrs = [
    'class'         => $class,
    'width'         => $width,
    'height'        => $height,
    'sizes'         => $sizes,
    'alt'           => $alt,
    'decoding'      => 'async',
    'loading'       => $priority ? null : 'lazy',
    'fetchpriority' => $priority ? 'high' : null,
];

if ($defer) {
    $attrs['data-src']    = $fallback;
    $attrs['data-srcset'] = $srcset;
} else {
    $attrs['src']    = $fallback;
    $attrs['srcset'] = $srcset;
}
?>
<img <?= attr($attrs) ?>>
