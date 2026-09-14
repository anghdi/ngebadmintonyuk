<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Income;
use App\Models\IncomeDetail;
use App\Models\Membership;
use App\Models\SessionRegistration;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MemberReportService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{rows: Collection<int, array<string, mixed>>, summary: array<string, int>, birthdays: Collection<int, array<string, mixed>>}
     */
    public function make(array $filters): array
    {
        $start = Carbon::parse($filters['start_date'])->startOfDay();
        $end = Carbon::parse($filters['end_date'])->endOfDay();
        $isGuest = $filters['type'] === 'guests';
        $payments = $this->payments($start->toDateString(), $end->toDateString(), $isGuest);
        $rows = collect();
        $query = $isGuest ? Guest::query() : User::query()->where('role', 'member');
        $query->select($isGuest ? ['id', 'name', 'phone'] : ['id', 'name', 'phone', 'date_of_birth']);
        if (! empty($filters['q'])) {
            $query->where('name', 'like', '%'.$filters['q'].'%');
        }
        if (! $isGuest) {
            if (! empty($filters['birthday_month'])) {
                $query->whereMonth('date_of_birth', (int) $filters['birthday_month']);
            }
            $query->with(['memberships' => fn ($query) => $query
                ->where('status', 'active')->whereDate('starts_on', '<=', today())
                ->where(fn ($query) => $query->whereNull('expires_on')->orWhereDate('expires_on', '>=', today()))
                ->withSum('transactions as balance', 'quantity')])
                ->withCount([
                    'attendances as present_count' => fn ($query) => $query->where('status', 'present')
                        ->whereHas('playSession', fn ($query) => $query->where('status', '!=', 'cancelled')->whereBetween('scheduled_at', [$start, $end])),
                    'attendances as absent_count' => fn ($query) => $query->where('status', 'absent')
                        ->whereHas('playSession', fn ($query) => $query->where('status', '!=', 'cancelled')->whereBetween('scheduled_at', [$start, $end])),
                ])->withSum(['topUpRequests as approved_top_up' => fn ($query) => $query
                ->where('status', 'approved')->whereBetween('reviewed_at', [$start, $end])], 'amount');
        } else {
            $query->withCount([
                'registrations as present_count' => fn ($query) => $query->where('attendance_status', 'present')
                    ->whereHas('playSession', fn ($query) => $query->where('status', '!=', 'cancelled')->whereBetween('scheduled_at', [$start, $end])),
                'registrations as absent_count' => fn ($query) => $query->where('attendance_status', 'no_show')
                    ->whereHas('playSession', fn ($query) => $query->where('status', '!=', 'cancelled')->whereBetween('scheduled_at', [$start, $end])),
            ]);
        }
        $query->chunkById(200, function ($people) use ($rows, $payments, $filters, $isGuest): void {
            foreach ($people as $person) {
                $complete = $person instanceof User ? $person->hasCompleteProfile() : null;
                if (! $isGuest && ! empty($filters['profile'])
                    && (($filters['profile'] === 'complete') !== $complete)) {
                    continue;
                }
                $birth = $person instanceof User && $complete ? $person->date_of_birth : null;
                $rows->push([
                    'id' => $person->id, 'name' => $person->name, 'phone' => $person->phone,
                    'complete' => $complete, 'birth_date' => $birth?->toDateString(),
                    'age' => $birth ? (int) $birth->diffInYears(today()) : null,
                    'present' => (int) $person->getAttribute('present_count'),
                    'absent' => (int) $person->getAttribute('absent_count'),
                    'quota' => $person instanceof User ? (int) $person->memberships->sum(fn (Membership $membership): int => max(0, (int) $membership->balance)) : 0,
                    'payment' => (int) $payments->get($person->id, 0),
                    'top_up' => $isGuest ? 0 : (int) $person->getAttribute('approved_top_up'),
                ]);
            }
        });
        $rows = $rows->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return [
            'rows' => $rows,
            'summary' => [
                'total' => $rows->count(), 'complete' => $rows->where('complete', true)->count(),
                'incomplete' => $isGuest ? 0 : $rows->where('complete', false)->count(),
                'present' => (int) $rows->sum('present'), 'absent' => (int) $rows->sum('absent'),
                'payment' => (int) $rows->sum('payment'), 'top_up' => (int) $rows->sum('top_up'),
            ],
            'birthdays' => $this->birthdays(),
        ];
    }

    /** @return Collection<int, mixed> */
    private function payments(string $start, string $end, bool $isGuest): Collection
    {
        $details = (new IncomeDetail)->getTable();
        $incomes = (new Income)->getTable();
        $registrations = (new SessionRegistration)->getTable();
        $identity = $isGuest ? 'guest_id' : 'user_id';

        return IncomeDetail::query()
            ->join($incomes, $incomes.'.id', '=', $details.'.income_id')
            ->join($registrations, $registrations.'.income_id', '=', $incomes.'.id')
            ->whereBetween($incomes.'.date', [$start, $end])
            ->where($registrations.'.payment_status', 'paid')
            ->whereIn($registrations.'.payment_method', ['cash', 'transfer'])
            ->whereNotNull($registrations.'.'.$identity)
            ->select($registrations.'.'.$identity)
            ->selectRaw('SUM(amount) as total')
            ->groupBy($registrations.'.'.$identity)->pluck('total', $identity);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function birthdays(): Collection
    {
        $today = today();
        $limit = $today->copy()->addDays(7);
        $rows = collect();
        User::query()->where('role', 'member')->whereNotNull('date_of_birth')
            ->where(fn ($query) => $query->whereMonth('date_of_birth', $today->month)->orWhereMonth('date_of_birth', $limit->month))
            ->select(['id', 'name', 'date_of_birth'])->chunkById(200, function ($members) use ($rows, $today, $limit): void {
                foreach ($members as $member) {
                    if (! $member->hasCompleteProfile()) {
                        continue;
                    }
                    $next = $this->nextBirthday($member->date_of_birth, $today);
                    if ($next->betweenIncluded($today, $limit)) {
                        $rows->push([
                            'id' => $member->id, 'name' => $member->name,
                            'date' => $next->toDateString(), 'today' => $next->isSameDay($today),
                            'age' => (int) $member->date_of_birth->diffInYears($next),
                        ]);
                    }
                }
            });

        return $rows->sortBy('date')->values();
    }

    public function nextBirthday(CarbonInterface $birthDate, CarbonInterface $from): Carbon
    {
        $year = $from->year;
        while (true) {
            if (checkdate($birthDate->month, $birthDate->day, $year)) {
                $candidate = Carbon::create($year, $birthDate->month, $birthDate->day)->startOfDay();
                if ($candidate->greaterThanOrEqualTo($from->copy()->startOfDay())) {
                    return $candidate;
                }
            }
            $year++;
        }
    }
}
