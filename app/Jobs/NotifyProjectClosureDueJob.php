<?php

namespace App\Jobs;

use App\Models\Project;
use App\Notifications\ClosureDueNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
            ->whereNull('closure_reminded_at')
            ->get()
            ->filter(fn(Project $project) => $project->canSubmitClosure() and $project->documents()->where('tag', 'summary')->isEmpty())
            ->each(function (Project $project) {
                $project->participants()->with('user')->get()
                    ->reject(fn($participant) => $participant->type == 'attendee' and $participant->user?->student_id < 6700000000)
                    ->each(fn($participant) => $participant->user->notify(new ClosureDueNotification($project, $participant)));
                $project->update(['closure_reminded_at' => now()]);
            });
    }
}
