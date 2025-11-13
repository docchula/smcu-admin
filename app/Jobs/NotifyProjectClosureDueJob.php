<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Notifications\ClosureDueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyProjectClosureDueJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void {
        // Start sending notification after 2 days of project end time
        Project::where('created_at', '>=', now()->subYear())
            ->where('year', '>=', 2567)
            ->where('period_end', '<=', now()->subDays(2))
            ->where('period_end', '>=', now()->subDays(20))
            ->whereNull('closure_reminded_at')
            ->get()
            ->filter(fn(Project $project) => $project->canSubmitClosure() and $project->documents()->where('tag', 'summary')->doesntExist())
            ->each(function (Project $project) {
                $project->participants()->with('user')->get()
                    ->reject(fn($participant) => $participant->type == 'attendee' and $participant->user?->student_id < 6700000000)
                    ->pipe(function (Collection $participants) {
                        // select all organizers + other participants not more than 30 people
                        $organizers = $participants->where('type', 'organizer');
                        $others = $participants->where('type', '!=', 'organizer')->take(max(0, 30 - $organizers->count()));

                        return $organizers->merge($others);
                    })
                    ->each(fn(ProjectParticipant $participant) => $participant->user->notify(new ClosureDueNotification($project, $participant)));
                $project->update(['closure_reminded_at' => now()]);
            });
    }
}
