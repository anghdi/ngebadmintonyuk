<?php

namespace App\Http\Controllers;

use App\Actions\DeleteMemberAction;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = User::query()
            ->where('role', 'member')
            ->with(['memberships' => fn ($query) => $query->withSum('transactions as balance', 'quantity')->latest()])
            ->latest()
            ->paginate(15);

        return view('members.index', compact('members'));
    }

    public function show(User $member): View
    {
        abort_if($member->isAdmin(), 404);
        $member->load([
            'memberships' => fn ($query) => $query
                ->withCount(['attendances', 'topUpRequests'])
                ->withSum('transactions as balance', 'quantity')
                ->with(['transactions' => fn ($query) => $query->with('creator')->latest()->limit(5)])
                ->latest(),
            'attendances' => fn ($query) => $query->with('playSession')->latest()->limit(12),
        ]);

        return view('members.show', compact('member'));
    }

    public function update(UpdateMemberRequest $request, User $member): RedirectResponse
    {
        $member->update($request->validated());

        return back()->with('success', 'Data member berhasil diperbarui.');
    }

    public function destroy(User $member, DeleteMemberAction $deleteMember): RedirectResponse
    {
        $deleteMember->handle($member);

        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus.');
    }
}
