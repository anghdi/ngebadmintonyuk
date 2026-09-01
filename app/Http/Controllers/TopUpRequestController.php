<?php

namespace App\Http\Controllers;

use App\Actions\CreateTopUpRequestAction;
use App\Actions\ReviewTopUpRequestAction;
use App\Http\Requests\ReviewTopUpRequest;
use App\Http\Requests\StoreTopUpRequest;
use App\Models\TopUpRequest;
use App\Models\TopUpSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TopUpRequestController extends Controller
{
    public function index(Request $request): View
    {
        if ($request->user()->isAdmin()) {
            $topUpRequests = TopUpRequest::query()->with(['member', 'membership', 'reviewer'])->latest()->paginate(20);
            $topUpSetting = TopUpSetting::current();

            return view('top-ups.admin', compact('topUpRequests', 'topUpSetting'));
        }

        $memberships = $request->user()->memberships()->where('status', 'active')->withSum('transactions as balance', 'quantity')->latest()->get();
        $topUpRequests = $request->user()->topUpRequests()->with(['membership', 'reviewer'])->latest()->paginate(10);
        $topUpSetting = TopUpSetting::current();

        return view('top-ups.member', compact('memberships', 'topUpRequests', 'topUpSetting'));
    }

    public function store(StoreTopUpRequest $request, CreateTopUpRequestAction $createTopUpRequest): RedirectResponse
    {
        $data = $request->validated();
        $membership = isset($data['membership_id'])
            ? $request->user()->memberships()->findOrFail($data['membership_id'])
            : null;
        $createTopUpRequest->handle($request->user(), $membership, $data['amount'], $data['bank'], $request->file('proof'));

        return back()->with('success', 'Pengajuan top up berhasil dikirim.');
    }

    public function update(ReviewTopUpRequest $request, TopUpRequest $topUpRequest, ReviewTopUpRequestAction $reviewTopUpRequest): RedirectResponse
    {
        $data = $request->validated();
        $reviewTopUpRequest->handle($topUpRequest, $data, $request->user());

        $message = $data['status'] === 'approved'
            ? 'Top up disetujui. Empat kuota telah ditambahkan.'
            : 'Pengajuan top up ditolak.';

        return back()->with('success', $message);
    }

    public function proof(Request $request, TopUpRequest $topUpRequest): StreamedResponse
    {
        abort_unless($request->user()->isAdmin() || $topUpRequest->user_id === $request->user()->id, 403);

        return Storage::disk('local')->response($topUpRequest->proof_path);
    }
}
