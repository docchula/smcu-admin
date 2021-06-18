<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $id
 * @property Carbon                          $created_at
 * @property Carbon|null                     $updated_at
 * @property string                          $name
 * @property string|null                     $advisor
 * @property string|null                     $type
 * @property string|null                     $recurrence
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

    protected $guarded = ['id', 'created_at', 'updated_at', 'approval_document_id', 'user_id'];
    protected $casts = [
        'created_at' => 'datetime:j F Y',
        'updated_at' => 'datetime:j F Y',
        'period_start' => 'date:j F Y',
        'period_end' => 'date:j F Y',
        'objectives' => 'array',
        'expense' => 'array',
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Document::class);
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(ProjectParticipant::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(User::class);
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
}
