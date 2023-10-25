<?php

namespace App\Models;

use Cache;
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
        return Cache::remember('department-list', 6000, fn() => self::select('id', 'super_id', 'name', 'sequence')->orderBy('sequence')->get());
    }
}
