<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __invoke(Request $request, ReportService $service)
    {
        $input = $request->validate(['start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date']]);
        $start = $input['start_date'] ?? today()->startOfMonth()->toDateString();
        $end = $input['end_date'] ?? today()->toDateString();

        return view('reports.index', $service->make($start, $end) + compact('start', 'end'));
    }

    public function download(Request $request, ReportService $service)
    {
        $input = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $start = $input['start_date'];
        $end = $input['end_date'];

        return Pdf::loadView('reports.pdf', $service->make($start, $end) + compact('start', 'end'))
            ->setPaper('a4', 'portrait')
            ->download("laporan-ngekas-{$start}-{$end}.pdf");
    }
}
