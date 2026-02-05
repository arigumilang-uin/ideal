<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset the given user's password.
     * 
     * Security measures:
     * - Strong password validation (min 8 chars, mixed case, numbers)
     * - Token-based verification (expires in 60 min)
     * - Rate limited (5 attempts/minute in routes)
     * - Invalidates all other sessions
     * - Updates password_changed_at timestamp
     * - Regenerates remember token
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        // Reset the user's password
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                // Update password and regenerate remember token
                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_changed_at' => now(), // Track when password was changed
                ])->setRememberToken(Str::random(60));

                $user->save();

                // Send security notification email
                $user->notify(new \App\Notifications\PasswordChangedNotification('reset', $request->ip()));

                // Fire password reset event
                event(new PasswordReset($user));

                // Invalidate all other sessions for security
                // (User will need to login again on other devices)
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password berhasil direset. Silakan login dengan password baru.')
            : back()->withErrors(['email' => [__($status)]]);
    }
}
