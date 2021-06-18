<?php

namespace App;

class Helper {
    public static function buddhistYear(?int $year = NULL) {
        return ($year ?? date('Y')) + 543;
    }
}
