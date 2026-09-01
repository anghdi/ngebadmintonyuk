<?php

namespace App\Http\Controllers;

use App\Actions\CreateSessionRegistrationByAdminAction;
use App\Actions\DeleteSessionRegistrationAction;
use App\Actions\RegisterForPlaySessionAction;
use App\Actions\UpdateSessionRegistrationAction;
use App\Http\Requests\StoreSessionRegistrationByAdminRequest;
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

    public function storeByAdmin(StoreSessionRegistrationByAdminRequest $request, PlaySession $playSession, CreateSessionRegistrationByAdminAction $create): RedirectResponse
    {
        $registration = $create->handle($playSession, $request->validated());

        return back()->with('success', "{$registration->name} berhasil ditambahkan ke daftar.");
    }

    public function update(UpdateSessionRegistrationRequest $request, PlaySession $playSession, SessionRegistration $registration, UpdateSessionRegistrationAction $update): RedirectResponse
    {
        $updatedRegistration = $update->handle($registration, $request->validated(), $request->user());

        return back()->with('success', "Data {$updatedRegistration->name} berhasil diperbarui.");
    }

    public function destroy(PlaySession $playSession, SessionRegistration $registration, DeleteSessionRegistrationAction $delete): RedirectResponse
    {
        $delete->handle($registration);

        return back()->with('success', "{$registration->name} dihapus dari daftar.");
    }
}
