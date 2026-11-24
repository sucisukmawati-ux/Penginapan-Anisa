<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleSocialiteUser;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! filled(config('services.google.client_id')) || ! filled(config('services.google.client_secret'))) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Login Google belum dikonfigurasi.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()->route('login')
                ->withErrors([
                    'google' => 'Sesi login Google sudah kedaluwarsa atau tidak cocok. Silakan mulai lagi dari halaman login.',
                ]);
        }

        if (! ($googleUser instanceof GoogleSocialiteUser)) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google tidak memberikan alamat email terverifikasi yang valid.']);
        }

        $googleData = $googleUser->getRaw();
        $email = Str::lower((string) $googleUser->getEmail());

        if (
            ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! filled($googleUser->getId())
            || ($googleData['email_verified'] ?? $googleData['verified_email'] ?? false) !== true
        ) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google tidak memberikan alamat email terverifikasi yang valid.']);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($user) {
            if (! $user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
        } else {
            $user = User::create([
                'name' => filled($googleUser->getName())
                    ? $googleUser->getName()
                    : Str::before($email, '@'),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Str::random(64),
                'role' => UserRole::GUEST,
            ]);
        }

        if (
            Features::enabled(Features::twoFactorAuthentication())
            && $user->two_factor_secret
            && (! Fortify::confirmsTwoFactorAuthentication() || $user->two_factor_confirmed_at)
        ) {
            request()->session()->put('login.id', $user->getKey());

            return redirect()->route('two-factor.login');
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
