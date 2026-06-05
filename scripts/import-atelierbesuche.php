<?php

declare(strict_types=1);

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Data\Yaml;
use Kirby\Toolkit\Str;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/kirby/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$kirby = new App();
$kirby->impersonate('kirby');

$websiteRoot = dirname(__DIR__);
$projectRoot = dirname($websiteRoot);

$csvFile = $argv[1] ?? $projectRoot . '/Export/2604/normalized/Atelierbesuche-Export-2026-May-19-0755-normalized.csv';
$downloadsDir = $argv[2] ?? $projectRoot . '/Export/2604/downloads';

if (is_file($csvFile) === false) {
    throw new RuntimeException("CSV file not found: {$csvFile}");
}

if (is_dir($downloadsDir) === false) {
    throw new RuntimeException("Downloads directory not found: {$downloadsDir}");
}

function csvRows(string $file): Generator { $h=fopen($file,'rb'); if($h===false) throw new RuntimeException("Cannot open CSV file: {$file}"); $header=fgetcsv($h); if($header===false){fclose($h); return;} if(isset($header[0])) $header[0]=preg_replace('/^\xEF\xBB\xBF/','',(string)$header[0]); while(($row=fgetcsv($h))!==false){ if($row===[null]||$row===false) continue; yield array_combine($header,$row)?:[]; } fclose($h);} 
function splitPipe(?string $value): array { $parts=array_map('trim', explode('|',(string)$value)); return array_values(array_filter($parts, static fn($item)=>$item!=='')); }
function uniqueInOrder(array $values): array { $seen=[]; $out=[]; foreach($values as $v){ if($v==='' || isset($seen[$v])) continue; $seen[$v]=true; $out[]=$v; } return $out; }
function parseDate(?string $value): string { $value=trim((string)$value); if($value==='') return ''; if(preg_match('/^\d{8}$/',$value)===1) return substr($value,0,4).'-'.substr($value,4,2).'-'.substr($value,6,2); return $value; }
function parseTags(?string $value): string { $value=trim((string)$value); if($value==='') return ''; $parts=preg_split('/[|,;]+/', $value)?:[]; $parts=array_values(array_filter(array_map('trim',$parts), static fn($i)=>$i!=='')); return implode(', ', $parts);} 
function writerHtml(?string $value): string { $value=str_replace(["\r\n","\r"],"\n",trim((string)$value)); if($value==='') return ''; return '<p>'.str_replace("\n\n",'</p><p>',str_replace("\n","<br>\n",$value)).'</p>'; }
function yamlList(array $values): string { $values=array_values(array_filter($values, static fn($i)=>$i!=='')); return $values===[] ? '' : Yaml::encode($values);} 
function ensureChildPage(Page $parent, string $slug, string $template, array $content): Page {
    $page = $parent->children()->find($slug);
    if ($page === null) {
        $page = $parent->createChild([
            'slug' => $slug,
            'template' => $template,
            'draft' => false,
            'translations' => [['code' => 'de', 'content' => $content]],
            'content' => $content,
        ]);
    } else {
        $page = $page->update($content, 'de');
    }

    if ($page->isListed() === false) {
        $page = $page->changeStatus('listed');
    }

    return $page;
}
function mediaMetadataByUrl(string $normalizedDir): array { $manifestFile=$normalizedDir.'/media-manifest.csv'; if(is_file($manifestFile)===false) return []; $metadata=[]; foreach(csvRows($manifestFile) as $row){ $imageUrl=trim((string)($row['image_url'] ?? '')); if($imageUrl==='') continue; $metadata[$imageUrl]=['title'=>trim((string)($row['image_title'] ?? '')),'caption'=>trim((string)($row['image_caption'] ?? '')),'credit'=>trim((string)($row['image_credit'] ?? ''))]; } return $metadata; }
function ensureFiles(Page $page, array $imageUrls, string $downloadsDir, array $fileMetadataByUrl=[]): array { $f=[]; foreach($imageUrls as $url){ $path=parse_url($url,PHP_URL_PATH); $rel=ltrim((string)$path,'/'); if($rel==='') continue; $src=$downloadsDir.'/'.$rel; if(!is_file($src)) continue; $ext=pathinfo($src, PATHINFO_EXTENSION); $stem=pathinfo($src, PATHINFO_FILENAME); $name=$stem.'-'.substr(sha1($rel), 0, 8).($ext!==''?'.'.$ext:''); $content=array_filter(['title'=>trim((string)($fileMetadataByUrl[$url]['title'] ?? '')),'caption'=>trim((string)($fileMetadataByUrl[$url]['caption'] ?? '')),'credit'=>trim((string)($fileMetadataByUrl[$url]['credit'] ?? ''))], static fn($v)=>$v!==''); if($page->file($name)===null){$page->createFile(['filename'=>$name,'source'=>$src,'parent'=>$page,'translations'=>$content!==[]?[['code'=>'de','content'=>$content]]:[],'content'=>$content]);} elseif($content!==[]){$page->file($name)?->update($content,'de');} if(!in_array($name,$f,true)) $f[]=$name;} return $f; }

$reisen = $kirby->site()->find('reisen');
if ($reisen === null) {
    throw new RuntimeException('Parent page "reisen" not found.');
}

$count = 0;
$imageMetadata = mediaMetadataByUrl(dirname($csvFile));
foreach (csvRows($csvFile) as $row) {
    $title = trim((string)($row['Title'] ?? ''));
    $wordpressId = trim((string)($row['id'] ?? ''));
    $slug = $wordpressId . '-' . Str::slug($title ?: 'reise');

    $content = [
        'title' => $title,
        'wordpressId' => $wordpressId,
        'kalender' => parseTags($row['Calendars'] ?? ''),
        'veroeffentlichtAm' => parseDate($row['Date'] ?? ''),
        'reiseDatumText' => '',
        'reiseStart' => parseDate($row['atelierbesuche_startdatum'] ?? ''),
        'reiseEnde' => parseDate($row['atelierbesuche_enddatum'] ?? ''),
        'beschreibung' => writerHtml($row['atelierbesuche_beschreibung'] ?? ''),
        'category' => 'atelierbesuch',
        'legacyBildquellen' => "Image URL: " . trim((string)($row['Image URL'] ?? '')) . "\n" .
            'atelierbesuche_bilder: ' . trim((string)($row['atelierbesuche_bilder'] ?? '')) . "\n" .
            'all_image_urls: ' . trim((string)($row['all_image_urls'] ?? '')),
    ];

    $page = ensureChildPage($reisen, $slug, 'reise', $content);
    $imageUrls = splitPipe($row['all_image_urls'] ?? '');
    if ($imageUrls === []) {
        $imageUrls = uniqueInOrder(array_merge(
            splitPipe($row['Image URL'] ?? ''),
            splitPipe($row['atelierbesuche_bilder_urls'] ?? '')
        ));
    }
    $fileMetadata = [];
    foreach ($imageUrls as $imageUrl) {
        if (isset($imageMetadata[$imageUrl])) {
            $fileMetadata[$imageUrl] = $imageMetadata[$imageUrl];
        }
    }
    $files = ensureFiles($page, $imageUrls, $downloadsDir, $fileMetadata);
    $page->update(array_merge($content, ['galerie' => yamlList($files)]), 'de');
    $count++;
}

echo "Imported Atelierbesuche: {$count}\n";
