<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Student
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $google_id
 * @property string|null $student_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $roles
 * @property \Illuminate\Database\Eloquent\Collection<\App\Models\ProjectParticipant> $participants
 */
class User extends Authenticatable {
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'student_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'current_team_id',
        'profile_photo_path',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'two_factor_enabled',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'google_id',
        'created_at',
        'updated_at',
        'roles'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'profile_photo_url',
    ];

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(ProjectParticipant::class);
    }

    /**
     * @return Collection<ProjectParticipant>
     */
    public function participantAndProjects(): Collection
    {
        $this->load([
            'participants',
            'participants.project', 'participants.project.approvalDocument', 'participants.project.summaryDocument',
        ]);

        return $this->participants->whereNotNull('project.approvalDocument')->sortByDesc('id')->values();
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Project::class);
    }

    public static function searchQuery(string $keyword = null): ?Builder {
        $query = self::query();
        if (empty($keyword) or strlen($keyword) < 3) {
            return null;
        }
        if (is_numeric($keyword)) {
            $query->where('student_id', $keyword);
        } elseif (str_contains($keyword, '@')) {
            $query->where('email', $keyword);
        } else {
            $query->where('name', 'like', "%$keyword%");
        }

        return $query;
    }
}
