<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int         $id
 * @property int         $year
 * @property int         $number
 * @property int|null    $number_to
 * @property string      $title
 * @property string|null $recipient
 * @property string|null $tag
 * @property int|null    $user_id
 * @property int|null    $project_id
 * @property int|null    $department_id
 * @property string|null $attachment_path
 * @property string|null $approved_path
 * @property string|null $status
 * @property Carbon      $created_at
 * @property Carbon|null $updated_at
 * @property \App\Models\Project|null $project
 */
class Document extends Model {
    use HasFactory;

    protected $fillable = ['title', 'recipient', 'tag', 'department_id', 'project_id', 'user_id'];
    protected $casts = ['created_at' => 'datetime:j M Y'];
    protected $hidden = ['user_id', 'attachment_path', 'approved_path'];

    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_UNDELIVERED = 'UNDELIVERED'; // Email delivery failure

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class)->select('id', 'name');
    }

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class)->select('id', 'name', 'number', 'year', 'department_id', 'objectives', 'expense');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)->select('id', 'name');
    }

    public static function latestOfYear(?int $year): ?self {
        return self::where('year', $year ?? (date('Y') + 543))->orderByDesc('number')->first();
    }
}
