<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaySessionRequest;
use App\Models\Membership;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlaySessionController extends Controller
{
    public function index(): View
    {
        $playSessions = PlaySession::query()->withCount('attendances')->latest('scheduled_at')->paginate(15);

        return view('play-sessions.index', compact('playSessions'));
    }

    public function store(StorePlaySessionRequest $request): RedirectResponse
    {
        $playSession = PlaySession::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('play-sessions.show', $playSession)->with('success', 'Sesi bermain berhasil dibuat.');
    }

    public function show(PlaySession $playSession): View
    {
        $playSession->load('attendances.transaction');
        $attendances = $playSession->attendances->keyBy('user_id');
        $members = User::query()
            ->where('role', 'member')
            ->with(['memberships' => fn ($query) => $query->withSum('transactions as balance', 'quantity')])
            ->orderBy('name')
            ->get();
        $compatibleBalances = $members->mapWithKeys(function (User $member) use ($playSession): array {
            $balance = $member->memberships
                ->filter(fn (Membership $membership): bool => $membership->status === 'active'
                    && $membership->venue_name === $playSession->venue_name
                    && $membership->court_name === $playSession->court_name
                    && $membership->price_per_session === $playSession->price_per_session
                    && $membership->starts_on->lte($playSession->scheduled_at)
                    && (! $membership->expires_on || $membership->expires_on->gte($playSession->scheduled_at)))
                ->sum('balance');

            return [$member->id => (int) $balance];
        });

        return view('play-sessions.show', compact('playSession', 'members', 'attendances', 'compatibleBalances'));
    }
}
