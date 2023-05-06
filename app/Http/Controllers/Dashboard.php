<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class Dashboard extends Controller {
    public function __invoke(Request $request) {
        return Inertia::render('Dashboard', [
            // 'projectsAwaitingSummary' => $request->user()->projects()->select(['id', 'number', 'year', 'name'])->whereDate('created_at', '>', now()->subYear())->whereNotIn('department_id', [32, 38, 39])->has('approvalDocument')->doesntHave('summaryDocument')->get(),
            'myProjects' => $request->user()->participantAndProjects(),
        ]);
    }
}
