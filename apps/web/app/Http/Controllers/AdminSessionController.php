<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AdminSessionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:4096'],
        ]);

        $email = User::normalizeLoginEmail($validated['email']);
        $key = 'admin-login:'.hash('sha256', $email.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Zu viele Anmeldeversuche. Bitte versuche es in einer Minute erneut.',
            ]);
        }

        $guard = Auth::guard('web');
        $guard->setRememberDuration(30 * 24 * 60);

        if (! $guard->attemptWhen(
            ['login_email' => $email, 'password' => $validated['password']],
            fn (User $user) => $user->canAccessAdmin(),
            $request->boolean('remember'),
        )) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages(['email' => 'E-Mail oder Passwort ist ungültig.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
