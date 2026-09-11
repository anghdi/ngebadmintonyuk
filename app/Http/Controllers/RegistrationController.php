<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterMemberRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterMemberRequest $request): RedirectResponse
    {
        $member = User::create($request->validated());
        Auth::login($member, remember: true);
        $request->session()->regenerate();

        return redirect()->route('notifications.setup')->with('success', 'Akun kamu sudah aktif. Aktifkan notifikasi untuk melanjutkan.');
    }
}
