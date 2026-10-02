<?php

namespace App\Services;

final class ChessBot
{
    private const INF = 10_000_000;
    private const MATE = 100_000;

    /** Piece values indexed by piece type (1=P 2=N 3=B 4=R 5=Q 6=K). */
    private const VALUES = [0, 100, 320, 330, 500, 900, 0];

    private static ?array $pst = null;
    private static int $nodes = 0;
    private static bool $timeout = false;

    /**
     * Pick a move for the side to move.
     *
     * @return int|null packed move (see ChessEngine::mkMove) or null if no legal moves
     */
    public static function bestMove(Position $pos, string $difficulty): ?int
    {
        $config = match ($difficulty) {
            'beginner' => ['depth' => 2, 'time' => 0.5, 'blunder' => 0.30],
            'intermediate' => ['depth' => 3, 'time' => 1.5, 'blunder' => 0.08],
            'advanced' => ['depth' => 4, 'time' => 3.5, 'blunder' => 0.0],
            default => ['depth' => 5, 'time' => 6.0, 'blunder' => 0.0],
        };

        $moves = ChessEngine::legalMoves($pos);
        if ($moves === []) {
            return null;
        }
        if (count($moves) === 1) {
            return $moves[0];
        }

        // Weaker levels occasionally play a deliberately weak (but not instantly losing) move.
        if ($config['blunder'] > 0 && (mt_rand() / mt_getrandmax()) < $config['blunder']) {
            $safe = [];
            foreach ($moves as $m) {
                $child = ChessEngine::applyMove($pos, $m);
                if (ChessEngine::statusOf($child) !== 'checkmate') {
                    $safe[] = $m;
                }
            }
            if ($safe !== []) {
                return $safe[array_rand($safe)];
            }
        }

        self::$nodes = 0;
        self::$timeout = false;
        $deadline = microtime(true) + $config['time'];

        $best = null;
        for ($depth = 1; $depth <= $config['depth']; $depth++) {
            $result = self::searchRoot($pos, $depth, $deadline);
            if ($result === null) {
                break;
            }
            $best = $result['move'];
            if (self::$timeout || abs($result['score']) >= self::MATE - 100) {
                break;
            }
        }

        return $best ?? $moves[array_rand($moves)];
    }

    /** @return array{move: int, score: int}|null */
    private static function searchRoot(Position $pos, int $depth, float $deadline): ?array
    {
        $moves = self::orderMoves($pos, ChessEngine::legalMoves($pos));
        $best = null;
        $bestScore = -self::INF;
        $alpha = -self::INF;

        foreach ($moves as $m) {
            $child = ChessEngine::applyMove($pos, $m);
            $score = -self::negamax($child, $depth - 1, -self::INF, -$alpha, $deadline, 1);

            if (self::$timeout) {
                return $best === null ? null : ['move' => $best, 'score' => $bestScore];
            }
            if ($score > $bestScore || $best === null) {
                $bestScore = $score;
                $best = $m;
            }
            if ($score > $alpha) {
                $alpha = $score;
            }
        }

        return ['move' => $best, 'score' => $bestScore];
    }

    private static function negamax(Position $pos, int $depth, int $alpha, int $beta, float $deadline, int $ply): int
    {
        self::$nodes++;
        if ((self::$nodes & 511) === 0 && microtime(true) > $deadline) {
            self::$timeout = true;

            return 0;
        }

        $moves = ChessEngine::legalMoves($pos);
        if ($moves === []) {
            return ChessEngine::inCheck($pos, $pos->side) ? -(self::MATE - $ply) : 0;
        }
        if ($depth === 0) {
            return self::quiesce($pos, $alpha, $beta, $deadline, $ply, 6);
        }

        $moves = self::orderMoves($pos, $moves);
        $best = -self::INF;
        foreach ($moves as $m) {
            $child = ChessEngine::applyMove($pos, $m);
            $score = -self::negamax($child, $depth - 1, -$beta, -$alpha, $deadline, $ply + 1);
            if (self::$timeout) {
                return 0;
            }
            if ($score > $best) {
                $best = $score;
            }
            if ($best > $alpha) {
                $alpha = $best;
            }
            if ($alpha >= $beta) {
                break;
            }
        }

        return $best;
    }

    private static function quiesce(Position $pos, int $alpha, int $beta, float $deadline, int $ply, int $qdepth): int
    {
        self::$nodes++;
        if ((self::$nodes & 511) === 0 && microtime(true) > $deadline) {
            self::$timeout = true;

            return 0;
        }

        $sign = $pos->side === ChessEngine::WHITE ? 1 : -1;
        $stand = $sign * self::evaluate($pos);
        if ($stand >= $beta) {
            return $stand;
        }
        if ($stand > $alpha) {
            $alpha = $stand;
        }
        if ($qdepth === 0 || self::$timeout) {
            return $alpha;
        }

        $captures = [];
        foreach (ChessEngine::legalMoves($pos) as $m) {
            if ($pos->board[ChessEngine::moveTo($m)] !== 0 || ChessEngine::movePromo($m) !== 0) {
                $captures[] = $m;
            }
        }
        $captures = self::orderMoves($pos, $captures);

        foreach ($captures as $m) {
            $child = ChessEngine::applyMove($pos, $m);
            $score = -self::quiesce($child, -$beta, -$alpha, $deadline, $ply + 1, $qdepth - 1);
            if (self::$timeout) {
                return 0;
            }
            if ($score >= $beta) {
                return $score;
            }
            if ($score > $alpha) {
                $alpha = $score;
            }
        }

        return $alpha;
    }

