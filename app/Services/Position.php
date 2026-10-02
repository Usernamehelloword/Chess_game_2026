<?php

namespace App\Services;

/**
 * A lightweight immutable chess position.
 *
 * Board representation: flat array of 64 integers, index = rank * 8 + file
 * where rank 0 is White's first rank and file 0 is the "a" file ("a1" = 0).
 */
final class Position
{
    public function __construct(
        public array $board,
        public int $side,       // 1 = white, 2 = black
        public ?int $ep,        // en-passant target square index (or null)
        public int $castle,     // bitmask: 1 = WK, 2 = WQ, 4 = BK, 8 = BQ
        public int $halfmove,   // halfmove clock (plies since capture/pawn move)
        public int $fullmove,   // fullmove number
    ) {
    }
}
