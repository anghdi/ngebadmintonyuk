<?php

namespace App\Http\Controllers;

use App\Actions\GenerateRotationScheduleAction;
use App\Http\Requests\GenerateRotationScheduleRequest;
use App\Models\PlaySession;
use Illuminate\Http\RedirectResponse;

class RotationScheduleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GenerateRotationScheduleRequest $request, PlaySession $playSession, GenerateRotationScheduleAction $generate): RedirectResponse
    {
        $generate->handle($playSession, $request->integer('round_count'), $request->integer('expected_version'), $request->string('roster_fingerprint')->toString());

        return redirect()->to(route('play-sessions.show', $playSession).'#rotasi')->with('success', 'Jadwal rotasi siap dilihat member.');
    }
}
