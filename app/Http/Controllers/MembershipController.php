<?php

namespace App\Http\Controllers;

use App\Actions\GrantMembershipAction;
use App\Http\Requests\StoreMembershipRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class MembershipController extends Controller
{
    public function store(StoreMembershipRequest $request, User $member, GrantMembershipAction $grantMembership): RedirectResponse
    {
        $grantMembership->handle($member, $request->validated(), $request->user());

        return back()->with('success', 'Paket dan kuota member berhasil ditambahkan.');
    }
}
