<?php

namespace App\Http\Controllers;

use App\Actions\DeletePlaySessionAction;
use App\Http\Requests\FilterPlaySessionsRequest;
use App\Http\Requests\StorePlaySessionRequest;
use App\Http\Requests\UpdatePlaySessionRequest;
use App\Models\Membership;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class PlaySessionController extends Controller
{
    public function index(FilterPlaySessionsRequest $request): View
    {
        $selectedMonth = $request->validated('month');
        $query = PlaySession::query();
        $availableMonths = (clone $query)
            ->latest('scheduled_at')
            ->get(['scheduled_at'])
            ->map(fn (PlaySession $playSession): array => [
                'value' => $playSession->scheduled_at->format('Y-m'),
                'label' => $playSession->scheduled_at->translatedFormat('F Y'),
            ])
            ->unique('value')
            ->values();
        $playSessions = $query
            ->when(
                $selectedMonth,
                function ($query, string $month): void {
                    $start = Date::parse($month.'-01')->startOfMonth();

                    $query->whereBetween('scheduled_at', [$start, $start->copy()->endOfMonth()]);
                },
                fn ($query) => $query->whereIn('id', []),
            )
            ->withCount(['attendances', 'registrations'])
            ->latest('scheduled_at')
            ->paginate(15)
            ->withQueryString();
        $selectedMonthLabel = $selectedMonth
            ? Date::parse($selectedMonth.'-01')->translatedFormat('F Y')
            : null;

        return view('play-sessions.index', compact('playSessions', 'availableMonths', 'selectedMonth', 'selectedMonthLabel'));
    }

    public function store(StorePlaySessionRequest $request): RedirectResponse
    {
        $playSession = PlaySession::create($request->validated() + ['created_by' => $request->user()->id]);

        return redirect()->route('play-sessions.show', $playSession)->with('success', 'Sesi bermain berhasil dibuat.');
    }

    public function edit(PlaySession $playSession): View
    {
        return view('play-sessions.edit', compact('playSession'));
    }

    public function update(UpdatePlaySessionRequest $request, PlaySession $playSession): RedirectResponse
    {
        $playSession->update($request->validated());

        return redirect()->route('play-sessions.show', $playSession)->with('success', 'Sesi bermain berhasil diperbarui.');
    }

    public function destroy(PlaySession $playSession, DeletePlaySessionAction $deletePlaySession): RedirectResponse
    {
        $deletePlaySession->handle($playSession);

        return redirect()->route('play-sessions.index')->with('success', 'Sesi bermain berhasil dihapus.');
    }

    public function show(PlaySession $playSession): View
    {
        $playSession->load('attendances.transaction');
        $registrations = $playSession->registrations()->with('user:id,name')->oldest('id')->get();
        $confirmedRegistrations = $registrations->take($playSession->max_players)->values();
        $waitingRegistrations = $registrations->slice($playSession->max_players)->values();
        $noShowCounts = SessionRegistration::query()
            ->whereIn('user_id', $registrations->pluck('user_id')->filter())
            ->where('attendance_status', 'no_show')
            ->select('user_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
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

        return view('play-sessions.show', compact('playSession', 'members', 'attendances', 'compatibleBalances', 'registrations', 'confirmedRegistrations', 'waitingRegistrations', 'noShowCounts'));
    }
}
