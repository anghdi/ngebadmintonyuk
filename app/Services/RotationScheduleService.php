<?php

namespace App\Services;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Support\Collection;

class RotationScheduleService
{
    /** @param Collection<int, SessionRegistration> $registrations
     * @return list<array{id: int, name: string, user_id: int|null, guest_id: int|null}>
     */
    public function roster(Collection $registrations): array
    {
        return array_values($registrations->map(fn (SessionRegistration $registration): array => [
            'id' => $registration->id,
            'name' => $registration->name,
            'user_id' => $registration->user_id,
            'guest_id' => $registration->guest_id,
        ])->all());
    }

    /** @param list<array{id: int, name: string, user_id: int|null, guest_id: int|null}> $roster */
    public function fingerprint(array $roster, int $courtCount): string
    {
        return hash('sha256', json_encode([$courtCount, $roster], JSON_THROW_ON_ERROR));
    }

    /** @param Collection<int, SessionRegistration> $registrations
     * @return array<string, mixed>
     */
    public function viewData(PlaySession $session, Collection $registrations, bool $review = false): array
    {
        $fingerprint = $this->fingerprint($this->roster($registrations), $session->court_count);
        $schedule = $session->rotation_schedule;
        $published = ($schedule['published_at'] ?? null) !== null;
        $stale = $schedule !== null && ($schedule['fingerprint'] ?? null) !== $fingerprint;

        return [
            'rotationSchedule' => $review || ($published && ! $stale) ? $schedule : null,
            'rotationPublished' => $published && ! $stale,
            'rotationStale' => $stale && ($review || $published),
            'rotationFingerprint' => $fingerprint,
            'rotationMinimumRounds' => max(1, (int) ceil($registrations->count() / ($session->court_count * 4))),
        ];
    }

    /** @param list<array{id: int, name: string, user_id: int|null, guest_id: int|null}> $roster
     * @return array<string, mixed>
     */
    public function generate(array $roster, int $courtCount, int $roundCount): array
    {
        $ids = array_column($roster, 'id');
        $games = array_fill_keys($ids, 0);
        $lastPlayed = array_fill_keys($ids, 0);
        $partners = [];
        $opponents = [];
        $rounds = [];

        for ($round = 1; $round <= $roundCount; $round++) {
            $ranked = $ids;
            usort($ranked, fn (int $a, int $b): int => [$games[$a], $lastPlayed[$a], $a] <=> [$games[$b], $lastPlayed[$b], $b]);
            $selected = array_slice($ranked, 0, $courtCount * 4);
            $bestCourts = [];
            $bestScore = PHP_INT_MAX;

            foreach ($this->pairings($selected) as $teams) {
                $partnerScore = 0;
                foreach ($teams as [$a, $b]) {
                    $partnerScore += ($partners[$this->pairKey($a, $b)] ?? 0) * 100;
                }
                foreach ($this->pairings(array_keys($teams)) as $matches) {
                    $score = $partnerScore;
                    $courts = [];
                    foreach ($matches as [$a, $b]) {
                        foreach ($teams[$a] as $player) {
                            foreach ($teams[$b] as $opponent) {
                                $score += $opponents[$this->pairKey($player, $opponent)] ?? 0;
                            }
                        }
                        $courts[] = ['number' => count($courts) + 1, 'team_a' => $teams[$a], 'team_b' => $teams[$b]];
                    }
                    if ($score < $bestScore) {
                        $bestScore = $score;
                        $bestCourts = $courts;
                    }
                }
            }
            foreach ($bestCourts as $court) {
                foreach (['team_a', 'team_b'] as $team) {
                    [$a, $b] = $court[$team];
                    $key = $this->pairKey($a, $b);
                    $partners[$key] = ($partners[$key] ?? 0) + 1;
                }
                foreach ($court['team_a'] as $player) {
                    foreach ($court['team_b'] as $opponent) {
                        $key = $this->pairKey($player, $opponent);
                        $opponents[$key] = ($opponents[$key] ?? 0) + 1;
                    }
                }
            }
            foreach ($selected as $id) {
                $games[$id]++;
                $lastPlayed[$id] = $round;
            }
            $rounds[] = ['number' => $round, 'courts' => $bestCourts, 'rest' => array_values(array_diff($ids, $selected))];
        }

        return [
            'fingerprint' => $this->fingerprint($roster, $courtCount),
            'generated_at' => now()->toIso8601String(),
            'court_count' => $courtCount,
            'roster' => $roster,
            'rounds' => $rounds,
            'games' => $games,
        ];
    }

    /** @param list<int> $ids
     * @return list<list<array{int, int}>>
     */
    private function pairings(array $ids): array
    {
        if ($ids === []) {
            return [[]];
        }
        $first = array_shift($ids);
        $result = [];
        foreach ($ids as $index => $partner) {
            $remaining = $ids;
            unset($remaining[$index]);
            foreach ($this->pairings(array_values($remaining)) as $pairs) {
                $result[] = [[$first, $partner], ...$pairs];
            }
        }

        return $result;
    }

    private function pairKey(int $a, int $b): string
    {
        return min($a, $b).':'.max($a, $b);
    }
}