    private static function orderMoves(Position $pos, array $moves): array
    {
        $board = $pos->board;
        usort($moves, fn ($a, $b) => self::moveScore($board, $b) <=> self::moveScore($board, $a));

        return $moves;
    }

    private static function moveScore(array $board, int $m): int
    {
        $victim = $board[ChessEngine::moveTo($m)];
        $attacker = $board[ChessEngine::moveFrom($m)];
        $score = 0;
        if ($victim !== 0) {
            $score = 10 * self::VALUES[ChessEngine::typeOf($victim)] - self::VALUES[ChessEngine::typeOf($attacker)];
        }
        if (ChessEngine::movePromo($m) !== 0) {
            $score += 800;
        }

        return $score;
    }

    private static function evaluate(Position $pos): int
    {
        self::ensureTables();
        $score = 0;
        foreach ($pos->board as $i => $p) {
            if ($p === 0) {
                continue;
            }
            $type = ChessEngine::typeOf($p);
            $r = $i >> 3;
            $f = $i & 7;
            if ($p <= 6) {
                $score += self::VALUES[$type] + self::$pst[$type][$r * 8 + $f];
            } else {
                $score -= self::VALUES[$type] + self::$pst[$type][(7 - $r) * 8 + $f];
            }
        }

        return $score;
    }

    /** Piece-square tables, White's point of view, index = rank*8+file (rank 0 = White's first rank). */
    private static function ensureTables(): void
    {
        if (self::$pst !== null) {
            return;
        }

        self::$pst = [
            1 => [ // pawn
                0, 0, 0, 0, 0, 0, 0, 0,
                5, 10, 10, -20, -20, 10, 10, 5,
                5, -5, -10, 0, 0, -10, -5, 5,
                0, 0, 0, 20, 20, 0, 0, 0,
                5, 5, 10, 25, 25, 10, 5, 5,
                10, 10, 20, 30, 30, 20, 10, 10,
                50, 50, 50, 50, 50, 50, 50, 50,
                0, 0, 0, 0, 0, 0, 0, 0,
            ],
            2 => [ // knight
                -50, -40, -30, -30, -30, -30, -40, -50,
                -40, -20, 0, 5, 5, 0, -20, -40,
                -30, 5, 10, 15, 15, 10, 5, -30,
                -30, 0, 15, 20, 20, 15, 0, -30,
                -30, 5, 15, 20, 20, 15, 5, -30,
                -30, 0, 10, 15, 15, 10, 0, -30,
                -40, -20, 0, 0, 0, 0, -20, -40,
                -50, -40, -30, -30, -30, -30, -40, -50,
            ],
            3 => [ // bishop
                -20, -10, -10, -10, -10, -10, -10, -20,
                -10, 5, 0, 0, 0, 0, 5, -10,
                -10, 10, 10, 10, 10, 10, 10, -10,
                -10, 0, 10, 10, 10, 10, 0, -10,
                -10, 5, 5, 10, 10, 5, 5, -10,
                -10, 0, 5, 10, 10, 5, 0, -10,
                -10, 0, 0, 0, 0, 0, 0, -10,
                -20, -10, -10, -10, -10, -10, -10, -20,
            ],
            4 => [ // rook
                0, 0, 0, 5, 5, 0, 0, 0,
                -5, 0, 0, 0, 0, 0, 0, -5,
                -5, 0, 0, 0, 0, 0, 0, -5,
                -5, 0, 0, 0, 0, 0, 0, -5,
                -5, 0, 0, 0, 0, 0, 0, -5,
                -5, 0, 0, 0, 0, 0, 0, -5,
                5, 10, 10, 10, 10, 10, 10, 5,
                0, 0, 0, 0, 0, 0, 0, 0,
            ],
            5 => [ // queen
                -20, -10, -10, -5, -5, -10, -10, -20,
                -10, 0, 5, 0, 0, 0, 0, -10,
                -10, 5, 5, 5, 5, 5, 0, -10,
                0, 0, 5, 5, 5, 5, 0, -5,
                -5, 0, 5, 5, 5, 5, 0, -5,
                -10, 0, 5, 5, 5, 5, 0, -10,
                -10, 0, 0, 0, 0, 0, 0, -10,
                -20, -10, -10, -5, -5, -10, -10, -20,
            ],
            6 => [ // king (middlegame)
                20, 30, 10, 0, 0, 10, 30, 20,
                20, 20, 0, 0, 0, 0, 20, 20,
                -10, -20, -20, -20, -20, -20, -20, -10,
                -20, -30, -30, -40, -40, -30, -30, -20,
                -30, -40, -40, -50, -50, -40, -40, -30,
                -30, -40, -40, -50, -50, -40, -40, -30,
                -30, -40, -40, -50, -50, -40, -40, -30,
                -30, -40, -40, -50, -50, -40, -40, -30,
            ],
        ];
    }
}