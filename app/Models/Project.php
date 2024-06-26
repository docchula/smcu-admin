<?php

namespace App\Models;

use App\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @property int                             $id
 * @property Carbon                          $created_at
 * @property Carbon|null                     $updated_at
 * @property string                          $name
 * @property string|null                     $advisor
 * @property string|null                     $type
 * @property string|null                     $recurrence
 * @property float|null $duration
 * @property string|null $estimated_attendees
 * @property int                             $year
 * @property int                             $number
 * @property Carbon|null                     $period_start
 * @property Carbon|null                     $period_end
 * @property string|null                     $background
 * @property string|null                     $aims
 * @property string|null                     $outcomes
 * @property array|null                      $objectives
 * @property array|null                      $expense
 * @property int|null                        $user_id
 * @property int|null                        $department_id
 * @property int|null                        $approval_document_id
 * @property Department                      $department
 * @property User                            $user
 * @property Collection|ProjectParticipant[] $participants
 */
class Project extends Model {
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at', 'approval_document_id', 'user_id', 'year', 'number'];
    protected $casts = [
        'created_at' => 'datetime:j F Y',
        'updated_at' => 'datetime:j F Y',
        'period_start' => 'date:j M Y',
        'period_end' => 'date:j M Y',
        'objectives' => 'array',
        'expense' => 'array',
    ];
    protected $hidden = ['user_id'];

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class)->select('id', 'name');
    }

    public function documents(): HasMany {
        return $this->hasMany(Document::class);
    }

    public function approvalDocument(): HasOne {
        return $this->hasOne(Document::class)->ofMany([
            'id' => 'max',
        ], function ($query) {
            $query->where('tag', 'approval');
        });
    }

    public function summaryDocument(): HasOne {
        return $this->hasOne(Document::class)->ofMany([
            'id' => 'max',
        ], function ($query) {
            $query->where('tag', 'summary');
        });
    }

    public function participants(): HasMany {
        return $this->hasMany(ProjectParticipant::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->select('id', 'name');
    }

    public function setPeriodStartAttribute($value) {
        $this->attributes['period_start'] = new Carbon($value);
    }

    public function setPeriodEndAttribute($value) {
        $this->attributes['period_end'] = new Carbon($value);
    }

    public function getNumber(): string {
        return $this->year . '-' . $this->number;
    }

    public static function latestOfYear(?int $year): ?self {
        return self::where('year', $year ?? (date('Y') + 543))->orderByDesc('number')->first();
    }

    public function castDateAsDateString(): static {
        $this->casts['period_start'] = 'date:Y-m-d';
        $this->casts['period_end'] = 'date:Y-m-d';

        return $this;
    }

    public static function searchQuery(?string $keyword = null, ?array $columns = []): Builder {
        $query = self::query()->select(['id', 'year', 'number', 'name', 'department_id', 'created_at', 'period_start', 'period_end', ...$columns])->with(['department']);
        if (empty($keyword)) {
            $currentBE = Helper::buddhistYear();
            $query->whereBetween('year', [$currentBE - 1, $currentBE + 1]);
        } else {
            // comma (,) means 'or'
            $keywordList = explode(',', $keyword);
            foreach ($keywordList as $keywordListItem) {
                $keywordListItem = trim($keywordListItem);
                if (!empty($keywordListItem)) {
                    // $query->orWhere('name', 'LIKE', '%'.$keywordListItem.'%');
                    if (preg_match("/^25\d{2}-\d{1,3}/", $keywordListItem)) { // format: 2567-1 or 2567-123
                        $query->orWhere(function (Builder $query) use ($keywordListItem) {
                            $parts = explode('-', $keywordListItem, 2);
                            $query->where('year', $parts[0]);
                            $query->where('number', $parts[1]);
                        });
                    } elseif (is_numeric($keyword)) {
                        $query->orWhere(($keywordListItem > 2500) ? 'year' : 'number', $keywordListItem);
                    } else {
                        $query->orWhere('name', 'LIKE', '%'.$keywordListItem.'%');
                    }
                }
            }
        }

        return $query;
    }

    public static function advisorList(): \Illuminate\Support\Collection
    {
        return Cache::remember('project-advisor', 3600, function () {
            return Project::distinct()->orderBy('advisor')->pluck('advisor')
                ->filter(function (string $name) {
                    return strlen($name) > 10
                        and Str::contains($name, ['อาจารย์', 'อ.', 'ผู้ช่วยศาสตราจารย์', 'ผศ.', 'รองศาสตราจารย์', 'รศ.', 'ศาสตราจารย์', 'ศ.'])
                        and !Str::contains($name, ' และ');
                })->map(function (string $name) {
                    return Str::of($name)->replace('อาจารย์', 'อ.')
                        ->replace('ผู้ช่วยศาสตราจารย์', 'ผศ.')
                        ->replace('รองศาสตราจารย์', 'รศ.')
                        ->replace('ศาสตราจารย์', 'ศ.')
                        ->replace('นายแพทย์', 'นพ.')
                        ->replace('แพทย์หญิง', 'พญ.')
                        ->replace(['  ', '   '], ' ')
                        ->replace('. ', '.')
                        ->trim()->value();
                })->unique()->values();
        });
    }
}
