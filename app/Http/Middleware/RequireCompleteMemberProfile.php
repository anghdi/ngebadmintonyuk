<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireCompleteMemberProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() !== null && ! $request->user()->hasCompleteProfile()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Lengkapi profil sebelum ikut sesi.',
                    'redirect_url' => route('profile.edit'),
                ], 403);
            }

            return redirect()->route('profile.edit')->withErrors(['profile' => 'Lengkapi profil sebelum ikut sesi.']);
        }

        return $next($request);
    }
}
