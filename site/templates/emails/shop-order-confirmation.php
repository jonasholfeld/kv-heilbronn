<?php
/**
 * Plain text version of shop-order-confirmation.html.php
 *
 * @var string $body HTML
 */
$text = preg_replace(['#<br\s*/?>\n?#i', '#</(p|li|h[1-6])>#i'], ["\n", "\n\n"], $body);
echo trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
