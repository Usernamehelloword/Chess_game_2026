// Game page orchestrator: clocks, multiplayer polling, bot turns, controls.
import { GameBoard } from './board';

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function post(url, data = {}) {
    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            Accept: 'application/json',
        },
        body: JSON.stringify(data),
    });
    return res.json();
}

function fmtClock(ms) {
    if (ms <= 0) return '0:00';
    const totalSec = Math.floor(ms / 1000);
    const m = Math.floor(totalSec / 60);
    const s = totalSec % 60;
    if (ms < 20000) {
        const tenths = Math.floor((ms % 1000) / 100);
        return `${m}:${String(s).padStart(2, '0')}.${tenths}`;
    }
    return `${m}:${String(s).padStart(2, '0')}`;
}

function confirmDialog(title, message, actionLabel, danger = true) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40';
        overlay.innerHTML = `
            <div class="pop-in w-[90%] max-w-sm rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-zinc-900">${title}</h3>
                <p class="mt-2 text-sm text-zinc-600">${message}</p>
                <div class="mt-5 flex justify-end gap-2">
                    <button data-act="cancel" class="rounded-xl px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100">Cancel</button>
                    <button data-act="ok" class="rounded-xl px-4 py-2 text-sm font-semibold text-white ${danger ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'}">${actionLabel}</button>
                </div>
            </div>`;
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) { overlay.remove(); resolve(false); }
            if (e.target.dataset?.act === 'cancel') { overlay.remove(); resolve(false); }
            if (e.target.dataset?.act === 'ok') { overlay.remove(); resolve(true); }
        });
        document.body.appendChild(overlay);
    });
}

