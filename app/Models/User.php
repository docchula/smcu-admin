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
        return $this->participants()->where('project_type', 'App\Models\Project')->with([
            'project', 'project.approvalDocument', 'project.summaryDocument',
        ])->orderByDesc('id')->get()->filter(fn(ProjectParticipant $participant) => $participant->project?->approvalDocument)->values();
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany {
        return $this->hasMany(Project::class);
    }

    /**
     * Returns the user's activity transcript, which is both
     * the projects and the activities (external projects) they have participated in.
     */
    public function getActivityTranscript() {
        if (!$this->relationLoaded('participants')) {
            $this->load(['participants', 'participants.project']);
            $this->participants->where('project_type', 'App\Models\Project')->load('project.department');
        }
        $myParticipants = $this->participants->whereNotNull('project')
            ->sortBy('project.period_start')
            ->map(function ($participant): array {
            return ($participant->project_type == 'App\Models\Project') ? [
                'identifier' => $participant->project->year.'-'.$participant->project->number,
                'project_id' => $participant->project->id,
                'name' => $participant->project->name,
                'department' => $participant->project->department?->name ?? '',
                'period_start' => $participant->project->period_start?->format('j M Y'),
                'period_end' => $participant->project->period_end?->format('j M Y'),
                'duration' => $participant->project->duration,
                'role' => $participant->type,
                'approve_status' => $participant->approve_status,
                'title' => $participant->title,
            ] : [
                'identifier' => 'A'.$participant->project->id,
                'activity_id' => $participant->project->id,
                'name' => $participant->project->name,
                'department' => $participant->project->organization,
                'period_start' => $participant->project->period_start?->format('j M Y'),
                'period_end' => $participant->project->period_end?->format('j M Y'),
                'duration' => $participant->project->duration,
                'role' => $participant->type,
                'approve_status' => 1,
                'title' => $participant->title,
            ];
        });

        return $myParticipants->sortByDesc('approve_status')->values();
    }

    public static function searchQuery(string $keyword = null): ?Builder {
        $query = self::query();
        if (empty($keyword) or strlen($keyword) < 3) {
            return null;
        }
        if (is_numeric($keyword) and strlen($keyword) <= 10) {
            if (strlen($keyword) === 10) {
                $query->where('student_id', $keyword);
            } elseif (strlen($keyword) >= 7) {
                $query->where('student_id', 'like', "$keyword%");
            } else {
                return null;
            }
        } elseif (str_contains($keyword, '@')) {
            $query->where('email', $keyword);
        } else {
            $query->where('name', 'like', "%$keyword%");
        }

        return $query;
    }
}
