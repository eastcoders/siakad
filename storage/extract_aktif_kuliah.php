<?php
$file = 'storage/app/templates/aktif_kuliah.docx';
if (!file_exists($file)) { echo "File not found\n"; exit; }
$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    if ($xml = $zip->getFromName('word/document.xml')) {
        $text = strip_tags($xml);
        preg_match_all('/(\$\{[^}]+\})|(\{\{[^}]+\}\})|(\[[A-Za-z0-9_ -]+\])/', $text, $matches);
        $found = array_unique($matches[0]);
        print_r($found);
    } else {
        echo "document.xml not found\n";
    }
} else {
    echo "Failed to open zip\n";
}
