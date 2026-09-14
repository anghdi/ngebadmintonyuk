<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommunityProfileController extends Controller
{
    public function show(User $member): View
    {
        abort_unless($member->role === 'member', 404);

        return view('profile.member', ['profile' => [
            'id' => $member->id,
            'name' => $member->name,
            'nickname' => $member->nickname,
            'playing_level' => $member->playing_level,
            'has_avatar' => $member->avatar_path !== null,
            'initials' => $member->initials(),
        ]]);
    }

    public function avatar(User $member): StreamedResponse
    {
        abort_unless($member->role === 'member' && $member->avatar_path !== null && Storage::disk('local')->exists($member->avatar_path), 404);

        return Storage::disk('local')->response($member->avatar_path, headers: ['Cache-Control' => 'private, no-store']);
    }
}
