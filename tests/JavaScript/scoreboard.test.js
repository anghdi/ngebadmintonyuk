import assert from 'node:assert/strict';
import test from 'node:test';

import { addBadmintonPoint, hasWonBadmintonGame } from '../../resources/js/scoreboard.js';

const matchState = (overrides = {}) => ({
    scores: [0, 0],
    games: [0, 0],
    completedGames: [],
    servingTeam: 0,
    gameOver: false,
    matchWinner: null,
    ...overrides,
});

test('a game is won at 21 with a two point lead', () => {
    assert.equal(hasWonBadmintonGame([21, 19], 0), true);
    assert.equal(hasWonBadmintonGame([21, 20], 0), false);
});

test('deuce continues until a two point lead or the 30 point cap', () => {
    assert.equal(hasWonBadmintonGame([29, 28], 0), false);
    assert.equal(hasWonBadmintonGame([30, 29], 0), true);
});

test('winning two games completes the match', () => {
    const state = matchState({ scores: [20, 10], games: [1, 0] });
    const result = addBadmintonPoint(state, 0);

    assert.deepEqual(result.scores, [21, 10]);
    assert.deepEqual(result.games, [2, 0]);
    assert.deepEqual(result.completedGames, [[21, 10]]);
    assert.equal(result.gameOver, true);
    assert.equal(result.matchWinner, 0);
});
