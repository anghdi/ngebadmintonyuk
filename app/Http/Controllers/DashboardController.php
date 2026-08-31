<?php

namespace App\Http\Controllers;

use App\Models\MembershipTransaction;
use App\Models\PlaySession;
use App\Models\ShuttlecockItem;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ReportService $reports): View
    {
        if (! $request->user()->isAdmin()) {
            return $this->memberDashboard($request);
        }

        $now = today();
        $data = $reports->make($now->copy()->startOfMonth()->toDateString(), $now->toDateString());
        $latest = collect($data['incomes'])->map(fn ($x) => ['type' => 'income', 'item' => $x])
            ->concat(collect($data['expenses'])->map(fn ($x) => ['type' => 'expense', 'item' => $x]))
            ->sortByDesc(fn ($x) => $x['item']->date->format('Y-m-d').'-'.str_pad($x['item']->id, 10, '0', STR_PAD_LEFT))->take(8);

        $memberCount = User::where('role', 'member')->count();
        $upcomingSessionCount = PlaySession::where('scheduled_at', '>=', now())->where('status', 'scheduled')->count();
        $lowStockCount = ShuttlecockItem::query()->withSum('movements as stock', 'quantity')->get()
            ->filter(fn (ShuttlecockItem $item): bool => (int) $item->stock <= $item->minimum_stock)->count();

        return view('dashboard', $data + compact('latest', 'memberCount', 'upcomingSessionCount', 'lowStockCount'));
    }

    private function memberDashboard(Request $request): View
    {
        $member = $request->user();
        $memberships = $member->memberships()->withSum('transactions as balance', 'quantity')->latest()->get();
        $memberTransactions = MembershipTransaction::query()
            ->whereHas('membership', fn ($query) => $query->whereBelongsTo($member));
        $remainingCredits = (int) $memberships->sum('balance');
        $usedCredits = abs((int) (clone $memberTransactions)->where('quantity', '<', 0)->sum('quantity'));
        $transactions = $memberTransactions
            ->with(['membership', 'attendance.playSession'])
            ->latest()
            ->limit(15)
            ->get();
        $upcomingSessions = PlaySession::query()
            ->where('scheduled_at', '>=', now())
            ->where('status', 'scheduled')
            ->oldest('scheduled_at')
            ->limit(6)
            ->get();

        return view('members.dashboard', compact('member', 'memberships', 'transactions', 'upcomingSessions', 'remainingCredits', 'usedCredits'));
    }
}
