<?php
/**
 * Collapsible credits bubble below a gallery image: title, caption and
 * credit, one line each. Line breaks set in the Panel are kept, a blank
 * line starts a new paragraph.
 *
 * @var Kirby\Cms\File $file
 */
$paragraphs = [[]];
foreach ([$file->title(), $file->caption(), $file->credit()] as $field) {
    $value = trim((string)$field->value());
    if ($value === '') {
        continue;
    }
    foreach (preg_split('/\R/u', $value) as $line) {
        $line = trim($line);
        if ($line !== '') {
            $paragraphs[array_key_last($paragraphs)][] = esc($line);
        } elseif (!empty(end($paragraphs))) {
            $paragraphs[] = [];
        }
    }
}
$paragraphs = array_filter($paragraphs);
if (empty($paragraphs)) {
    return;
}
?>
<div class="credits-wrapper bubble">
    <p>Credits</p>
    <div class="credits-collapse-wrapper">
        <div class="credits-content-wrapper">
            <?php foreach ($paragraphs as $lines): ?>
            <p><?= implode('<br>', $lines) ?></p>
            <?php endforeach ?>
        </div>
    </div>
</div>
