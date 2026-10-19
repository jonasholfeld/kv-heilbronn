<?php
/**
 * Collapsible credits bubble below a gallery image: title, caption and
 * credit, one line each. Line breaks set in the Panel are kept, blank lines
 * dropped so the bubble never shows empty rows.
 *
 * @var Kirby\Cms\File $file
 */
$lines = [];
foreach ([$file->title(), $file->caption(), $file->credit()] as $field) {
    foreach (preg_split('/\R/u', (string)$field->value()) as $line) {
        $line = trim($line);
        if ($line !== '') {
            $lines[] = esc($line);
        }
    }
}
if (empty($lines)) {
    return;
}
?>
<div class="credits-wrapper bubble">
    <p>Credits</p>
    <div class="credits-collapse-wrapper">
        <div class="credits-content-wrapper">
            <?= implode('<br>', $lines) ?>
        </div>
    </div>
</div>
