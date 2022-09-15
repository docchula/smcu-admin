<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use VestaClient;

class GoogleController extends Controller {
    public function redirectToGoogle() {
        return Socialite::driver('google')->with(['hd' => 'docchula.com'])->redirect();
    }

    public function handleGoogleCallback(): \Illuminate\Http\RedirectResponse {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            return redirect('/')->with('flash.banner', 'Invalid state. Try again.')->with('flash.bannerStyle', 'danger');
        }
        if (!Str::endsWith($googleUser->email, '@docchula.com')) {
            return redirect('/')->with('flash.banner', 'Invalid email')->with('flash.bannerStyle', 'danger');
        }
        if ($user = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->email)->first()) {
            /** @var User $user */
            if ($user->email == $googleUser->email) {
                // Double check Google ID as the email may be changed
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->id;
                    $user->saveOrFail();
                } elseif ($user->google_id != $googleUser->id) {
                    return redirect('/')->with('flash.banner', 'Invalid credential, please contact administrator.')->with('flash.bannerStyle', 'danger');
                }
            }
        } else {
            $user = new User([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]);
        }
        if (!isset($user->student_id) and VestaClient::isEnabled()) {
            // Retrieve student information
            $response = VestaClient::retrieveStudent($user->email, $user->email);
            if ($response->successful()) {
                $vestaUser = $response->json();
                if (isset($vestaUser['first_name'])) {
                    $user->name = ($vestaUser['title'] ?? '') . $vestaUser['first_name'] . ' ' . $vestaUser['last_name'];
                    $user->student_id = $vestaUser['student_id'];
                    $user->saveOrFail();
                }
            }
        }

        if (isset($user->student_id)) {
            Auth::login($user);

            return redirect()->intended('dashboard');
        } else {
            Log::info('Unable to find student information.', ['email' => $user->email]);
            return redirect('/')->with('flash.banner', 'Unable to find student information, please contact administrator.')->with('flash.bannerStyle', 'danger');
        }
    }
}
