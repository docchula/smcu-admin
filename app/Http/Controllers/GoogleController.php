<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller {
    public function redirectToGoogle() {
        return Socialite::driver('google')->with(['hd' => 'docchula.com'])->redirect();
    }

    public function handleGoogleCallback(): \Illuminate\Http\RedirectResponse {
        try {
            $user = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            return redirect('/')->with('flash.banner', 'Invalid state. Try again.')->with('flash.bannerStyle', 'danger');
        }
        if (!Str::endsWith($user->email, 'docchula.com')) {
            return redirect('/')->with('flash.banner', 'Invalid email')->with('flash.bannerStyle', 'danger');
        }
        $finduser = User::where('google_id', $user->id)->first();
        if ($finduser) {
            Auth::login($finduser);

            return redirect()->intended('dashboard');
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'google_id' => $user->id,
                'password' => encrypt('123456dummy')
            ]);
            Auth::login($newUser);

            return redirect()->intended('dashboard');
        }
    }
}
