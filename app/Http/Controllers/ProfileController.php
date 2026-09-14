<?php

namespace App\Http\Controllers;

use App\Actions\UpdateMemberProfileAction;
use App\Http\Requests\UpdateProfilePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $member = $request->user();
        abort_if($member->isAdmin(), 403);
        $attendanceCount = $member->attendances()->where('status', 'present')->count();
        $remainingCredits = (int) $member->memberships()->withSum('transactions as balance', 'quantity')->get()->sum('balance');

        return view('profile.edit', compact('member', 'attendanceCount', 'remainingCredits'));
    }

    public function update(UpdateProfileRequest $request, UpdateMemberProfileAction $updateProfile): RedirectResponse
    {
        $updateProfile->handle($request->user(), $request->profileData(), $request->file('avatar'));

        return redirect()->route('profile.edit')->with('success', 'Profil tersimpan.');
    }

    public function password(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password'), 'remember_token' => Str::random(60)]);
        Auth::login($request->user(), remember: true);
        $request->session()->regenerate();

        return redirect()->route('profile.edit')->with('success', 'Kata sandi diperbarui.');
    }

    public function avatar(Request $request): StreamedResponse
    {
        abort_if($request->user()->isAdmin() || $request->user()->avatar_path === null, 404);

        return Storage::disk('local')->response($request->user()->avatar_path, headers: ['Cache-Control' => 'private, no-store']);
    }
}
