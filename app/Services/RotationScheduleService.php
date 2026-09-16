<?php

namespace App\Services;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Support\Arr;
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
        ];
    }

    /** @param list<array{id: int, name: string, user_id: int|null, guest_id: int|null}> $roster
     * @return array<string, mixed>
     */
    public function generate(array $roster, int $courtCount, int $roundCount): array
    {
        $listedIds = array_column($roster, 'id');
        $ids = Arr::shuffle($listedIds);
        $playersPerTurn = $courtCount * 4;
        $keptFirstGroup = count($ids) > $playersPerTurn
            && array_diff(array_slice($ids, 0, $playersPerTurn), array_slice($listedIds, 0, $playersPerTurn)) === [];
        if ($ids === $listedIds || $keptFirstGroup) {
            $ids = [...array_slice($ids, 1), $ids[0]];
        }
        $mixOrder = array_flip($ids);
        $games = array_fill_keys($listedIds, 0);
        $lastPlayed = array_fill_keys($ids, 0);
        $courtVisits = array_fill_keys($ids, [1 => 0, 2 => 0]);
        $lastCourt = array_fill_keys($ids, 0);
        $partners = [];
        $opponents = [];
        $encounters = [];
        $rounds = [];

        for ($round = 1; $round <= $roundCount; $round++) {
            $selected = $this->selectPlayers(
                $ids,
                $playersPerTurn,
                $games,
                $lastPlayed,
                $mixOrder,
                $encounters,
            );
            $bestCourts = [];
            $bestScore = null;

            foreach ($this->pairings($selected) as $teams) {
                [$repeatedPartnerPairs, $partnerFrequency] = $this->partnerRepeatScore($teams, $partners);
                foreach ($this->pairings(array_keys($teams)) as $matches) {
                    $repeatedOpponentPairs = 0;
                    $opponentFrequency = 0;
                    $maximumEncounterFrequency = 0;
                    $repeatedEncounterPairs = 0;
                    $encounterFrequency = 0;
                    $courts = [];
                    foreach ($matches as [$a, $b]) {
                        [$courtRepeatedPairs, $courtOpponentFrequency] = $this->opponentRepeatScore($teams[$a], $teams[$b], $opponents);
                        $repeatedOpponentPairs += $courtRepeatedPairs;
                        $opponentFrequency += $courtOpponentFrequency;
                        [$courtMaximumFrequency, $courtRepeatedEncounters, $courtEncounterFrequency] = $this->encounterRepeatScore(
                            [...$teams[$a], ...$teams[$b]],
                            $encounters,
                        );
                        $maximumEncounterFrequency = max($maximumEncounterFrequency, $courtMaximumFrequency);
                        $repeatedEncounterPairs += $courtRepeatedEncounters;
                        $encounterFrequency += $courtEncounterFrequency;
                        $courts[] = ['number' => count($courts) + 1, 'team_a' => $teams[$a], 'team_b' => $teams[$b]];
                    }
                    $courtOrders = $courtCount === 2 ? [$courts, array_reverse($courts)] : [$courts];
                    foreach ($courtOrders as $orderedCourts) {
                        $balancePenalty = 0;
                        $repeatPenalty = 0;
                        foreach ($orderedCourts as $index => &$court) {
                            $court['number'] = $index + 1;
                            $court['label'] = $index === 0 ? 'A' : 'B';
                            foreach ([...$court['team_a'], ...$court['team_b']] as $id) {
                                if ($courtCount === 2) {
                                    $visits = $courtVisits[$id];
                                    $visits[$court['number']]++;
                                    $visitDifference = abs($visits[1] - $visits[2]);
                                    $balancePenalty += $visitDifference;
                                    $repeatPenalty += (int) ($lastCourt[$id] === $court['number']);
                                }
                            }
                        }
                        unset($court);
                        $candidateScore = [
                            $maximumEncounterFrequency,
                            $repeatedEncounterPairs,
                            $encounterFrequency,
                            $repeatedPartnerPairs,
                            $partnerFrequency,
                            $repeatedOpponentPairs,
                            $opponentFrequency,
                            $balancePenalty,
                            $repeatPenalty,
                        ];
                        if ($bestScore === null || $candidateScore < $bestScore) {
                            $bestScore = $candidateScore;
                            $bestCourts = $orderedCourts;
                        }
                    }
                }
            }
            foreach ($bestCourts as $court) {
                $courtPlayers = [...$court['team_a'], ...$court['team_b']];
                foreach ($courtPlayers as $id) {
                    $courtVisits[$id][$court['number']]++;
                    $lastCourt[$id] = $court['number'];
                }
                foreach ($this->pairsWithin($courtPlayers) as [$a, $b]) {
                    $key = $this->pairKey($a, $b);
                    $encounters[$key] = ($encounters[$key] ?? 0) + 1;
                }
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
            'mix_order' => $ids,
            'rounds' => $rounds,
            'games' => $games,
        ];
    }

    /**
     * @param  list<int>  $ids
     * @param  array<int, int>  $games
     * @param  array<int, int>  $lastPlayed
     * @param  array<int, int>  $mixOrder
     * @param  array<string, int>  $encounters
     * @return list<int>
     */
    private function selectPlayers(
        array $ids,
        int $playersPerTurn,
        array $games,
        array $lastPlayed,
        array $mixOrder,
        array $encounters,
    ): array {
        $selected = [];
        $remaining = $ids;

        while (count($selected) < $playersPerTurn) {
            usort($remaining, function (int $a, int $b) use ($games, $lastPlayed, $mixOrder, $encounters, $selected): int {
                [$maximumA, $totalA] = $this->encounterLoad($a, $selected, $encounters);
                [$maximumB, $totalB] = $this->encounterLoad($b, $selected, $encounters);

                return [
                    $games[$a],
                    $maximumA,
                    $totalA,
                    $lastPlayed[$a],
                    $mixOrder[$a],
                ] <=> [
                    $games[$b],
                    $maximumB,
                    $totalB,
                    $lastPlayed[$b],
                    $mixOrder[$b],
                ];
            });

            $selected[] = array_shift($remaining);
        }

        return $selected;
    }

    /**
     * @param  list<int>  $selected
     * @param  array<string, int>  $encounters
     * @return array{int, int}
     */
    private function encounterLoad(int $player, array $selected, array $encounters): array
    {
        $maximum = 0;
        $total = 0;

        foreach ($selected as $otherPlayer) {
            $frequency = $encounters[$this->pairKey($player, $otherPlayer)] ?? 0;
            $maximum = max($maximum, $frequency);
            $total += $frequency;
        }

        return [$maximum, $total];
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

    /**
     * @param  list<int>  $players
     * @return list<array{int, int}>
     */
    private function pairsWithin(array $players): array
    {
        $pairs = [];

        foreach ($players as $index => $player) {
            foreach (array_slice($players, $index + 1) as $otherPlayer) {
                $pairs[] = [$player, $otherPlayer];
            }
        }

        return $pairs;
    }

    /**
     * @param  list<int>  $players
     * @param  array<string, int>  $encounters
     * @return array{int, int, int}
     */
    private function encounterRepeatScore(array $players, array $encounters): array
    {
        $maximumFrequency = 0;
        $repeatedPairs = 0;
        $frequency = 0;

        foreach ($this->pairsWithin($players) as [$a, $b]) {
            $pairFrequency = $encounters[$this->pairKey($a, $b)] ?? 0;
            $maximumFrequency = max($maximumFrequency, $pairFrequency);
            $repeatedPairs += (int) ($pairFrequency > 0);
            $frequency += $pairFrequency;
        }

        return [$maximumFrequency, $repeatedPairs, $frequency];
    }

    /**
     * @param  list<array{int, int}>  $teams
     * @param  array<string, int>  $partners
     * @return array{int, int}
     */
    private function partnerRepeatScore(array $teams, array $partners): array
    {
        $repeatedPairs = 0;
        $frequency = 0;

        foreach ($teams as [$a, $b]) {
            $encounters = $partners[$this->pairKey($a, $b)] ?? 0;
            $repeatedPairs += (int) ($encounters > 0);
            $frequency += $encounters;
        }

        return [$repeatedPairs, $frequency];
    }

    /**
     * @param  array{int, int}  $teamA
     * @param  array{int, int}  $teamB
     * @param  array<string, int>  $opponents
     * @return array{int, int}
     */
    private function opponentRepeatScore(array $teamA, array $teamB, array $opponents): array
    {
        $repeatedPairs = 0;
        $frequency = 0;

        foreach ($teamA as $player) {
            foreach ($teamB as $opponent) {
                $encounters = $opponents[$this->pairKey($player, $opponent)] ?? 0;
                $repeatedPairs += (int) ($encounters > 0);
                $frequency += $encounters;
            }
        }

        return [$repeatedPairs, $frequency];
    }
}
