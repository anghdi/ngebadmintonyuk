import { installMemberNotifications } from './member-notifications.js';
import { addBadmintonPoint } from './scoreboard.js';
import { isIosDevice, resolvePwaInstallMode } from './pwa-install.js';
import { installServerLoading } from './server-loading.js';

const serverLoading = installServerLoading(window, document);

const sidebar = document.querySelector('#sidebar');
const menu = document.querySelector('[data-sidebar-open]');

function toggleSidebar(open) {
    sidebar?.classList.toggle('open', open);
    document.body.classList.toggle('nav-open', open);
    menu?.setAttribute('aria-expanded', open ? 'true' : 'false');
}

menu?.addEventListener('click', () => toggleSidebar(true));
document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
    button.addEventListener('click', () => toggleSidebar(false));
});
sidebar?.querySelectorAll('nav a').forEach((link) => {
    link.addEventListener('click', () => toggleSidebar(false));
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        toggleSidebar(false);
    }
});

const installButton = document.querySelector('[data-pwa-install]');
const installGuide = document.querySelector('[data-pwa-guide]');

if (installButton) {
    const ios = isIosDevice(navigator.userAgent, navigator.platform, navigator.maxTouchPoints);
    const standalone = window.matchMedia('(display-mode: standalone)').matches
        || navigator.standalone === true;
    let deferredInstallPrompt;

    const updateInstallButton = () => {
        const mode = resolvePwaInstallMode({
            standalone,
            ios,
            promptAvailable: Boolean(deferredInstallPrompt),
        });

        installButton.hidden = mode === 'installed' || mode === 'hidden';
        installButton.dataset.installMode = mode;
    };

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        updateInstallButton();
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = undefined;
        installButton.hidden = true;
        installButton.dataset.installMode = 'installed';
    });

    installButton.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            await deferredInstallPrompt.prompt();
            deferredInstallPrompt = undefined;
            updateInstallButton();

            return;
        }

        if (ios && installGuide) {
            if (typeof installGuide.showModal === 'function') {
                installGuide.showModal();
            } else {
                installGuide.setAttribute('open', '');
            }
        }
    });

    document.querySelectorAll('[data-pwa-guide-close]').forEach((button) => {
        button.addEventListener('click', () => installGuide?.close());
    });
    installGuide?.addEventListener('click', (event) => {
        if (event.target === installGuide) {
            installGuide.close();
        }
    });

    updateInstallButton();
}

document.querySelectorAll('[data-member-select]').forEach((select) => {
    select.addEventListener('change', () => {
        const option = select.options[select.selectedIndex];
        const form = select.closest('form');

        if (!option?.value || !form) {
            return;
        }

        const nameInput = form.querySelector('input[name="name"]');
        const phoneInput = form.querySelector('input[name="phone"]');

        if (nameInput) {
            nameInput.value = option.dataset.memberName ?? '';
        }

        if (phoneInput && option.dataset.memberPhone) {
            phoneInput.value = option.dataset.memberPhone;
        }
    });
});

document.querySelectorAll('[data-copy-text]').forEach((button) => {
    button.addEventListener('click', async () => {
        const text = button.dataset.copyText;

        if (!text) {
            return;
        }

        try {
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(text);
            } else {
                const input = document.createElement('textarea');
                input.value = text;
                input.setAttribute('readonly', '');
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.append(input);
                input.select();

                const copied = document.execCommand('copy');
                input.remove();

                if (!copied) {
                    throw new Error('Browser tidak mendukung penyalinan otomatis.');
                }
            }

            button.textContent = 'Tersalin';
            button.setAttribute('aria-label', `${button.dataset.copyLabel ?? 'Nomor rekening'} tersalin`);
        } catch {
            button.textContent = 'Gagal menyalin';
        }

        window.setTimeout(() => {
            button.textContent = 'Salin';
            button.setAttribute('aria-label', button.dataset.copyLabel ?? 'Salin nomor rekening');
        }, 1800);
    });
});

document.querySelectorAll('[data-usage-guide]').forEach((guide) => {
    const dialog = guide.querySelector('[data-usage-guide-dialog]');

    guide.querySelector('[data-usage-guide-open]')?.addEventListener('click', () => {
        if (typeof dialog?.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog?.setAttribute('open', '');
        }
    });
    guide.querySelectorAll('[data-usage-guide-close]').forEach((button) => {
        button.addEventListener('click', () => dialog?.close());
    });
    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
});

installMemberNotifications(window, document, serverLoading);

