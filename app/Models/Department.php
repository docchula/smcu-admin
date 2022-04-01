<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string      $name
 * @property string|null $group
 * @property int         $sequence
 */
class Department extends Model {
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public static function optionList() {
        return self::select('id', 'super_id', 'name')->orderBy('sequence')->get();
    }
}
