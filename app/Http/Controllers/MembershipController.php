<?php

namespace App\Http\Controllers;

use App\Actions\DeleteMembershipAction;
use App\Actions\GrantMembershipAction;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Requests\UpdateMembershipRequest;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class MembershipController extends Controller
{
    public function store(StoreMembershipRequest $request, User $member, GrantMembershipAction $grantMembership): RedirectResponse
    {
        $grantMembership->handle($member, $request->validated(), $request->user());

        return back()->with('success', 'Paket dan kuota member berhasil ditambahkan.');
    }

    public function update(UpdateMembershipRequest $request, User $member, Membership $membership): RedirectResponse
    {
        $membership->update($request->validated());

        return back()->with('success', 'Paket member berhasil diperbarui.');
    }

    public function destroy(User $member, Membership $membership, DeleteMembershipAction $deleteMembership): RedirectResponse
    {
        abort_unless($membership->user_id === $member->id, 404);
        $deleteMembership->handle($membership);

        return back()->with('success', 'Paket member berhasil dihapus.');
    }
}
