<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $exception) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Google login failed. Please try again.',
                ]);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'No admin account is registered with this Google email.',
                ]);
        }

        if ($user->type !== 'admin') {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'This Google account is not allowed to access the admin portal.',
                ]);
        }

        if (! $user->active) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'This admin account is inactive.',
                ]);
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'last_login_at' => now(),

            // bypasses local temporary password flow.
            'must_change_password' => false,
            'temporary_password_expires_at' => null,
        ]);

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}