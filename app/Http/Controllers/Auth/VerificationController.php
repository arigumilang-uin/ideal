<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationController extends Controller
{
    /**
     * Show the email verification notice.
     * This is shown when user tries to access a route that requires verified email.
     */
    public function notice(Request $request): View|RedirectResponse
    {
        // If already verified, redirect to dashboard
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email');
    }

    /**
     * Mark the email as verified.
     * This is called when user clicks the verification link in their email.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        // Mark email as verified
        $request->fulfill();

        return redirect()->route('dashboard')->with('success', 'Email berhasil diverifikasi!');
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Link verifikasi baru telah dikirim ke email Anda.');
    }
}
