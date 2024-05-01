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
}
