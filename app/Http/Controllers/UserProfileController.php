<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController as JetstreamUserProfileController;
use Laravel\Jetstream\Jetstream;

class UserProfileController extends JetstreamUserProfileController {
    public function show(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        return Jetstream::inertia()->render($request, 'Profile/Show', [
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'sessions' => $this->sessions($request)->all(),
            'participants' => $request->user()->load(['participants', 'participants.project', 'participants.project.department'])->participants,
        ]);
    }

    public function printMyProjects(Request $request)
    {
        $user = $request->user();

        return view('my-projects', ['user' => $user, 'draft' => true]);
        // return response()->view('base64-pdf-viewer', ['encoded' => base64_encode(Pdf::loadView('my-projects', ['user' => $user, 'draft' => true])->output())]);
    }
}
