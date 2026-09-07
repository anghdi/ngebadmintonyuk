<?php

namespace App\Http\Controllers;

use App\Actions\RecordAttendanceAction;
use App\Actions\UpdateSessionRegistrationAction;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AttendanceController extends Controller
{
    public function update(UpdateAttendanceRequest $request, PlaySession $playSession, User $member, RecordAttendanceAction $recordAttendance, UpdateSessionRegistrationAction $updateRegistration): RedirectResponse
    {
        abort_if($member->isAdmin(), 404);
        $registration = $playSession->registrations()->where('user_id', $member->id)->first();

        if ($registration) {
            $updateRegistration->handle($registration, [
                'user_id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'attendance_status' => $request->validated('status') === 'present' ? 'present' : 'no_show',
                'admin_notes' => $request->validated('notes'),
            ], $request->user());
        } else {
            $recordAttendance->handle($playSession, $member, $request->validated('status'), $request->validated('notes'), $request->user());
        }

        return back()->with('success', "Kehadiran {$member->name} berhasil diperbarui.");
    }
}
