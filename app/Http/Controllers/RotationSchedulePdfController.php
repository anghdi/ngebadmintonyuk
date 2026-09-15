<?php

namespace App\Http\Controllers;

use App\Models\PlaySession;
use App\Services\RotationScheduleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RotationSchedulePdfController extends Controller
{
    public function __invoke(PlaySession $playSession, RotationScheduleService $schedules): Response
    {
        Gate::authorize('admin');

        $registrations = $playSession->registrations()
            ->oldest('id')
            ->limit($playSession->max_players)
            ->get();
        $data = $schedules->viewData($playSession, $registrations, review: true);

        abort_if($data['rotationSchedule'] === null || $data['rotationStale'], 404);

        $filename = implode('-', [
            'rotasi-bermain',
            $playSession->scheduled_at->format('Y-m-d'),
            Str::slug($playSession->venue_name),
        ]).'.pdf';

        return Pdf::loadView('rotations.pdf', [
            'playSession' => $playSession,
            'schedule' => $data['rotationSchedule'],
            'published' => $data['rotationPublished'],
        ])->setPaper('a4', 'landscape')->download($filename);
    }
}
