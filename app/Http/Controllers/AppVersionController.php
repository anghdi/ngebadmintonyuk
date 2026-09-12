<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()
            ->json(['version' => (string) config('app.version')])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
