<?php

namespace App\Http\Controllers;

use App\Actions\CreateSessionRegistrationByAdminAction;
use App\Actions\DeleteSessionRegistrationAction;
use App\Actions\RecordSessionRegistrationPaymentAction;
use App\Actions\RegisterForPlaySessionAction;
use App\Actions\UpdateSessionRegistrationAction;
use App\Http\Requests\CancelSessionRegistrationRequest;
use App\Http\Requests\StoreSessionRegistrationByAdminRequest;
use App\Http\Requests\StoreSessionRegistrationRequest;
use App\Http\Requests\UpdateSessionRegistrationPaymentRequest;
use App\Http\Requests\UpdateSessionRegistrationRequest;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Http\RedirectResponse;

class SessionRegistrationController extends Controller
{
    public function store(StoreSessionRegistrationRequest $request, PlaySession $playSession, RegisterForPlaySessionAction $register): RedirectResponse
    {
        $registration = $register->handle($playSession, $request->validated(), $request->user());
        $position = $playSession->registrations()->where('id', '<=', $registration->id)->count();
        $message = $position > $playSession->max_players
            ? 'Nama Anda berhasil masuk waiting list.'
            : 'Nama Anda berhasil masuk daftar bermain.';

        return redirect()->route('public-sessions.show', $playSession)->with('success', $message);
    }

    public function storeByAdmin(StoreSessionRegistrationByAdminRequest $request, PlaySession $playSession, CreateSessionRegistrationByAdminAction $create): RedirectResponse
    {
        $registration = $create->handle($playSession, $request->validated());

        $position = $playSession->registrations()->where('id', '<=', $registration->id)->count();
        $listName = $position > $playSession->max_players ? 'waiting list' : 'daftar pemain';

        return back()->with('success', "{$registration->name} berhasil ditambahkan ke {$listName}.");
    }

    public function cancel(CancelSessionRegistrationRequest $request, PlaySession $playSession, SessionRegistration $registration, DeleteSessionRegistrationAction $delete): RedirectResponse
    {
        $delete->handle($registration);

        return redirect()->route('public-sessions.show', $playSession)->with('success', 'Keikutsertaan berhasil dibatalkan.');
    }

    public function update(UpdateSessionRegistrationRequest $request, PlaySession $playSession, SessionRegistration $registration, UpdateSessionRegistrationAction $update): RedirectResponse
    {
        $updatedRegistration = $update->handle($registration, $request->validated(), $request->user());

        return back()->with('success', "Data {$updatedRegistration->name} berhasil diperbarui.");
    }

    public function updatePayment(UpdateSessionRegistrationPaymentRequest $request, PlaySession $playSession, SessionRegistration $registration, RecordSessionRegistrationPaymentAction $recordPayment): RedirectResponse
    {
        $recordPayment->handle(
            $registration,
            $request->validated('payment_method'),
            $request->boolean('is_paid'),
            $request->user(),
        );

        return back()->with('success', 'Pembayaran berhasil diperbarui.');
    }

    public function destroy(PlaySession $playSession, SessionRegistration $registration, DeleteSessionRegistrationAction $delete): RedirectResponse
    {
        $delete->handle($registration);

        return back()->with('success', "{$registration->name} dihapus dari daftar.");
    }
}
