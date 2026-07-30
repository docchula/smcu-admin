<?php

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use PhpOffice\PhpSpreadsheet\IOFactory;

test('resizing image (intervention/image)', function () {
    $img = ImageManager::usingDriver(new Driver())
        ->decodeBinary(file_get_contents('https://www.gstatic.com/webp/gallery/1.jpg'))
        ->scaleDown(400, 300)->encode(new WebpEncoder(quality: 80));
    $this->assertTrue((bool) $img);
});

test('resizing an uploaded photo to webp (PersonnelController)', function () {
    // Mirrors the photo pipeline in PersonnelController::store()
    $file = UploadedFile::fake()->image('photo.png', 2400, 1600);

    $encoded = ImageManager::usingDriver(new Driver())
        ->decodeSplFileInfo($file)
        ->scaleDown(1000, 1000)
        ->encode(new WebpEncoder(quality: 80));

    $info = getimagesizefromstring((string) $encoded);

    expect($info['mime'])->toBe('image/webp')
        ->and($info[0])->toBe(1000)
        ->and($info[1])->toBe(667); // aspect ratio preserved
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
