<?php

namespace App\Models;

use Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personnel extends Model
{
    protected $fillable = ['name', 'name_en', 'position', 'position_en', 'year', 'sequence', 'supervisor', 'email', 'department_id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class)->select('id', 'name');
    }

    public static function getYear($year = null): Collection
    {
        if (empty($year)) {
            $year = self::getYearList()[0];
        }

        return Cache::remember('personnel-year-'.$year, 7200, fn() => self::where('year', $year)->orderBy('sequence')->get());
    }

    public static function getYearList(): array
    {
        return Cache::remember('personnel-year-list', 7200, fn() => self::pluck('year')->unique()->sortDesc()->toArray());
    }
}
