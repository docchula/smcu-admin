<?php

use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use PhpOffice\PhpSpreadsheet\IOFactory;

test('resizing image (intervention/image)', function () {
    $img = ImageManager::gd()->read(file_get_contents('https://source.unsplash.com/user/c_v_r/800x800'))
        ->scaleDown(600, 700)->encode(new WebpEncoder(quality: 80));
    $this->assertTrue((bool) $img);
});

test('creating excel (phpoffice/phpspreadsheet)', function () {
    $spreadsheet = IOFactory::load('storage/export_participant_template.xlsx');
    $worksheet = $spreadsheet->getActiveSheet();
    $worksheet->setCellValue('A1', 'รายชื่อนิสิตผู้เกี่ยวข้อง โครงการทดสอบ');
    $tmpPath = tempnam('storage', 'tmp-test-');
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpPath);

    expect(file_exists($tmpPath))->toBeTrue();
    unlink($tmpPath);
});
