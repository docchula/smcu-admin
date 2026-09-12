<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranscriptController extends Controller {
    public function index(Request $request) {
        $this->authorize('view-transcript');
        $keyword = $request->input('search');
        /** @var User $user */
        $user = User::searchQuery($keyword)?->with(['participants', 'participants.project'])->first();
        $user?->participants->where('project_type', 'App\Models\Project')->load('project.department');

        return Inertia::render('TranscriptView', [
            'user' => $user,
            'transcript' => $user?->getActivityTranscript(),
            'keyword' => $keyword,
        ]);
    }

    public function print(User $user) {
        $this->authorize('view-transcript');

        return self::printTranscript($user, false);
    }

    public static function printTranscript(User $user, bool $isDraft = true) {
        $link = $user->getTranscriptLink();

        return view('my-projects', [
            'user' => $user,
            'draft' => $isDraft,
            'link' => $link,
            'qrCode' => Helper::transcriptQrCode($link),
        ]);
    }

    public function publicView($identifier) {
        $user = User::where('public_identifier', $identifier)->with(['participants', 'participants.project'])->firstOrFail();
        $user?->participants->where('project_type', 'App\Models\Project')->load('project.department');

        return Inertia::render('TranscriptPublicView', [
            'user_name' => $user->name,
            'transcript' => $user?->getActivityTranscript()->filter(fn($item) => $item['approve_status'] >= 1),
        ]);
    }
}
