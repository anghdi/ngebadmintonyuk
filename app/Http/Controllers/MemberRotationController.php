<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Services\RotationScheduleService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberRotationController extends Controller
{
    public function index(Request $request, RotationScheduleService $schedules): View
    {
        $sessions = PlaySession::query()->where('status', '!=', 'cancelled')
            ->whereNotNull('rotation_schedule->published_at')
            ->whereHas('registrations', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['registrations' => fn ($query) => $query->select('id', 'play_session_id', 'user_id', 'guest_id', 'name')->oldest('id')])
            ->latest('scheduled_at')->paginate(12);
        $rotationStates = $sessions->getCollection()->mapWithKeys(fn (PlaySession $session): array => [
            $session->id => $schedules->viewData($session, $session->registrations->take($session->max_players)->values()),
        ]);

        return view('rotations.index', compact('sessions', 'rotationStates'));
    }

    public function show(PlaySession $playSession, RotationScheduleService $schedules): View
    {
        abort_unless($playSession->status !== 'cancelled' && ($playSession->rotation_schedule['published_at'] ?? null) !== null, 404);
        $registrations = $playSession->registrations()->oldest('id')->limit($playSession->max_players)->get();

        return view('rotations.show', ['playSession' => $playSession] + $schedules->viewData($playSession, $registrations));
    }
}
