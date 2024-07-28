<?php

use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use PhpOffice\PhpSpreadsheet\IOFactory;

test('resizing image (intervention/image)', function () {
    $img = ImageManager::gd()->read(file_get_contents('https://www.gstatic.com/webp/gallery/1.jpg'))
        ->scaleDown(400, 300)->encode(new WebpEncoder(quality: 80));
    $this->assertTrue((bool) $img);
});

test('creating excel (phpoffice/phpspreadsheet)', function () {
    $spreadsheet = IOFactory::load('storage/export_participant_template.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    $worksheet->setCellValue('A1', 'Test data');
    $tmpPath = tempnam('storage', 'tmp-test-');
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpPath);

    expect(file_exists($tmpPath))->toBeTrue();
    unlink($tmpPath);
});
