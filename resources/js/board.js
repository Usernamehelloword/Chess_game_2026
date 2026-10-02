// Interactive chessboard renderer. Rules are validated with chess.js;
// the server re-validates every move authoritatively.
import { Chess } from 'chess.js';

const FILES = 'abcdefgh';

function pieceImage(type, color) {
    return `/pieces/${color}${type.toUpperCase()}.svg`;
}

function squareName(fileIdx, rankIdx) {
    return FILES[fileIdx] + (rankIdx + 1);
}

export class GameBoard {
    constructor(rootEl, { interactive = false, onUserMove = null, flipped = false } = {}) {
        this.root = rootEl;
        this.chess = new Chess();
        this.interactive = interactive;
        this.onUserMove = onUserMove;
        this.flipped = flipped;
        this.selected = null;
        this.lastMove = null;
        this.squares = {};
        this.animSquare = null;
        this.build();
        this.render();
    }

    build() {
        this.root.classList.add('chess-board');
        this.root.innerHTML = '';
        this.squares = {};

        for (let row = 0; row < 8; row++) {
            for (let col = 0; col < 8; col++) {
                const fileIdx = this.flipped ? 7 - col : col;
                const rankIdx = this.flipped ? row : 7 - row;
                const name = squareName(fileIdx, rankIdx);

                const sq = document.createElement('div');
                sq.dataset.square = name;
                sq.className = 'square ' + ((fileIdx + rankIdx) % 2 === 0 ? 'sq-light' : 'sq-dark');

                // Edge coordinates
                if (row === 7) {
                    const c = document.createElement('span');
                    c.className = 'sq-coord file';
                    c.textContent = FILES[fileIdx];
                    sq.appendChild(c);
                }
                if (col === 0) {
                    const c = document.createElement('span');
                    c.className = 'sq-coord rank';
                    c.textContent = rankIdx + 1;
                    sq.appendChild(c);
                }

                if (this.interactive) {
                    sq.classList.add('sq-interactive');
                    sq.addEventListener('click', () => this.onSquareClick(name));
                }

                this.root.appendChild(sq);
                this.squares[name] = sq;
            }
        }
    }

    onSquareClick(name) {
        if (! this.interactive) return;
        const piece = this.chess.get(name);

        if (this.selected) {
            if (name === this.selected) {
                this.selected = null;
                this.render();
                return;
            }
            const legal = this.chess.moves({ square: this.selected, verbose: true });
            const target = legal.find((m) => m.to === name);
            if (target) {
                if (target.promotion) {
                    this.askPromotion(target.color, (promo) => {
                        this.selected = null;
                        this.render();
                        this.onUserMove?.(target.from + target.to + promo);
                    });
                } else {
                    const from = this.selected;
                    this.selected = null;
                    this.render();
                    this.onUserMove?.(from + name);
                }
                return;
            }
        }

        if (piece && piece.color === this.chess.turn()) {
            this.selected = name;
        } else {
            this.selected = null;
        }
        this.render();
    }

    setInteractive(interactive) {
        if (this.interactive === interactive) {
            return;
        }
        this.interactive = interactive;
        for (const [name, el] of Object.entries(this.squares)) {
            if (interactive) {
                const handler = () => this.onSquareClick(name);
                el._clickHandler = handler;
                el.classList.add('sq-interactive');
                el.addEventListener('click', handler);
            } else {
                el.classList.remove('sq-interactive');
                if (el._clickHandler) {
                    el.removeEventListener('click', el._clickHandler);
                    el._clickHandler = null;
                }
            }
        }
    }

    askPromotion(color, cb) {
        const existing = document.getElementById('promotion-chooser');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.id = 'promotion-chooser';
        overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/40';
        const pieces = ['q', 'r', 'b', 'n'];
        const box = document.createElement('div');
        box.className = 'pop-in flex gap-2 rounded-2xl bg-white p-4 shadow-xl';
        pieces.forEach((p) => {
            const btn = document.createElement('button');
            btn.className = 'h-16 w-16 rounded-xl border border-zinc-200 p-1 transition hover:bg-amber-100';
            const img = document.createElement('img');
            img.src = pieceImage(p, color);
            img.className = 'h-full w-full';
            img.alt = p;
            btn.appendChild(img);
            btn.addEventListener('click', () => {
                overlay.remove();
                cb(p);
            });
            box.appendChild(btn);
        });
        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }

    legalTargets(square) {
        return this.chess.moves({ square, verbose: true });
    }

    loadFen(fen) {
        this.chess.load(fen);
        this.selected = null;
        this.render();
    }

