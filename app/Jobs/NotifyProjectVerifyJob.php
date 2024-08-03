<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Notifications\ClosureVerifyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyProjectVerifyJob implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public int $uniqueFor = 7200;

    public function __construct(public Project $project) {
    }

    public function handle(): void {
        $this->project->participants()->whereIn('type', ['organizer', 'staff'])->where('verify_status', 0)->get()
            ->each(fn(ProjectParticipant $participant) => $participant->user->notify(new ClosureVerifyNotification($this->project)));
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string {
        return $this->project->id;
    }
}
