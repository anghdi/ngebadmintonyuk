<?php

namespace App\Http\Controllers;

use App\Actions\SendPushNotificationAction;
use App\Http\Requests\SendPushNotificationRequest;
use App\Models\PlaySession;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PushNotificationController extends Controller
{
    public function index(): View
    {
        $playSessions = PlaySession::query()
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->withCount('registrations')
            ->oldest('scheduled_at')
            ->get();
        $notifications = PushNotification::query()
            ->with(['playSession:id,venue_name,scheduled_at', 'sender:id,name'])
            ->latest()
            ->paginate(15);
        $subscriptionCount = PushSubscription::query()
            ->whereHas('user', fn ($query) => $query->where('role', 'member'))
            ->count();

        return view('push-notifications.index', compact('playSessions', 'notifications', 'subscriptionCount'));
    }

    public function store(SendPushNotificationRequest $request, SendPushNotificationAction $sendPushNotification): RedirectResponse
    {
        $notification = $sendPushNotification->handle($request->validated(), $request->user());

        if ($notification->device_count === 0) {
            return back()->withErrors(['notification' => 'Tidak ada perangkat aktif yang dapat menerima notifikasi.']);
        }

        if ($notification->success_count === 0) {
            return back()->withErrors(['notification' => "Notifikasi gagal dikirim ke {$notification->failure_count} perangkat."]);
        }

        $message = "Notifikasi berhasil dikirim ke {$notification->success_count} perangkat.";

        if ($notification->failure_count > 0) {
            $message .= " Gagal dikirim ke {$notification->failure_count} perangkat.";
        }

        return back()->with('success', $message);
    }
}
