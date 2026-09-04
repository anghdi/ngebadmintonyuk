<?php

namespace App\Http\Controllers;

use App\Actions\ResetLegacyPushSubscriptionsAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, ResetLegacyPushSubscriptionsAction $resetLegacyPushSubscriptions): RedirectResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        $user = $request->user();

        if (! $user->isAdmin() && $resetLegacyPushSubscriptions->handle($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('legacy_push_reset', true);
        }

        if (! $user->isAdmin()) {
            $request->session()->put('offer_push_notifications', true);
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
