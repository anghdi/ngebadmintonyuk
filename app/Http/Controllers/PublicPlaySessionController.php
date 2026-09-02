<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPlaySessionController extends Controller
{
    public function index(): View
    {
        $playSessions = PlaySession::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->withCount('registrations')
            ->oldest('scheduled_at')
            ->paginate(12);

        return view('public-sessions.index', compact('playSessions'));
    }

    public function show(Request $request, PlaySession $playSession): View
    {
        abort_unless($playSession->status === 'scheduled' && $playSession->scheduled_at->isFuture(), 404);

        $playSession->load(['registrations' => fn ($query) => $query->select('id', 'play_session_id', 'user_id', 'name', 'phone', 'payment_method')->oldest()]);
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
