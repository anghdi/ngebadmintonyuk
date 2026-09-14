<?php

namespace App\Http\Controllers;

use App\Actions\GenerateRotationScheduleAction;
use App\Actions\PublishRotationScheduleAction;
use App\Http\Requests\GenerateRotationScheduleRequest;
use App\Http\Requests\PublishRotationScheduleRequest;
use App\Models\PlaySession;
use Illuminate\Http\RedirectResponse;

class RotationScheduleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GenerateRotationScheduleRequest $request, PlaySession $playSession, GenerateRotationScheduleAction $generate): RedirectResponse
    {
        $generate->handle($playSession, $request->integer('sets_per_match'), $request->integer('expected_version'), $request->string('roster_fingerprint')->toString());

        return redirect()->to(route('play-sessions.show', $playSession).'#rotasi')->with('success', 'Draf rotasi dibuat. Review sebelum dipublikasikan.');
    }

    public function publish(PublishRotationScheduleRequest $request, PlaySession $playSession, PublishRotationScheduleAction $publish): RedirectResponse
    {
        $publish->handle($playSession, $request->integer('expected_version'));

        return redirect()->to(route('play-sessions.show', $playSession).'#rotasi')->with('success', 'Rotasi dipublikasikan untuk member.');
    }
}
