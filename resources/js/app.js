import { renderStaticBoard } from './board';
import { initGamePage } from './game';

// Page dispatch based on data-page on <body>.
const page = document.body.dataset.page;

if (page === 'home') {
    const heroBoard = document.getElementById('hero-board');
    if (heroBoard) {
        renderStaticBoard(heroBoard, heroBoard.dataset.fen, false);
    }
}

if (page === 'game') {
    const el = document.getElementById('game-config');
    if (el) {
        initGamePage(JSON.parse(el.textContent));
    }
}

// Lobby waiting room: poll until opponent joins, then reload.
if (page === 'game-waiting') {
    const code = document.body.dataset.code;
    const timer = setInterval(async () => {
        try {
            const res = await fetch(`/games/${code}/waiting`, { headers: { Accept: 'application/json' } });
            const data = await res.json();
            if (data.ok && data.status === 'active') {
                clearInterval(timer);
                window.location.reload();
            }
        } catch {
            // retry
        }
    }, 1500);
}

// Copy game code buttons.
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-copy]');
    if (! btn) return;
    navigator.clipboard?.writeText(btn.dataset.copy).then(() => {
        btn.textContent = 'Copied!';
        setTimeout(() => { btn.textContent = 'Copy Game ID'; }, 1500);
    });
});

