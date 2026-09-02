<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterPlaySessionsRequest;
use App\Models\PlaySession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;

class PublicPlaySessionController extends Controller
{
    public function index(FilterPlaySessionsRequest $request): View
    {
        $selectedMonth = $request->validated('month');
        $query = PlaySession::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now());
        $availableMonths = (clone $query)
            ->oldest('scheduled_at')
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
            ->withCount('registrations')
            ->oldest('scheduled_at')
            ->paginate(12)
            ->withQueryString();
        $selectedMonthLabel = $selectedMonth
            ? Date::parse($selectedMonth.'-01')->translatedFormat('F Y')
            : null;

        return view('public-sessions.index', compact('playSessions', 'availableMonths', 'selectedMonth', 'selectedMonthLabel'));
    }

    public function show(Request $request, PlaySession $playSession): View
    {
        abort_unless($playSession->status === 'scheduled' && $playSession->scheduled_at->isFuture(), 404);

        $playSession->load(['registrations' => fn ($query) => $query->select('id', 'play_session_id', 'user_id', 'name', 'phone', 'payment_method', 'payment_status', 'attendance_status')->oldest()]);
        $isFull = $playSession->registrations->count() >= $playSession->max_players;
        $registration = $request->user() && ! $request->user()->isAdmin()
            ? $playSession->registrations->firstWhere('user_id', $request->user()->id)
            : null;
        $noShowCount = $request->user() && ! $request->user()->isAdmin()
            ? $request->user()->sessionRegistrations()->where('attendance_status', 'no_show')->count()
            : 0;

        return view('public-sessions.show', compact('playSession', 'registration', 'noShowCount', 'isFull'));
    }
}
