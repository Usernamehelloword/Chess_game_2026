<?php

namespace App\Services;

final class ChessEngine
{
    public const START_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

    // Piece codes: type = code % 10 (1=P 2=N 3=B 4=R 5=Q 6=K), color = code <= 6.
    public const WP = 1;
    public const WN = 2;
    public const WB = 3;
    public const WR = 4;
    public const WQ = 5;
    public const WK = 6;
    public const BP = 11;
    public const BN = 12;
    public const BB = 13;
    public const BR = 14;
    public const BQ = 15;
    public const BK = 16;

    public const WHITE = 1;
    public const BLACK = 2;

    public const CASTLE_WK = 1;
    public const CASTLE_WQ = 2;
    public const CASTLE_BK = 4;
    public const CASTLE_BQ = 8;

    private const KNIGHT_DELTAS = [[1, 2], [2, 1], [-1, 2], [-2, 1], [1, -2], [2, -1], [-1, -2], [-2, -1]];
    private const KING_DELTAS = [[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [1, -1], [-1, 1], [-1, -1]];
    private const ROOK_DIRS = [[1, 0], [-1, 0], [0, 1], [0, -1]];
    private const BISHOP_DIRS = [[1, 1], [1, -1], [-1, 1], [-1, -1]];

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private static function idx(int $r, int $f): int
    {
        return ($r << 3) | $f;
    }

    private static function onBoard(int $r, int $f): bool
    {
        return $r >= 0 && $r < 8 && $f >= 0 && $f < 8;
    }

    public static function colorOf(int $piece): int
    {
        return $piece === 0 ? 0 : ($piece <= 6 ? 1 : 2);
    }

    public static function typeOf(int $piece): int
    {
        return $piece === 0 ? 0 : ($piece <= 6 ? $piece : $piece - 10);
    }

    public static function sqName(int $i): string
    {
        return chr(97 + ($i & 7)).(string) (($i >> 3) + 1);
    }

    public static function sqIndex(string $name): int
    {
        $file = ord(strtolower($name[0])) - 97;
        $rank = (int) $name[1] - 1;

        return ($rank << 3) | $file;
    }

    // Move encoding: from << 12 | to << 3 | promo (promo: 0 none, 1 Q, 2 R, 3 B, 4 N)
    public static function mkMove(int $from, int $to, int $promo = 0): int
    {
        return ($from << 12) | ($to << 3) | $promo;
    }

    public static function moveFrom(int $m): int
    {
        return $m >> 12;
    }

    public static function moveTo(int $m): int
    {
        return ($m >> 3) & 63;
    }

    public static function movePromo(int $m): int
    {
        return $m & 7;
    }

    public static function moveFromUci(string $uci): ?int
    {
        if (! preg_match('/^([a-h][1-8])([a-h][1-8])([qrbn])?$/', $uci, $m)) {
            return null;
        }

        $promo = match ($m[3] ?? '') {
            'q' => 1, 'r' => 2, 'b' => 3, 'n' => 4, default => 0,
        };

        return self::mkMove(self::sqIndex($m[1]), self::sqIndex($m[2]), $promo);
    }

    public static function uciOfMove(int $move): string
    {
        $promo = match (self::movePromo($move)) {
            1 => 'q', 2 => 'r', 3 => 'b', 4 => 'n', default => '',
        };

        return self::sqName(self::moveFrom($move)).self::sqName(self::moveTo($move)).$promo;
    }

    // ------------------------------------------------------------------
    // FEN
    // ------------------------------------------------------------------

    public static function fromFen(string $fen): Position
    {
        $parts = preg_split('/\s+/', trim($fen));
        $rows = explode('/', $parts[0]);
        $board = array_fill(0, 64, 0);
        foreach ($rows as $r => $row) {
            $f = 0;
            foreach (str_split($row) as $ch) {
                if (ctype_digit($ch)) {
                    $f += (int) $ch;

                    continue;
                }
                $type = match (strtolower($ch)) {
                    'p' => 1, 'n' => 2, 'b' => 3, 'r' => 4, 'q' => 5, 'k' => 6, default => 0,
                };
                $rank = 7 - $r; // FEN starts at rank 8
                $board[self::idx($rank, $f)] = ctype_upper($ch) ? $type : 10 + $type;
                $f++;
            }
        }

        $castle = 0;
        $castleStr = $parts[2] ?? '-';
        if (str_contains($castleStr, 'K')) {
            $castle |= self::CASTLE_WK;
        }
        if (str_contains($castleStr, 'Q')) {
            $castle |= self::CASTLE_WQ;
        }
        if (str_contains($castleStr, 'k')) {
            $castle |= self::CASTLE_BK;
        }
        if (str_contains($castleStr, 'q')) {
            $castle |= self::CASTLE_BQ;
        }

        $ep = (($parts[3] ?? '-') === '-') ? null : self::sqIndex($parts[3]);
        $side = ($parts[1] ?? 'w') === 'w' ? self::WHITE : self::BLACK;

        return new Position(
            $board,
            $side,
            $ep,
            $castle,
            (int) ($parts[4] ?? 0),
            (int) ($parts[5] ?? 1),
        );
    }

    public static function toFen(Position $pos): string
    {
        $rows = [];
        for ($r = 7; $r >= 0; $r--) {
            $row = '';
            $empty = 0;
            for ($f = 0; $f < 8; $f++) {
                $p = $pos->board[self::idx($r, $f)];
                if ($p === 0) {
                    $empty++;

                    continue;
                }
                if ($empty > 0) {
                    $row .= (string) $empty;
                    $empty = 0;
                }
                $letters = [1 => 'P', 2 => 'N', 3 => 'B', 4 => 'R', 5 => 'Q', 6 => 'K'];
                $letter = $letters[$p % 10];
                $row .= $p <= 6 ? $letter : strtolower($letter);
            }
            if ($empty > 0) {
                $row .= (string) $empty;
            }
            $rows[] = $row;
        }

        $castle = '';
        if ($pos->castle & self::CASTLE_WK) {
            $castle .= 'K';
        }
        if ($pos->castle & self::CASTLE_WQ) {
            $castle .= 'Q';
        }
        if ($pos->castle & self::CASTLE_BK) {
            $castle .= 'k';
        }
        if ($pos->castle & self::CASTLE_BQ) {
            $castle .= 'q';
        }

        return implode('/', $rows)
            .' '.($pos->side === self::WHITE ? 'w' : 'b')
            .' '.($castle !== '' ? $castle : '-')
            .' '.($pos->ep !== null ? self::sqName($pos->ep) : '-')
            .' '.$pos->halfmove
            .' '.$pos->fullmove;
    }

    public static function start(): Position
    {
        return self::fromFen(self::START_FEN);
    }

    // ------------------------------------------------------------------
    // Attack detection
    // ------------------------------------------------------------------

    public static function isAttacked(Position $pos, int $sq, int $byColor): bool
    {
        $board = $pos->board;
        $r = $sq >> 3;
        $f = $sq & 7;

        // Pawns: a white pawn on rank r-1 attacks rank r; black pawn on r+1.
        $pr = $byColor === self::WHITE ? $r - 1 : $r + 1;
        $pawn = $byColor === self::WHITE ? self::WP : self::BP;
        foreach ([-1, 1] as $df) {
            $ff = $f + $df;
            if (self::onBoard($pr, $ff) && $board[self::idx($pr, $ff)] === $pawn) {
                return true;
            }
        }

        // Knights.
        $knight = $byColor === self::WHITE ? self::WN : self::BN;
        foreach (self::KNIGHT_DELTAS as [$dr, $df]) {
            if (self::onBoard($r + $dr, $f + $df) && $board[self::idx($r + $dr, $f + $df)] === $knight) {
                return true;
            }
        }

        // Adjacent king.
        $king = $byColor === self::WHITE ? self::WK : self::BK;
        foreach (self::KING_DELTAS as [$dr, $df]) {
            if (self::onBoard($r + $dr, $f + $df) && $board[self::idx($r + $dr, $f + $df)] === $king) {
                return true;
            }
        }

        // Sliding pieces.
        $rookQueen = [$byColor === self::WHITE ? self::WR : self::BR, $byColor === self::WHITE ? self::WQ : self::BQ];
        $bishopQueen = [$byColor === self::WHITE ? self::WB : self::BB, $byColor === self::WHITE ? self::WQ : self::BQ];

        foreach (self::ROOK_DIRS as [$dr, $df]) {
            $rr = $r + $dr;
            $ff = $f + $df;
            while (self::onBoard($rr, $ff)) {
                $p = $board[self::idx($rr, $ff)];
                if ($p !== 0) {
                    if (in_array($p, $rookQueen, true)) {
                        return true;
                    }

                    break;
                }
                $rr += $dr;
                $ff += $df;
            }
        }

        foreach (self::BISHOP_DIRS as [$dr, $df]) {
            $rr = $r + $dr;
            $ff = $f + $df;
            while (self::onBoard($rr, $ff)) {
                $p = $board[self::idx($rr, $ff)];
                if ($p !== 0) {
                    if (in_array($p, $bishopQueen, true)) {
                        return true;
                    }

                    break;
                }
                $rr += $dr;
                $ff += $df;
            }
        }

        return false;
    }

    public static function inCheck(Position $pos, int $color): bool
    {
        $king = $color === self::WHITE ? self::WK : self::BK;
        $kingSq = null;
        for ($i = 0; $i < 64; $i++) {
            if ($pos->board[$i] === $king) {
                $kingSq = $i;

                break;
            }
        }
        if ($kingSq === null) {
            return false;
        }

        return self::isAttacked($pos, $kingSq, $color === self::WHITE ? self::BLACK : self::WHITE);
    }

    /** @return int[] packed pseudo-legal moves for the side to move */
    public static function pseudoMoves(Position $pos): array
    {
        $moves = [];
        $side = $pos->side;
        $board = $pos->board;

        for ($i = 0; $i < 64; $i++) {
            $p = $board[$i];
            if ($p === 0 || self::colorOf($p) !== $side) {
                continue;
            }
            $type = self::typeOf($p);
            $r = $i >> 3;
            $f = $i & 7;

            if ($type === 1) { // pawn
                $dir = $side === self::WHITE ? 1 : -1;
                $startRank = $side === self::WHITE ? 1 : 6;
                $promoRank = $side === self::WHITE ? 7 : 0;
                $r1 = $r + $dir;

                // Pushes.
                if (self::onBoard($r1, $f) && $board[self::idx($r1, $f)] === 0) {
                    if ($r1 === $promoRank) {
                        for ($pr = 1; $pr <= 4; $pr++) {
                            $moves[] = self::mkMove($i, self::idx($r1, $f), $pr);
                        }
                    } else {
                        $moves[] = self::mkMove($i, self::idx($r1, $f));
                        if ($r === $startRank) {
                            $r2 = $r + 2 * $dir;
                            if ($board[self::idx($r2, $f)] === 0) {
                                $moves[] = self::mkMove($i, self::idx($r2, $f));
                            }
                        }
                    }
                }

                // Captures (incl. en passant).
                foreach ([-1, 1] as $df) {
                    $ff = $f + $df;
                    if (! self::onBoard($r1, $ff)) {
                        continue;
                    }
                    $target = self::idx($r1, $ff);
                    $tp = $board[$target];
                    if ($tp !== 0 && self::colorOf($tp) !== $side) {
                        if ($r1 === $promoRank) {
                            for ($pr = 1; $pr <= 4; $pr++) {
                                $moves[] = self::mkMove($i, $target, $pr);
                            }
                        } else {
                            $moves[] = self::mkMove($i, $target);
                        }
                    } elseif ($tp === 0 && $pos->ep === $target) {
                        $moves[] = self::mkMove($i, $target);
                    }
                }
            } elseif ($type === 2) { // knight
                foreach (self::KNIGHT_DELTAS as [$dr, $df]) {
                    $rr = $r + $dr;
                    $ff = $f + $df;
                    if (! self::onBoard($rr, $ff)) {
                        continue;
                    }
                    $target = self::idx($rr, $ff);
                    $tp = $board[$target];
                    if ($tp === 0 || self::colorOf($tp) !== $side) {
                        $moves[] = self::mkMove($i, $target);
                    }
                }
            }
            elseif ($type === 6) { // king
                foreach (self::KING_DELTAS as [$dr, $df]) {
                    $rr = $r + $dr;
                    $ff = $f + $df;
                    if (! self::onBoard($rr, $ff)) {
                        continue;
                    }
                    $target = self::idx($rr, $ff);
                    $tp = $board[$target];
                    if ($tp === 0 || self::colorOf($tp) !== $side) {
                        $moves[] = self::mkMove($i, $target);
                    }
                }

                // Castling (rights + empty path + not through check).
                if ($side === self::WHITE && $i === 4) {
                    if (
                        ($pos->castle & self::CASTLE_WK) !== 0
                        && $board[5] === 0 && $board[6] === 0 && $board[7] === self::WR
                        && ! self::isAttacked($pos, 4, self::BLACK)
                        && ! self::isAttacked($pos, 5, self::BLACK)
                        && ! self::isAttacked($pos, 6, self::BLACK)
                    ) {
                        $moves[] = self::mkMove(4, 6);
                    }
                    if (
                        ($pos->castle & self::CASTLE_WQ) !== 0
                        && $board[3] === 0 && $board[2] === 0 && $board[1] === 0 && $board[0] === self::WR
                        && ! self::isAttacked($pos, 4, self::BLACK)
                        && ! self::isAttacked($pos, 3, self::BLACK)
                        && ! self::isAttacked($pos, 2, self::BLACK)
                    ) {
                        $moves[] = self::mkMove(4, 2);
                    }
                }
                if ($side === self::BLACK && $i === 60) {
                    if (
                        ($pos->castle & self::CASTLE_BK) !== 0
                        && $board[61] === 0 && $board[62] === 0 && $board[63] === self::BR
                        && ! self::isAttacked($pos, 60, self::WHITE)
                        && ! self::isAttacked($pos, 61, self::WHITE)
                        && ! self::isAttacked($pos, 62, self::WHITE)
                    ) {
                        $moves[] = self::mkMove(60, 62);
                    }
                    if (
                        ($pos->castle & self::CASTLE_BQ) !== 0
                        && $board[59] === 0 && $board[58] === 0 && $board[57] === 0 && $board[56] === self::BR
                        && ! self::isAttacked($pos, 60, self::WHITE)
                        && ! self::isAttacked($pos, 59, self::WHITE)
                        && ! self::isAttacked($pos, 58, self::WHITE)
                    ) {
                        $moves[] = self::mkMove(60, 58);
                    }
                }
            } else { // sliders: bishop (3), rook (4), queen (5)
                $dirs = [];
                if ($type === 4 || $type === 5) {
                    array_push($dirs, ...self::ROOK_DIRS);
                }
                if ($type === 3 || $type === 5) {
                    array_push($dirs, ...self::BISHOP_DIRS);
                }
                foreach ($dirs as [$dr, $df]) {
                    $rr = $r + $dr;
                    $ff = $f + $df;
                    while (self::onBoard($rr, $ff)) {
                        $target = self::idx($rr, $ff);
                        $tp = $board[$target];
                        if ($tp === 0) {
                            $moves[] = self::mkMove($i, $target);
                        } else {
                            if (self::colorOf($tp) !== $side) {
                                $moves[] = self::mkMove($i, $target);
                            }

                            break;
                        }
                        $rr += $dr;
                        $ff += $df;
                    }
                }
            }
        }

        return $moves;
    }

    /** Applies a move and returns the resulting position (does not validate legality). */
    public static function applyMove(Position $pos, int $move): Position
    {
        $from = self::moveFrom($move);
        $to = self::moveTo($move);
        $promo = self::movePromo($move);

        $board = $pos->board;
        $piece = $board[$from];
        $type = self::typeOf($piece);
        $side = self::colorOf($piece);
        $captured = $board[$to];

        $board[$to] = $piece;
        $board[$from] = 0;

        $ep = null;
        $halfmove = $pos->halfmove + 1;

        if ($type === 1) { // pawn
            $halfmove = 0;

            // En-passant capture: diagonal move onto an empty square.
            if ($captured === 0 && ($to & 7) !== ($from & 7)) {
                $capSq = $side === self::WHITE ? $to - 8 : $to + 8;
                $board[$capSq] = 0;
            }

            // Double push sets the en-passant target square.
            if (abs($to - $from) === 16) {
                $ep = ($from + $to) >> 1;
            }

            // Promotion.
            $toRank = $to >> 3;
            if ($toRank === 7 || $toRank === 0) {
                $promoType = match ($promo) {
                    2 => 4, 3 => 3, 4 => 2, default => 5,
                };
                $board[$to] = $side === self::WHITE ? $promoType : 10 + $promoType;
            }
        }

        if ($type === 6 && abs($to - $from) === 2) { // castling: also move the rook
            if ($to > $from) { // kingside: rook h -> f
                $rookFrom = $side === self::WHITE ? 7 : 63;
                $rookTo = $side === self::WHITE ? 5 : 61;
            } else { // queenside: rook a -> d
                $rookFrom = $side === self::WHITE ? 0 : 56;
                $rookTo = $side === self::WHITE ? 3 : 59;
            }
            $board[$rookTo] = $board[$rookFrom];
            $board[$rookFrom] = 0;
        }

        // Update castling rights.
        $castle = $pos->castle;
        if ($type === 6) {
            $castle &= $side === self::WHITE ? ~3 : ~12;
        }
        if ($board[7] !== self::WR) {
            $castle &= ~self::CASTLE_WK;
        }
        if ($board[0] !== self::WR) {
            $castle &= ~self::CASTLE_WQ;
        }
        if ($board[63] !== self::BR) {
            $castle &= ~self::CASTLE_BK;
        }
        if ($board[56] !== self::BR) {
            $castle &= ~self::CASTLE_BQ;
        }

        if ($captured !== 0) {
            $halfmove = 0;
        }

        $fullmove = $side === self::BLACK ? $pos->fullmove + 1 : $pos->fullmove;

        return new Position($board, $side === self::WHITE ? self::BLACK : self::WHITE, $ep, $castle, $halfmove, $fullmove);
    }

    /** @return int[] fully legal moves for the side to move */
    public static function legalMoves(Position $pos): array
    {
        $legal = [];
        foreach (self::pseudoMoves($pos) as $move) {
            $child = self::applyMove($pos, $move);
            if (! self::inCheck($child, $pos->side)) {
                $legal[] = $move;
            }
        }

        return $legal;
    }

    /** Status for the side to move: 'normal' | 'check' | 'checkmate' | 'stalemate'. */
    public static function statusOf(Position $pos): string
    {
        $moves = self::legalMoves($pos);
        if ($moves === []) {
            return self::inCheck($pos, $pos->side) ? 'checkmate' : 'stalemate';
        }

        return self::inCheck($pos, $pos->side) ? 'check' : 'normal';
    }

    public static function isInsufficientMaterial(Position $pos): bool
    {
        $minors = 0;
        foreach ($pos->board as $p) {
            if ($p === 0) {
                continue;
            }
            $type = self::typeOf($p);
            if ($type === 1 || $type === 4 || $type === 5) {
                return false;
            }
            if ($type === 2 || $type === 3) {
                $minors++;
            }
        }

        return $minors <= 1;
    }

    // ------------------------------------------------------------------
    // SAN
    // ------------------------------------------------------------------

    public static function toSAN(Position $pos, int $move): string
    {
        $from = self::moveFrom($move);
        $to = self::moveTo($move);
        $promo = self::movePromo($move);
        $piece = $pos->board[$from];
        $type = self::typeOf($piece);
        $isCapture = $pos->board[$to] !== 0
            || ($type === 1 && $pos->board[$to] === 0 && ($to & 7) !== ($from & 7) && $to === $pos->ep);

        if ($type === 6 && abs($to - $from) === 2) {
            $san = $to > $from ? 'O-O' : 'O-O-O';
        } elseif ($type === 1) {
            $san = $isCapture
                ? chr(97 + ($from & 7)).'x'.self::sqName($to)
                : self::sqName($to);
            if ($promo !== 0) {
                $san .= '='.match ($promo) {
                    2 => 'R', 3 => 'B', 4 => 'N', default => 'Q',
                };
            }
        } else {
            $letter = ['', '', 'N', 'B', 'R', 'Q', 'K'][$type];

            // Disambiguation among same-type pieces that reach the same square.
            $others = [];
            foreach (self::legalMoves($pos) as $m) {
                if (self::moveTo($m) === $to && self::moveFrom($m) !== $from) {
                    $otherPiece = $pos->board[self::moveFrom($m)];
                    if ($otherPiece !== 0 && self::typeOf($otherPiece) === $type) {
                        $others[] = self::moveFrom($m);
                    }
                }
            }
            $disamb = '';
            if ($others !== []) {
                $sameFile = false;
                $sameRank = false;
                foreach ($others as $o) {
                    if (($o & 7) === ($from & 7)) {
                        $sameFile = true;
                    }
                    if (($o >> 3) === ($from >> 3)) {
                        $sameRank = true;
                    }
                }
                if (! $sameFile) {
                    $disamb = chr(97 + ($from & 7));
                } elseif (! $sameRank) {
                    $disamb = (string) (($from >> 3) + 1);
                } else {
                    $disamb = chr(97 + ($from & 7)).(string) (($from >> 3) + 1);
                }
            }

            $san = $letter.$disamb.($isCapture ? 'x' : '').self::sqName($to);
        }

        $child = self::applyMove($pos, $move);
        if (self::inCheck($child, $child->side)) {
            $san .= self::legalMoves($child) === [] ? '#' : '+';
        }

        return $san;
    }
}