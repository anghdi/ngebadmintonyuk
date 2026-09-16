<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppInstallationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        abort_if($request->user()->isAdmin(), 403);

        $member = $request->user();

        if ($member->pwa_installed_at === null) {
            $member->pwa_installed_at = now();
            $member->save();
        }

        return response()->json(['recorded' => true]);
    }
}
