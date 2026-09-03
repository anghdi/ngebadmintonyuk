export function hasWonBadmintonGame(scores, teamIndex) {
    const score = scores[teamIndex];
    const opponentScore = scores[teamIndex === 0 ? 1 : 0];

    return score === 30 || (score >= 21 && score - opponentScore >= 2);
}

export function addBadmintonPoint(state, teamIndex) {
    if (state.gameOver || state.matchWinner !== null) {
        return state;
    }

    const nextState = {
        ...state,
        scores: [...state.scores],
        games: [...state.games],
        completedGames: state.completedGames.map((score) => [...score]),
        servingTeam: teamIndex,
    };

    nextState.scores[teamIndex] += 1;

    if (hasWonBadmintonGame(nextState.scores, teamIndex)) {
        nextState.games[teamIndex] += 1;
        nextState.completedGames.push([...nextState.scores]);
        nextState.gameOver = true;

        if (nextState.games[teamIndex] === 2) {
            nextState.matchWinner = teamIndex;
        }
    }

    return nextState;
}
