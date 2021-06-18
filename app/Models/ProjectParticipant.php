<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Project::class);
    }
}