const scoreboard = document.querySelector('[data-scoreboard]');

if (scoreboard) {
    const storageKey = 'ngebadmintonyuk-scoreboard';
    const defaultState = {
        teams: ['Tim A', 'Tim B'],
        scores: [0, 0],
        games: [0, 0],
        completedGames: [],
        servingTeam: 0,
        gameOver: false,
        matchWinner: null,
    };
    let history = [];
    let state = { ...defaultState };

    try {
        const storedState = JSON.parse(localStorage.getItem(storageKey));

        if (Array.isArray(storedState?.teams) && Array.isArray(storedState?.scores) && Array.isArray(storedState?.games)) {
            state = { ...defaultState, ...storedState };
        }
    } catch {
        localStorage.removeItem(storageKey);
    }

    const copyState = () => JSON.parse(JSON.stringify(state));
    const teamIndex = (team) => team === 'a' ? 0 : 1;
    const teamName = (index) => state.teams[index] || `Tim ${index === 0 ? 'A' : 'B'}`;
    const save = () => localStorage.setItem(storageKey, JSON.stringify(state));

    const render = () => {
        ['a', 'b'].forEach((team) => {
            const index = teamIndex(team);
            const teamCard = scoreboard.querySelector(`[data-score-team="${team}"]`);
            const nameInput = scoreboard.querySelector(`[data-team-name="${team}"]`);

            nameInput.value = state.teams[index];
            scoreboard.querySelector(`[data-score="${team}"]`).textContent = state.scores[index];
            scoreboard.querySelector(`[data-games="${team}"]`).textContent = state.games[index];
            teamCard.classList.toggle('is-serving', state.servingTeam === index && state.matchWinner === null);
            scoreboard.querySelector(`[data-add-point="${team}"]`).disabled = state.gameOver || state.matchWinner !== null;
        });

        const currentGame = state.completedGames.length + 1;
        const servingSide = state.scores[state.servingTeam] % 2 === 0 ? 'kanan' : 'kiri';
        const status = state.matchWinner !== null
            ? `${teamName(state.matchWinner)} memenangkan pertandingan.`
            : state.gameOver
                ? `${teamName(state.scores[0] > state.scores[1] ? 0 : 1)} memenangkan game ${currentGame}.`
                : `${teamName(state.servingTeam)} melakukan servis dari sisi ${servingSide}.`;

        scoreboard.querySelector('[data-game-label]').textContent = `Game ${Math.min(currentGame, 3)}`;
        scoreboard.querySelector('[data-game-history]').textContent = state.completedGames.length
            ? state.completedGames.map((score) => score.join('–')).join(' · ')
            : 'Belum ada game selesai';
        scoreboard.querySelector('[data-score-status]').textContent = status;
        scoreboard.querySelector('[data-score-next]').hidden = !state.gameOver || state.matchWinner !== null;
        scoreboard.querySelector('[data-score-undo]').disabled = history.length === 0;
        save();
    };

    const remember = () => {
        history.push(copyState());
        history = history.slice(-50);
    };

    scoreboard.querySelectorAll('[data-add-point]').forEach((button) => {
        button.addEventListener('click', () => {
            if (state.gameOver || state.matchWinner !== null) {
                return;
            }

            remember();
            const index = teamIndex(button.dataset.addPoint);
            state = addBadmintonPoint(state, index);
            render();
        });
    });

    scoreboard.querySelectorAll('[data-team-name]').forEach((input) => {
        input.addEventListener('input', () => {
            state.teams[teamIndex(input.dataset.teamName)] = input.value.slice(0, 30);
            save();
            render();
            input.focus();
        });
    });

    scoreboard.querySelector('[data-score-next]').addEventListener('click', () => {
        if (!state.gameOver || state.matchWinner !== null) {
            return;
        }

        remember();
        state.scores = [0, 0];
        state.gameOver = false;
        state.servingTeam = state.completedGames.length % 2;
        render();
    });

    scoreboard.querySelector('[data-score-undo]').addEventListener('click', () => {
        const previousState = history.pop();

        if (previousState) {
            state = previousState;
            render();
        }
    });

    scoreboard.querySelector('[data-score-reset]').addEventListener('click', () => {
        if (!window.confirm('Mulai ulang seluruh pertandingan?')) {
            return;
        }

        state = { ...defaultState, teams: [...state.teams], scores: [0, 0], games: [0, 0], completedGames: [] };
        history = [];
        render();
    });

    render();
}
