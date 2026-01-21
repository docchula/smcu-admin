<?php

namespace App;

use Illuminate\Support\Carbon;

class Helper {
    public static function buddhistYear(?int $year = null): int {
        return ($year ?? date('Y')) + 543;
    }

    /**
     * Get the current SMCU term year. (starts at 1st March)
     * @return int
     */
    public static function termYear(): int {
        return self::buddhistYear(Carbon::now()->subMonths(2)->year);
    }

    public static function formatDepartmentName(string $departmentName): string {
        if ($departmentName == 'คณะกรรมการบริหาร') {
            return 'สโมสรนิสิตคณะแพทยศาสตร์';
        } elseif (str_starts_with($departmentName, 'คณะกรรมการชั้น') or str_starts_with($departmentName, 'ชมรม') or str_starts_with($departmentName,
                'ฝ่าย')) {
            return $departmentName.' สโมสรนิสิตคณะแพทยศาสตร์';
        }

        return $departmentName;
    }

    public static function stripEmoji($string): string {
        // Comprehensive regex to match and remove various forms of emojis
        $regex_emojis = '/[\x{1F600}-\x{1F64F}]|'. // Emoticons
            '[\x{1F300}-\x{1F5FF}]|'. // Miscellaneous Symbols and Pictographs
            '[\x{1F680}-\x{1F6FF}]|'. // Transport And Map Symbols
            '[\x{2600}-\x{26FF}]|'.   // Miscellaneous Symbols
            '[\x{2700}-\x{27BF}]|'.   // Dingbats
            '[\x{1F900}-\x{1F9FF}]|'. // Supplemental Symbols and Pictographs
            '[\x{1FA00}-\x{1FA6F}]|'. // Chess Symbols
            '[\x{1FA70}-\x{1FAFF}]/u'; // Symbols and Pictographs Extended A

        $clear_string = preg_replace($regex_emojis, '', $string);

        return trim($clear_string);
    }
}