    applyUci(uci, { animate = true } = {}) {
        const from = uci.slice(0, 2);
        const to = uci.slice(2, 4);
        const promo = uci.slice(4, 5) || undefined;

        // FLIP: capture the moving piece's position before the re-render.
        let startRect = null;
        if (animate) {
            const fromSquare = this.squares[from];
            const pieceEl = fromSquare?.querySelector('.piece');
            if (pieceEl) {
                startRect = pieceEl.getBoundingClientRect();
            }
        }

        const move = this.chess.move({ from, to, promotion: promo });
        if (! move) {
            return false;
        }
        this.lastMove = { from, to };
        this.render();

        // FLIP: slide the freshly-rendered piece from its old position to the new one.
        if (animate && startRect) {
            const toSquare = this.squares[to];
            const pieceEl = toSquare?.querySelector('.piece');
            if (pieceEl) {
                const endRect = pieceEl.getBoundingClientRect();
                const dx = startRect.left - endRect.left;
                const dy = startRect.top - endRect.top;
                if (Math.abs(dx) > 0.5 || Math.abs(dy) > 0.5) {
                    pieceEl.classList.add('piece-lifting');
                    const anim = pieceEl.animate([
                        { transform: `translate(${dx}px, ${dy}px)` },
                        { transform: 'translate(0, 0)' },
                    ], {
                        duration: 220,
                        easing: 'cubic-bezier(0.22, 0.61, 0.36, 1)',
                    });
                    anim.onfinish = () => pieceEl.classList.remove('piece-lifting');
                }
            }
        }

        return true;
    }

    reset() {
        this.chess.reset();
        this.lastMove = null;
        this.selected = null;
        this.render();
    }

    fen() {
        return this.chess.fen();
    }

    turn() {
        return this.chess.turn(); // 'w' | 'b'
    }

    isGameOver() {
        return this.chess.isGameOver();
    }

    gameResult() {
        if (this.chess.isCheckmate()) {
            return { winner: this.chess.turn() === 'w' ? 'black' : 'white', reason: 'checkmate' };
        }
        if (this.chess.isStalemate()) return { winner: 'draw', reason: 'stalemate' };
        if (this.chess.isInsufficientMaterial()) return { winner: 'draw', reason: 'insufficient-material' };
        if (this.chess.isThreefoldRepetition()) return { winner: 'draw', reason: 'threefold-repetition' };
        if (this.chess.isDraw()) return { winner: 'draw', reason: 'draw' };
        return null;
    }

    render() {
        const board = this.chess.board();
        const turnColor = this.chess.turn();

        // Clear dynamic state on all squares.
        for (const [, el] of Object.entries(this.squares)) {
            el.querySelectorAll('.piece').forEach((p) => p.remove());
            el.classList.remove('hl-last', 'hl-selected', 'hl-check', 'hl-move', 'hl-capture');
        }

        // Last move highlight.
        if (this.lastMove) {
            this.squares[this.lastMove.from]?.classList.add('hl-last');
            this.squares[this.lastMove.to]?.classList.add('hl-last');
        }

        // Check highlight on the king of the side to move.
        if (this.chess.inCheck()) {
            for (const row of board) {
                for (const cell of row) {
                    if (cell && cell.type === 'k' && cell.color === turnColor) {
                        this.squares[cell.square]?.classList.add('hl-check');
                    }
                }
            }
        }

        // Pieces.
        for (const row of board) {
            for (const cell of row) {
                if (! cell) continue;
                const sqEl = this.squares[cell.square];
                if (! sqEl) continue;

                const wrap = document.createElement('div');
                wrap.className = 'piece';
                if (cell.square === this.animSquare) wrap.classList.add('piece-anim');
                const img = document.createElement('img');
                img.src = pieceImage(cell.type, cell.color);
                img.alt = cell.color + cell.type;
                img.draggable = false;
                img.className = 'piece-img';
                wrap.appendChild(img);
                sqEl.appendChild(wrap);
            }
        }
        this.animSquare = null;

        // Selection + legal move markers.
        if (this.selected) {
            this.squares[this.selected]?.classList.add('hl-selected');
            for (const m of this.legalTargets(this.selected)) {
                const el = this.squares[m.to];
                if (! el) continue;
                if (this.chess.get(m.to)) {
                    el.classList.add('hl-capture');
                } else {
                    el.classList.add('hl-move');
                }
            }
        }
    }

    setFlipped(flipped) {
        if (this.flipped === flipped) return;
        this.flipped = flipped;
        this.selected = null;
        this.build();
        this.render();
    }
}

/** Static, non-interactive preview board (home hero, etc). */
export function renderStaticBoard(rootEl, fen, flipped = false) {
    const board = new GameBoard(rootEl, { interactive: false, flipped });
    board.loadFen(fen);
    return board;
}