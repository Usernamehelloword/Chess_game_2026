<?php

namespace Tests\Unit;

use App\Services\ChessEngine;
use App\Services\Position;
use PHPUnit\Framework\TestCase;

class ChessEngineTest extends TestCase
{
    private function play(string $startFen, array $uciMoves): Position
    {
        $pos = ChessEngine::fromFen($startFen);
        foreach ($uciMoves as $uci) {
            $move = ChessEngine::moveFromUci($uci);
            $this->assertNotNull($move);
            $this->assertContains($move, ChessEngine::legalMoves($pos), "Illegal move in test: $uci");
            $pos = ChessEngine::applyMove($pos, $move);
        }

        return $pos;
    }

    private function perft(Position $pos, int $depth): int
    {
        if ($depth === 0) {
            return 1;
        }
        $n = 0;
        foreach (ChessEngine::legalMoves($pos) as $m) {
            $n += $this->perft(ChessEngine::applyMove($pos, $m), $depth - 1);
        }

        return $n;
    }

    public function test_start_position_has_twenty_moves(): void
    {
        $this->assertCount(20, ChessEngine::legalMoves(ChessEngine::start()));
    }

    public function test_perft_matches_known_values(): void
    {
        $this->assertSame(20, $this->perft(ChessEngine::start(), 1));
        $this->assertSame(400, $this->perft(ChessEngine::start(), 2));
        $this->assertSame(8902, $this->perft(ChessEngine::start(), 3));
    }

    public function test_fen_round_trip(): void
    {
        $fens = [
            ChessEngine::START_FEN,
            'r1bq1rk1/pp2bppp/2n1pn2/3p4/2PP4/2N1PN2/PP2BPPP/R1BQ1RK1 w - - 0 9',
            '8/8/8/8/8/8/8/K1k5 w - - 0 1',
        ];
        foreach ($fens as $fen) {
            $this->assertSame($fen, ChessEngine::toFen(ChessEngine::fromFen($fen)));
        }
    }

    public function test_scholar_mate_is_detected(): void
    {
        $pos = $this->play(ChessEngine::START_FEN, [
            'e2e4', 'e7e5',
            'd1h5', 'b8c6',
            'f1c4', 'g8f6',
            'h5f7',
        ]);
        $this->assertSame('checkmate', ChessEngine::statusOf($pos));
        $this->assertSame(ChessEngine::BLACK, $pos->side);
    }

    public function test_fools_mate_is_detected(): void
    {
        $pos = $this->play(ChessEngine::START_FEN, [
            'f2f3', 'e7e5',
            'g2g4', 'd8h4',
        ]);
        $this->assertSame('checkmate', ChessEngine::statusOf($pos));
    }

    public function test_stalemate_is_detected(): void
    {
        $pos = ChessEngine::fromFen('7k/5Q2/6K1/8/8/8/8/8 b - - 0 1');
        $this->assertSame('stalemate', ChessEngine::statusOf($pos));
    }

    public function test_castling_is_generated_and_applied(): void
    {
        $pos = ChessEngine::fromFen('r3k2r/pppppppp/8/8/8/8/PPPPPPPP/R3K2R w KQkq - 0 1');
        $uci = array_map(fn ($m) => ChessEngine::uciOfMove($m), ChessEngine::legalMoves($pos));
        $this->assertContains('e1g1', $uci);
        $this->assertContains('e1c1', $uci);

        $after = ChessEngine::applyMove($pos, ChessEngine::moveFromUci('e1g1'));
        $this->assertSame(ChessEngine::WR, $after->board[ChessEngine::sqIndex('f1')]);
        $this->assertSame(0, $after->board[ChessEngine::sqIndex('h1')]);
        $this->assertSame(0, $after->castle & (ChessEngine::CASTLE_WK | ChessEngine::CASTLE_WQ));
        $this->assertSame(ChessEngine::CASTLE_BK | ChessEngine::CASTLE_BQ, $after->castle & 12);

        $san = ChessEngine::toSAN($pos, ChessEngine::moveFromUci('e1g1'));
        $this->assertSame('O-O', $san);
    }

