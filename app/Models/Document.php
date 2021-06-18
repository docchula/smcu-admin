<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $year
 * @property int         $number
 * @property int|null    $number_to
 * @property string      $title
 * @property string      $recipient
 * @property int|null    $user_id
 * @property int|null    $project_id
 * @property int|null    $department_id
 * @property string|null $attachment_path
 */
class Document extends Model {
    use HasFactory;

    protected $fillable = ['title', 'recipient', 'department_id', 'user_id'];
    protected $casts = ['created_at' => 'datetime:j F Y'];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Project::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(User::class);
    }

    public static function latestOfYear(?int $year): ?self {
        return self::where('year', $year ?? (date('Y') + 543))->orderByDesc('id')->first();
    }
}
