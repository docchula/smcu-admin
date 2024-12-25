<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model {
    use SoftDeletes;

    protected $fillable = ['name', 'organization', 'period_start', 'period_end', 'description', 'duration', 'role'];

    protected function casts(): array {
        return [
            'participants' => AsCollection::class,
            'period_start' => 'date:j M Y',
            'period_end' => 'date:j M Y',
        ];
    }

    public static function searchQuery(?string $keyword = null, ?array $columns = []): Builder {
        $query = self::query();
        if (!empty($keyword)) {
            // comma (,) means 'or'
            $keywordList = explode(',', $keyword);
            foreach ($keywordList as $keywordListItem) {
                $keywordListItem = trim($keywordListItem);
                if (!empty($keywordListItem)) {
                    // $query->orWhere('name', 'LIKE', '%'.$keywordListItem.'%');
                    if (is_numeric($keyword)) {
                        $query->orWhere('id', $keywordListItem);
                    } else {
                        $query->orWhere('name', 'LIKE', '%'.$keywordListItem.'%');
                    }
                }
            }
        }

        return $query;
    }
}
