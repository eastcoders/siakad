<?php

require 'vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$templatePath = 'storage/app/templates/izin_pkl.docx';

if (! file_exists($templatePath)) {
    echo "Template not found: $templatePath\n";
    exit(1);
}

try {
    $templateProcessor = new TemplateProcessor($templatePath);
    $variables = $templateProcessor->getVariables();
    echo "Variables found in template:\n";
    print_r($variables);
} catch (\Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