export function initGamePage(config) {
    const boardEl = document.getElementById('board');
    if (! boardEl) return;

    const state = {
        code: config.code,
        mode: config.mode,
        myColor: config.myColor,
        base: config.base * 1000,
        inc: config.inc * 1000,
        clocks: { white: config.base * 1000, black: config.base * 1000 },
        running: null,
        lastTick: null,
        moveCount: config.moveCount,
        finished: config.finished,
        drawOfferedBy: config.drawOfferedBy,
        botThinking: false,
        gameOverShown: config.finished,
        pollTimer: null,
    };

    const sanListEl = document.getElementById('move-list');
    const statusEl = document.getElementById('game-status');

    const board = new GameBoard(boardEl, {
        interactive: false,
        flipped: state.myColor === 'black',
        onUserMove: (uci) => handleUserMove(uci),
    });

    for (const m of config.moves) {
        board.applyUci(m.uci, { animate: false });
        appendMoveList(m.san, m.color, Math.ceil(m.n / 2));
    }
    if (config.lastMove) {
        board.lastMove = { from: config.lastMove.slice(0, 2), to: config.lastMove.slice(2, 4) };
    }
    board.render();
    board.setInteractive(! state.finished);

    // ------------------------------------------------------------------
    // Clocks
    // ------------------------------------------------------------------

    function tick() {
        if (state.finished || ! state.running) return;
        const now = performance.now();
        const delta = now - state.lastTick;
        state.lastTick = now;
        state.clocks[state.running] = Math.max(0, state.clocks[state.running] - delta);
        if (state.clocks[state.running] <= 0) {
            onFlag(state.running);
        }
        renderClocks();
    }

    setInterval(tick, 100);

    function setActiveClock(color) {
        state.running = state.finished ? null : color;
        state.lastTick = performance.now();
        renderClocks();
    }

    function onMovePlayed(byColor) {
        state.clocks[byColor] += state.inc;
        setActiveClock(byColor === 'white' ? 'black' : 'white');
    }

    function renderClocks() {
        for (const color of ['white', 'black']) {
            const el = document.getElementById(`clock-${color}`);
            if (! el) continue;
            el.textContent = fmtClock(state.clocks[color]);
            el.classList.toggle('clock-active', state.running === color);
            el.classList.toggle('clock-low', state.clocks[color] < 30000 && ! state.finished);
        }
    }

    async function onFlag(flaggedColor) {
        if (state.finished) return;
        state.finished = true;
        state.running = null;
        renderClocks();
        await post(`/games/${state.code}/timeout`);
        showResult(flaggedColor === 'white' ? 'black' : 'white', 'timeout');
    }

    // ------------------------------------------------------------------
    // Move list
    // ------------------------------------------------------------------

    function appendMoveList(san, color, num) {
        if (! sanListEl || ! san) return;
        const row = document.createElement('div');
        row.className = 'flex gap-2 px-3 py-1 text-sm';
        const moveNo = num ?? Math.ceil(state.moveCount / 2);
        if (color === 'white') {
            row.innerHTML = `<span class="w-8 shrink-0 text-zinc-400">${moveNo}.</span><span class="w-16 font-medium text-zinc-800">${san}</span>`;
        } else {
            const span = document.createElement('span');
            span.className = 'w-16 font-medium text-zinc-800';
            span.textContent = san;
            const lastRow = sanListEl.lastElementChild;
            if (lastRow && ! lastRow.dataset.full) {
                lastRow.appendChild(span);
                lastRow.dataset.full = '1';
                return;
            }
            row.innerHTML = `<span class="w-8 shrink-0 text-zinc-400">${moveNo}...</span>`;
            row.appendChild(span);
        }
        sanListEl.appendChild(row);
        sanListEl.scrollTop = sanListEl.scrollHeight;
    }

    // ------------------------------------------------------------------
    // Playing moves
    // ------------------------------------------------------------------

    function myTurn() {
        const turn = board.turn() === 'w' ? 'white' : 'black';
        return turn === state.myColor;
    }

    function updateInteractivity() {
        board.setInteractive(! state.finished && ! state.botThinking && myTurn());
    }

    async function handleUserMove(uci) {
        if (state.finished || state.botThinking || ! myTurn()) return;

        const res = await post(`/games/${state.code}/move`, { uci });
        if (! res.ok) {
            showToast(res.error || 'Move rejected');
            return;
        }

        board.applyUci(uci);
        state.moveCount += 1;
        appendMoveList(res.san, state.myColor);
        onMovePlayed(state.myColor);
        updateInteractivity();

        if (res.finished) {
            finishGame(res.winner, res.reason);
            return;
        }

        if (state.mode === 'bot') {
            askBotMove();
        }
    }

    async function askBotMove() {
        if (state.finished) return;
        state.botThinking = true;
        setStatus('Bot is thinking<span class="thinking-dot">.</span><span class="thinking-dot">.</span><span class="thinking-dot">.</span>');
        try {
            const res = await post(`/games/${state.code}/bot-move`);
            state.botThinking = false;
            if (res.ok) {
                board.applyUci(lastUciFromServer(res));
                state.moveCount += 1;
                appendMoveList(res.san, state.myColor === 'white' ? 'black' : 'white');
                onMovePlayed(state.myColor === 'white' ? 'black' : 'white');
                if (res.finished) {
                    finishGame(res.winner, res.reason);
                    return;
                }
            }
        } catch {
            state.botThinking = false;
        }
        updateInteractivity();
        setStatus(defaultStatus());
    }

    function lastUciFromServer(res) {
        // The move endpoint returns only SAN; fetch last move from board diff
        // by asking state endpoint is heavy — instead the controller returns
        // the uci via `uci` field when available.
        return res.uci;
    }

    // ------------------------------------------------------------------
    // Status bar / toasts
    // ------------------------------------------------------------------

    function defaultStatus() {
        if (state.finished) return 'Game over';
        if (state.mode === 'bot') {
            return state.botThinking ? 'Bot is thinking...' : (myTurn() ? 'Your move' : 'Waiting for bot...');
        }
        if (state.moveCount < 2 && state.mode === 'multi') {
            return myTurn() ? 'Your move' : 'Waiting for opponent...';
        }
        return myTurn() ? 'Your move' : 'Opponent is thinking<span class="thinking-dot">.</span><span class="thinking-dot">.</span><span class="thinking-dot">.</span>';
    }

    function setStatus(html) {
        if (statusEl) statusEl.innerHTML = html;
    }

    function showToast(message) {
        const t = document.createElement('div');
        t.className = 'fade-in fixed bottom-6 left-1/2 z-50 -translate-x-1/2 rounded-xl bg-zinc-900 px-4 py-2 text-sm text-white shadow-lg';
        t.textContent = message;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 2200);
    }

    // ------------------------------------------------------------------
    // Multiplayer polling
    // ------------------------------------------------------------------

    async function poll() {
        if (state.finished || state.mode !== 'multi') return;
        try {
            const res = await fetch(`/games/${state.code}/state?since=${state.moveCount}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await res.json();
            if (! data.ok) return;
            const s = data.state;

            // Apply opponent moves.
            for (const m of s.moves) {
                board.applyUci(m.uci);
                state.moveCount = Math.max(state.moveCount, m.n);
                appendMoveList(m.san, m.color);
                onMovePlayed(m.color);
                state.drawOfferedBy = null;
            }
            updateInteractivity();

            // Draw offer handling.
            if (s.game.draw_offered_by && s.game.draw_offered_by !== state.myColor && ! state.finished) {
                showDrawOffer();
            }

            if (s.game.status === 'finished' && ! state.finished) {
                state.finished = true;
                state.running = null;
                renderClocks();
                showResult(s.game.winner, s.game.result_reason);
            }
        } catch {
            // network hiccup; retry next tick
        }
    }

    let drawOfferVisible = false;
    function showDrawOffer() {
        if (drawOfferVisible) return;
        drawOfferVisible = true;
        confirmDialog('Draw offered', 'Your opponent offers a draw. Accept?', 'Accept', false).then(async (yes) => {
            drawOfferVisible = false;
            await post(`/games/${state.code}/draw`, { action: yes ? 'accept' : 'decline' });
            if (yes) {
                state.finished = true;
                state.running = null;
                renderClocks();
                showResult('draw', 'agreement');
            }
        });
    }

    // ------------------------------------------------------------------
    // Result modal
    // ------------------------------------------------------------------

    const REASON_LABELS = {
        checkmate: 'Checkmate',
        resignation: 'Resignation',
        timeout: 'Time out',
        stalemate: 'Stalemate',
        'insufficient-material': 'Insufficient material',
        'fifty-move-rule': 'Fifty-move rule',
        'threefold-repetition': 'Threefold repetition',
        agreement: 'Draw agreed',
    };

    function showResult(winner, reason) {
        if (state.gameOverShown) return;
        state.gameOverShown = true;
        state.finished = true;
        state.running = null;
        renderClocks();
        setStatus('Game over');

        const iWon = winner === state.myColor;
        const isDraw = winner === 'draw' || winner == null;
        const title = isDraw ? 'Draw' : (iWon ? 'You Won! 🎉' : 'Game Over');
        const subtitle = isDraw
            ? 'The game ended in a draw.'
            : (iWon ? `You won by ${REASON_LABELS[reason] || reason}.` : `You lost by ${REASON_LABELS[reason] || reason}.`);
        const emoji = isDraw ? '🤝' : (iWon ? '🏆' : '😔');

        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50';
        overlay.innerHTML = `
            <div class="pop-in w-[92%] max-w-md rounded-3xl bg-white p-8 text-center shadow-2xl">
                <div class="text-5xl">${emoji}</div>
                <h2 class="mt-3 text-2xl font-bold ${isDraw ? 'text-zinc-900' : (iWon ? 'text-emerald-600' : 'text-zinc-900')}">${title}</h2>
                <p class="mt-1 text-sm text-zinc-500">${subtitle}</p>
                <p class="mt-1 text-xs text-zinc-400">${state.moveCount} moves played</p>
                <div class="mt-6 flex flex-col gap-2">
                    <button data-act="rematch" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Play Again</button>
                    <button data-act="home" class="rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">Return Home</button>
                </div>
            </div>`;
        overlay.addEventListener('click', async (e) => {
            const act = e.target.dataset?.act;
            if (! act) return;
            if (act === 'home') window.location.href = '/';
            if (act === 'rematch') {
                const res = await post(`/games/${state.code}/rematch`);
                if (res.ok) {
                    window.location.href = `/games/${res.code}`;
                } else {
                    showToast(res.error || 'Could not start a rematch');
                }
            }
        });
        document.body.appendChild(overlay);
    }

    function finishGame(winner, reason) {
        state.finished = true;
        state.finished = true;
        state.running = null;
        board.setInteractive(false);
        renderClocks();
        setStatus('Game over');
        showResult(winner, reason);
    }

    // ------------------------------------------------------------------
    // Controls
    // ------------------------------------------------------------------

    document.getElementById('btn-flip')?.addEventListener('click', () => {
        board.setFlipped(! board.flipped);
    });

    document.getElementById('btn-resign')?.addEventListener('click', async () => {
        if (state.finished) return;
        const ok = await confirmDialog('Resign', 'Are you sure you want to resign?', 'Resign');
        if (! ok) return;
        const res = await post(`/games/${state.code}/resign`);
        if (res.ok) {
            finishGame(state.myColor === 'white' ? 'black' : 'white', 'resignation');
        } else {
            showToast(res.error || 'Cannot resign');
        }
    });

    document.getElementById('btn-draw')?.addEventListener('click', async () => {
        if (state.finished) return;
        const ok = await confirmDialog('Offer draw', 'Offer your opponent a draw?', 'Offer draw', false);
        if (! ok) return;
        const res = await post(`/games/${state.code}/draw`, { action: 'offer' });
        if (res.ok) {
            setStatus('Draw offered — waiting for response...');
            showToast('Draw offered');
        } else {
            showToast(res.error || 'Cannot offer a draw');
        }
    });

    // ------------------------------------------------------------------
    // Startup
    // ------------------------------------------------------------------

    if (state.finished) {
        if (config.winner) {
            showResult(config.winner, config.reason || 'game-over');
        }
    } else {
        const turnColor = board.turn() === 'w' ? 'white' : 'black';
        setActiveClock(turnColor);
        setStatus(defaultStatus());
        if (state.mode === 'multi') {
            state.pollTimer = setInterval(poll, 1500);
        }
        if (state.mode === 'bot' && ! myTurn()) {
            askBotMove();
        }
    }
}