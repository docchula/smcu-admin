<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TranscriptController extends Controller {
    public function index(Request $request) {
        $this->authorize('faculty-action');
        $keyword = $request->input('search');

        return Inertia::render('TranscriptView', [
            'user' => User::searchQuery($keyword)?->with(['participants', 'participants.project', 'participants.project.department'])->first(),
            'keyword' => $keyword,
        ]);
    }

    public function print(User $user) {
        $this->authorize('faculty-action');

        return view('my-projects', ['user' => $user]);
        // return response()->view('base64-pdf-viewer', ['encoded' => base64_encode(Pdf::loadView('my-projects', ['user' => $user])->output())]);
    }
}
