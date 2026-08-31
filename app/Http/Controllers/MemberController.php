<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            'memberships' => fn ($query) => $query->withSum('transactions as balance', 'quantity')->latest(),
            'attendances' => fn ($query) => $query->with('playSession')->latest()->limit(12),
        ]);

        return view('members.show', compact('member'));
    }
}