    public function test_castling_forbidden_through_check(): void
    {
        // Black rook on f8 attacks f1 (nothing blocks the f-file): white may
        // not castle kingside, but queenside is still legal.
        $pos = ChessEngine::fromFen('5r2/8/8/8/8/8/8/R3K2R w KQq - 0 1');
        $uci = array_map(fn ($m) => ChessEngine::uciOfMove($m), ChessEngine::legalMoves($pos));
        $this->assertNotContains('e1g1', $uci);
        $this->assertContains('e1c1', $uci);
    }

    public function test_en_passant_capture(): void
    {
        $pos = $this->play(ChessEngine::START_FEN, [
            'e2e4', 'a7a6',
            'e4e5', 'd7d5',
        ]);
        $uci = array_map(fn ($m) => ChessEngine::uciOfMove($m), ChessEngine::legalMoves($pos));
        $this->assertContains('e5d6', $uci);

        $after = ChessEngine::applyMove($pos, ChessEngine::moveFromUci('e5d6'));
        $this->assertSame(0, $after->board[ChessEngine::sqIndex('d5')]);
        $san = ChessEngine::toSAN($pos, ChessEngine::moveFromUci('e5d6'));
        $this->assertSame('exd6', $san);
    }

    public function test_pawn_promotion(): void
    {
        $pos = ChessEngine::fromFen('8/P7/8/8/8/8/k6K/8 w - - 0 1');
        $uci = array_map(fn ($m) => ChessEngine::uciOfMove($m), ChessEngine::legalMoves($pos));
        $this->assertContains('a7a8q', $uci);
        $this->assertContains('a7a8n', $uci);

        $after = ChessEngine::applyMove($pos, ChessEngine::moveFromUci('a7a8q'));
        $this->assertSame(ChessEngine::WQ, $after->board[ChessEngine::sqIndex('a8')]);
    }

    public function test_pinned_piece_is_not_legal(): void
    {
        $pos = ChessEngine::fromFen('3rk3/8/8/8/8/8/3N4/3K4 w - - 0 1');
        $uci = array_map(fn ($m) => ChessEngine::uciOfMove($m), ChessEngine::legalMoves($pos));
        $this->assertNotContains('d2b3', $uci);
        $this->assertNotContains('d2f3', $uci);
    }

    public function test_insufficient_material(): void
    {
        $this->assertTrue(ChessEngine::isInsufficientMaterial(ChessEngine::fromFen('8/8/8/8/8/8/8/K1k5 w - - 0 1')));
        $this->assertTrue(ChessEngine::isInsufficientMaterial(ChessEngine::fromFen('8/8/8/8/8/8/8/KBk5 w - - 0 1')));
        // K+R vs K is sufficient to checkmate.
        $this->assertFalse(ChessEngine::isInsufficientMaterial(ChessEngine::fromFen('8/8/8/8/8/8/8/KR1k4 w - - 0 1')));
        $this->assertFalse(ChessEngine::isInsufficientMaterial(ChessEngine::start()));
    }

    public function test_san_disambiguation(): void
    {
        $pos = ChessEngine::fromFen('k7/8/8/8/8/5N2/8/1N2K3 w - - 0 1');
        $san = ChessEngine::toSAN($pos, ChessEngine::moveFromUci('b1d2'));
        $this->assertSame('Nbd2', $san);
    }

    public function test_fifty_move_clock_is_tracked(): void
    {
        $pos = $this->play(ChessEngine::START_FEN, ['g1f3', 'g8f6']);
        $this->assertSame(2, $pos->halfmove);

        $pos = $this->play(ChessEngine::START_FEN, ['e2e4', 'e7e5']);
        $this->assertSame(0, $pos->halfmove);
    }
}