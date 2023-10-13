<?php

namespace App\Http\Controllers;

use App\Models\User;
use Crypt;
use Google\Service\Gmail;
use Google\Service\PeopleService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Storage;
use VestaClient;

class GoogleController extends Controller {
    public const ADMIN_ACCOUNT_TOKEN_FILE = '.admin_account_token';
    public const ADMIN_ACCOUNT_EMAIL = 'itdivision@docchula.com';

    public function redirectToGoogle() {
        return Socialite::driver('google')->with(['hd' => 'docchula.com'])->redirect();
    }

    public function redirectToGoogleWithGmailAccess()
    {
        return Socialite::driver('google')->with([
            'access_type' => 'offline',
            'hd' => 'docchula.com',
            'login_hint' => self::ADMIN_ACCOUNT_EMAIL,
        ])->scopes([
            PeopleService::USERINFO_PROFILE,
            PeopleService::USERINFO_EMAIL,
            Gmail::GMAIL_READONLY,
        ])->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $exception) {
            return redirect('/')->with('flash.banner', 'Invalid state. Try again.')->with('flash.bannerStyle', 'danger');
        }
        if (!Str::endsWith($googleUser->email, '@docchula.com')) {
            return redirect('/')->with('flash.banner', 'Invalid email')->with('flash.bannerStyle', 'danger');
        }
        if ($googleUser->email == self::ADMIN_ACCOUNT_EMAIL and in_array(Gmail::GMAIL_READONLY, $googleUser->approvedScopes)) {
            // For admin account with authorized permission: save token in file for later use
            $oldToken = [];
            try {
                $oldToken = Crypt::decrypt(Storage::get(GoogleController::ADMIN_ACCOUNT_TOKEN_FILE));
            } catch (DecryptException) {
            }
            Storage::put(self::ADMIN_ACCOUNT_TOKEN_FILE, Crypt::encrypt([
                'access_token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken ?? $oldToken['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds($googleUser->expiresIn),
                'approved_scopes' => $googleUser->approvedScopes,
            ]));
            session()->flash('flash.banner', 'Admin account token saved to file.');
            $user = User::firstOrCreate(['email' => $googleUser->email], [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
            ]);
        } elseif ($user = User::where('google_id', $googleUser->id)->orWhere('email', $googleUser->email)->first()) {
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
        if ((!isset($user->student_id) OR preg_match('/^[a-z\s]*$/i', $user->name)) and VestaClient::isEnabled()) {
            // Retrieve student information, if student id not already set or name is in English
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

        if (isset($user->student_id) or $user->email == self::ADMIN_ACCOUNT_EMAIL) {
            Auth::login($user);

            return redirect()->intended('dashboard');
        } else {
            Log::info('Unable to find student information.', ['email' => $user->email]);
            return redirect('/')->with('flash.banner', 'Unable to find student information, please contact administrator.')->with('flash.bannerStyle', 'danger');
        }
    }
}
