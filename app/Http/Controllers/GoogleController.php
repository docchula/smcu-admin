<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\VestaService;
use GuzzleHttp\Client;
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
            $user = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
            ]);
        }
        if (!isset($user->student_id) and VestaService::isEnabled()) {
            // Retrieve student information
            $client = new Client(['base_uri' => 'https://vesta.mdcu.keendev.net/juno/v1/']);
            // Send a request to https://foo.com/api/test
            $response = $client->get('students/' . $user->email . '?access_level=8', [
                'headers' => [
                    'Authorization' => 'Bearer ' . VestaService::generateProxyIdToken($user->google_id, $user->email, $user->name)
                ]
            ]);
            if ($response->getStatusCode() == 200) {
                $vestaUser = json_decode($response->getBody());
                if (isset($vestaUser->first_name)) {
                    $user->name = ($vestaUser->title ?? '') . $vestaUser->first_name . ' ' . $vestaUser->last_name;
                    $user->student_id = $vestaUser->student_id;
                    $user->saveOrFail();
                }
            }
        }

        if (isset($user->student_id)) {
            Auth::login($user);

            return redirect()->intended('dashboard');
        } else {
            return redirect('/')->with('flash.banner', 'Unable to find student information, please contact administrator.')->with('flash.bannerStyle', 'danger');
        }
    }
}
