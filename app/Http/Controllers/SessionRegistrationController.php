<?php

namespace App\Http\Controllers;

use App\Actions\RegisterForPlaySessionAction;
use App\Actions\UpdateSessionRegistrationAction;
use App\Http\Requests\StoreSessionRegistrationRequest;
use App\Http\Requests\UpdateSessionRegistrationRequest;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Http\RedirectResponse;

class SessionRegistrationController extends Controller
{
    public function store(StoreSessionRegistrationRequest $request, PlaySession $playSession, RegisterForPlaySessionAction $register): RedirectResponse
    {
        $register->handle($playSession, $request->validated(), $request->user());

        return redirect()->route('public-sessions.show', $playSession)->with('success', 'Nama Anda berhasil masuk daftar bermain.');
    }

    public function update(UpdateSessionRegistrationRequest $request, PlaySession $playSession, SessionRegistration $registration, UpdateSessionRegistrationAction $update): RedirectResponse
    {
        $update->handle($registration, $request->validated(), $request->user());

        return back()->with('success', "Status {$registration->name} berhasil diperbarui.");
    }
}
