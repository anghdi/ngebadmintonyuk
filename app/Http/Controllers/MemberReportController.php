<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterMemberReportRequest;
use App\Services\MemberReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MemberReportController extends Controller
{
    public function index(FilterMemberReportRequest $request, MemberReportService $service): View
    {
        $filters = $request->validated();
        $data = $service->make($filters);
        $page = (int) ($filters['page'] ?? 1);
        $rows = new LengthAwarePaginator($data['rows']->forPage($page, 20)->values(), $data['rows']->count(), 20, $page, [
            'path' => route('member-reports.index'), 'query' => $request->query(),
        ]);

        return view('member-reports.index', array_replace($data, ['rows' => $rows, 'filters' => $filters]));
    }

    public function download(FilterMemberReportRequest $request, MemberReportService $service): Response
    {
        $filters = $request->validated();

        return Pdf::loadView('member-reports.pdf', $service->make($filters) + ['filters' => $filters])
            ->setPaper('a4', 'landscape')
            ->download("laporan-{$filters['type']}-{$filters['start_date']}-{$filters['end_date']}.pdf");
    }
}
