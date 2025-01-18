<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\ProjectParticipant;

trait NotifyParticipantsTrait {
    public static function notifyParticipants(Project $project): void {
        $project->participants()->where('type', 'organizer')->get()->each(fn(ProjectParticipant $participant
        ) => $participant->user->notify(new self($project)));
    }
}
