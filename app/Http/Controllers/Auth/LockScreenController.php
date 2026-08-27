<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LockScreenController extends Controller
{
    /**
     * Display the lock screen view.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $request->session()->put('session_locked', true);

        return view('auth.lock');
    }

    /**
     * Lock the current session manually or due to inactivity.
     */
    public function lock(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $request->session()->put('session_locked', true);
        }

        return redirect()->route('lock');
    }

    /**
     * Unlock the session with password verification.
     */
    public function unlock(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => __('Silakan masukkan password untuk membuka kunci sesi.'),
        ]);

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => __('Password yang Anda masukkan tidak sesuai.'),
            ]);
        }

        $request->session()->forget('session_locked');
        $request->session()->put('last_activity', time());

        return redirect()->intended(route('dashboard'));
    }
}
