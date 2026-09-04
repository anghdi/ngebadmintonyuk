<?php

namespace App\Http\Middleware;

use App\Actions\ResetLegacyPushSubscriptionsAction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireCurrentPushSetup
{
    public function __construct(
        public ResetLegacyPushSubscriptionsAction $resetLegacyPushSubscriptions,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->isAdmin() || ! $this->resetLegacyPushSubscriptions->handle($user)) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('legacy_push_reset', true);
    }
}
