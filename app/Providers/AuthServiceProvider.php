<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin-action', function (User $user) {
            return in_array('admin', explode(',', $user->roles));
        });
        Gate::define('faculty-action', function (User $user) {
            // Role: Associate/Assistant Dean for Student Affairs
            return in_array('faculty', explode(',', $user->roles));
        });
        Gate::define('download-action', function (User $user) {
            // Role: Student Affairs supporting staff
            return in_array('download', explode(',', $user->roles));
        });
        Gate::define('update-document', function (User $user, Document $document) {
            return is_null($document->id) OR ($document->user_id === $user->id) OR $user->can('admin-action');
        });
        Gate::define('update-project', function (User $user, Project $project) {
            return (is_null($project->id)
                or $user->can('admin-action') or $user->can('faculty-action')
                OR (
                    ($project->user_id === $user->id or $project->participants()->where('user_id', $user->id)->where('type', 'organizer')->exists())
                    AND $project->created_at->diffInMonths(now()) < 15 // Created in the last 15 months
                )) ? Response::allow() : Response::deny('You are not authorized to update this project.');
        });
        Gate::define('create-activity', function (User $user) {
            $userRoles = explode(',', $user->roles);

            return in_array('faculty', $userRoles) or in_array('activity', $userRoles);
        });
        Gate::define('view-transcript', function (User $user) {
            return in_array('view_transcript', explode(',', $user->roles)) or $user->can('create-activity');
        });
    }
}
