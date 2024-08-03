<?php

namespace App\Http\Controllers;

use App\Models\ProjectParticipant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Dashboard extends Controller {
    public function __invoke(Request $request) {
        return Inertia::render('Dashboard', [
            // 'projectsAwaitingSummary' => $request->user()->projects()->select(['id', 'number', 'year', 'name'])->whereDate('created_at', '>', now()->subYear())->whereNotIn('department_id', [32, 38, 39])->has('approvalDocument')->doesntHave('summaryDocument')->get(),
            'myProjects' => $request->user()->participantAndProjects()->map(function (ProjectParticipant $participant) {
                $participant->project->closure_status = $participant->project->getClosureStatus();
                $participant->verify_status = !empty($participant->verify_status);
                $participant->makeVisible('verify_status');

                return $participant;
            }),
        ]);
    }
}
