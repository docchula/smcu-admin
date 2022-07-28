<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $year
 * @property int         $number
 * @property int|null    $number_to
 * @property string      $title
 * @property string|null $recipient
 * @property int|null    $user_id
 * @property int|null    $project_id
 * @property int|null    $department_id
 * @property string|null $attachment_path
 * @property Carbon      $created_at
 * @property Carbon|null $updated_at
 */
class Document extends Model {
    use HasFactory;

    protected $fillable = ['title', 'recipient', 'department_id', 'project_id', 'user_id'];
    protected $casts = ['created_at' => 'datetime:j M Y'];
    protected $hidden = ['user_id', 'attachment_path'];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Department::class)->select('id', 'name');
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Project::class)->select('id', 'name', 'number', 'year', 'department_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(User::class)->select('id', 'name');
    }

    public static function latestOfYear(?int $year): ?self {
        return self::where('year', $year ?? (date('Y') + 543))->orderByDesc('number')->first();
    }
}
