<?php

namespace App\Http\Controllers;

use App\Actions\RecordAttendanceAction;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function update(UpdateAttendanceRequest $request, PlaySession $playSession, User $member, RecordAttendanceAction $recordAttendance): RedirectResponse
    {
        abort_if($member->isAdmin(), 404);
        $recordAttendance->handle($playSession, $member, $request->validated('status'), $request->validated('notes'), $request->user());

        return back()->with('success', "Kehadiran {$member->name} berhasil diperbarui.");
    }
}
