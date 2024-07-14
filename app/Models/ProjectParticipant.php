<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $user_id
 * @property int         $project_id
 * @property string      $type
 * @property string|null $title
 * @property User        $user
 * @property Project     $project
 */
class ProjectParticipant extends Model {
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'title'];
    protected $visible = ['id', 'type', 'title', 'user', 'project', 'verify_status'];
    protected $casts = [
        'reject_participants' => AsArrayObject::class,
    ];
    public const TYPES_OPTIONS = ['organizer' => 'ผู้รับผิดชอบ', 'staff' => 'ผู้ปฏิบัติงาน', 'attendee' => 'ผู้เข้าร่วม'];
    public const TYPES_RANK = ['organizer' => 0, 'staff' => 1, 'attendee' => 2];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Project::class);
    }
}
