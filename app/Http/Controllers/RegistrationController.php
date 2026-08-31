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
        Auth::login($member);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun member kamu sudah aktif. Selamat bergabung!');
    }
}
