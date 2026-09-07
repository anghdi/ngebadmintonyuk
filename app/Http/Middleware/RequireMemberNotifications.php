<?php

namespace App\Http\Middleware;

use App\Models\PushSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireMemberNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isAdmin()
            || $request->routeIs('notifications.setup', 'push-subscriptions.*', 'logout')) {
            return $next($request);
        }

        $installationId = $request->session()->get('push_installation_id');

        if (is_string($installationId) && PushSubscription::query()
            ->whereBelongsTo($user)
            ->where('driver', 'webpush')
            ->where('installation_id', $installationId)
            ->exists()) {
            return $next($request);
        }

        $request->session()->forget('push_installation_id');

        if ($request->isMethod('GET') && ! $request->expectsJson()) {
            $request->session()->put('push_intended_url', $request->getRequestUri());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Aktifkan notifikasi pada perangkat ini terlebih dahulu.',
                'redirect_url' => route('notifications.setup'),
            ], 403);
        }

        return redirect()->route('notifications.setup');
    }
}
