<?php

use App\Helper;

test('buddhistYear adds 543 to the given year', function () {
    expect(Helper::buddhistYear(2024))->toBe(2567)
        ->and(Helper::buddhistYear(2025))->toBe(2568);
});

test('buddhistYear defaults to the current year', function () {
    expect(Helper::buddhistYear())->toBe((int) date('Y') + 543);
});

test('buddhistYear reads the system clock, not the Carbon test clock', function () {
    // Helper::buddhistYear() uses date('Y') rather than Carbon, so travelling in
    // time does not affect it. termYear() does use Carbon. Documenting the
    // difference so a future change to either is a deliberate one.
    $this->travelTo('2030-06-15');

    expect(Helper::buddhistYear())->toBe((int) date('Y') + 543)
        ->and(Helper::termYear())->toBe(2573);
});

test('termYear rolls over two months after the calendar year', function () {
    // The SMCU term year starts in March, so January and February still belong
    // to the previous term year.
    $this->travelTo('2025-01-15');
    expect(Helper::termYear())->toBe(2567);

    $this->travelTo('2025-02-28');
    expect(Helper::termYear())->toBe(2567);

    $this->travelTo('2025-03-01');
    expect(Helper::termYear())->toBe(2568);

    $this->travelTo('2025-12-31');
    expect(Helper::termYear())->toBe(2568);
});

test('formatDepartmentName expands club and committee names', function (string $input, string $expected) {
    expect(Helper::formatDepartmentName($input))->toBe($expected);
})->with([
    ['คณะกรรมการบริหาร', 'สโมสรนิสิตคณะแพทยศาสตร์'],
    ['คณะกรรมการชั้นปีที่ 1', 'คณะกรรมการชั้นปีที่ 1 สโมสรนิสิตคณะแพทยศาสตร์'],
    ['ชมรมดนตรีสากล', 'ชมรมดนตรีสากล สโมสรนิสิตคณะแพทยศาสตร์'],
    ['ฝ่ายวิชาการ', 'ฝ่ายวิชาการ สโมสรนิสิตคณะแพทยศาสตร์'],
    // Anything else is returned unchanged.
    ['หน่วยงานภายนอก', 'หน่วยงานภายนอก'],
]);

test('stripEmoji removes emoji and trims the result', function () {
    expect(Helper::stripEmoji('ค่ายอาสา 🎉'))->toBe('ค่ายอาสา')
        ->and(Helper::stripEmoji('🚀 Rocket 🚗'))->toBe('Rocket')
        ->and(Helper::stripEmoji('  no emoji here  '))->toBe('no emoji here');
});
