<?php

namespace App\Services;

use App\Models\ChessMove;
use App\Models\Game;
use App\Models\User;
use RuntimeException;

final class GameService
{
    public const TIME_CONTROLS = [
        ['id' => '1+0', 'label' => 'Bullet', 'base' => 60, 'inc' => 0],
        ['id' => '3+0', 'label' => 'Blitz', 'base' => 180, 'inc' => 0],
        ['id' => '3+2', 'label' => 'Blitz', 'base' => 180, 'inc' => 2],
        ['id' => '5+0', 'label' => 'Blitz', 'base' => 300, 'inc' => 0],
        ['id' => '10+0', 'label' => 'Rapid', 'base' => 600, 'inc' => 0],
        ['id' => '10+5', 'label' => 'Rapid', 'base' => 600, 'inc' => 5],
        ['id' => '30+0', 'label' => 'Classical', 'base' => 1800, 'inc' => 0],
    ];

    public const DIFFICULTIES = ['beginner', 'intermediate', 'advanced', 'expert'];

    public const BOT_RATINGS = [
        'beginner' => 800,
        'intermediate' => 1300,
        'advanced' => 1700,
        'expert' => 2100,
    ];

    public static function timeControls(): array
    {
        return self::TIME_CONTROLS;
    }

    public static function validTimeControl(string $id): bool
    {
        foreach (self::TIME_CONTROLS as $tc) {
            if ($tc['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    public static function timeControl(string $id): ?array
    {
        foreach (self::TIME_CONTROLS as $tc) {
            if ($tc['id'] === $id) {
                return $tc;
            }
        }

        return null;
    }

    public static function validDifficulty(string $id): bool
    {
        return in_array($id, self::DIFFICULTIES, true);
    }

    public static function generateCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no ambiguous chars
        do {
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $exists = Game::query()->where('code', $code)->exists();
        } while ($exists);

        return $code;
    }

    // ------------------------------------------------------------------
    // Creation
    // ------------------------------------------------------------------

    public static function createMultiGame(User $user, string $timeControlId): Game
    {
        $tc = self::timeControl($timeControlId) ?? self::timeControl('10+5');

        $game = new Game;
        $game->code = self::generateCode();
        $game->mode = Game::MODE_MULTI;
        $game->status = Game::STATUS_WAITING;
        $game->white_user_id = $user->id;
        $game->white_side = 'user';
        $game->black_side = 'user';
        $game->time_control = $tc['id'];
        $game->time_base = $tc['base'];
        $game->time_increment = $tc['inc'];
        $game->rated = true;
        $game->save();

        return $game;
    }

    public static function createBotGame(User $user, string $difficulty, string $timeControlId, string $color): Game
    {
        $tc = self::timeControl($timeControlId) ?? self::timeControl('10+5');
        $userIsWhite = $color !== 'black'; // 'white' or 'random' resolved by caller

        $game = new Game;
        $game->code = self::generateCode();
        $game->mode = Game::MODE_BOT;
        $game->status = Game::STATUS_ACTIVE;
        $game->white_user_id = $userIsWhite ? $user->id : null;
        $game->black_user_id = $userIsWhite ? null : $user->id;
        $game->white_side = $userIsWhite ? 'user' : 'bot';
        $game->black_side = $userIsWhite ? 'bot' : 'user';
        $game->time_control = $tc['id'];
        $game->time_base = $tc['base'];
        $game->time_increment = $tc['inc'];
        $game->difficulty = $difficulty;
        $game->rated = true;
        $game->started_at = now();
        $game->save();

        return $game;
    }

    public static function joinGame(User $user, string $code): Game
    {
        $game = Game::query()->where('code', strtoupper(trim($code)))->first();
        if (! $game) {
            throw new RuntimeException('No game found with that code.');
        }
        if ($game->status !== Game::STATUS_WAITING) {
            throw new RuntimeException('That game has already started.');
        }
        if ($game->white_user_id === $user->id) {
            return $game; // creator returning to their lobby
        }

        $game->black_user_id = $user->id;
        $game->status = Game::STATUS_ACTIVE;
        $game->started_at = now();
        $game->save();

        return $game;
    }

    // ------------------------------------------------------------------
    // Gameplay
    // ------------------------------------------------------------------

    public static function colorOfUser(Game $game, ?User $user): ?string
    {
        if (! $user) {
            return null;
        }
        if ($game->white_user_id === $user->id) {
            return 'white';
        }
        if ($game->black_user_id === $user->id) {
            return 'black';
        }

        return null;
    }

    /** Rebuild the current position by replaying all stored moves. */
    public static function replayGame(Game $game): Position
    {
        $pos = ChessEngine::start();
        foreach ($game->moves() as $move) {
            $m = ChessEngine::moveFromUci($move->uci);
            if ($m === null) {
                break;
            }
            $pos = ChessEngine::applyMove($pos, $m);
        }

        return $pos;
    }

    /**
     * Validate + play a move for the given game.
     *
     * @return array{ok: bool, error?: string, san?: string, finished?: bool, winner?: ?string, reason?: string}
     */
    public static function playMove(Game $game, string $uci): array
    {
        if ($game->status !== Game::STATUS_ACTIVE) {
            return ['ok' => false, 'error' => 'The game is not active.'];
        }

        $moves = $game->moves();
        $pos = self::replayPosition($moves);
        $sideToMove = count($moves) % 2 === 0 ? 'white' : 'black';

        $move = ChessEngine::moveFromUci($uci);
        if ($move === null) {
            return ['ok' => false, 'error' => 'Malformed move.'];
        }

        $legal = ChessEngine::legalMoves($pos);
        if (! in_array($move, $legal, true)) {
            return ['ok' => false, 'error' => 'Illegal move.'];
        }

        return self::commitMove($game, $pos, $move, count($moves) + 1, $sideToMove);
    }

    /**
     * Let the bot play its move if it is the bot's turn.
     *
     * @return array{ok: bool, error?: string, san?: string, finished?: bool, winner?: ?string, reason?: string}|null
     */
    public static function botMoveIfNeeded(Game $game): ?array
    {
        if ($game->mode !== Game::MODE_BOT || $game->status !== Game::STATUS_ACTIVE) {
            return null;
        }

        $botColor = $game->botColor();
        $moves = $game->moves();
        $sideToMove = count($moves) % 2 === 0 ? 'white' : 'black';
        if ($botColor !== $sideToMove) {
            return null;
        }

        $pos = self::replayPosition($moves);
        $best = ChessBot::bestMove($pos, $game->difficulty ?? 'intermediate');
        if ($best === null) {
            return null;
        }

        return self::commitMove($game, $pos, $best, count($moves) + 1, $sideToMove);
    }

    /** @return array{ok: bool, san: string, finished: bool, winner: ?string, reason: string} */
    private static function commitMove(Game $game, Position $pos, int $move, int $number, string $color): array
    {
        $san = ChessEngine::toSAN($pos, $move);
        $fenBefore = ChessEngine::toFen($pos);
        $child = ChessEngine::applyMove($pos, $move);
        $fenAfter = ChessEngine::toFen($child);
        $uci = ChessEngine::uciOfMove($move);

        ChessMove::query()->create([
            'game_id' => $game->id,
            'move_number' => $number,
            'color' => $color,
            'uci' => $uci,
            'san' => $san,
            'fen_before' => $fenBefore,
            'fen_after' => $fenAfter,
        ]);

        $winner = null;
        $reason = 'ongoing';
        $finished = false;

        $status = ChessEngine::statusOf($child);
        if ($status === 'checkmate') {
            $finished = true;
            $winner = $child->side === ChessEngine::WHITE ? 'black' : 'white';
            $reason = 'checkmate';
        } elseif ($status === 'stalemate') {
            $finished = true;
            $winner = 'draw';
            $reason = 'stalemate';
        } elseif (ChessEngine::isInsufficientMaterial($child)) {
            $finished = true;
            $winner = 'draw';
            $reason = 'insufficient-material';
        } elseif ($child->halfmove >= 100) {
            $finished = true;
            $winner = 'draw';
            $reason = 'fifty-move-rule';
        } else {
            // Threefold repetition: has this exact position appeared 3+ times?
            $count = ChessMove::query()
                ->where('game_id', $game->id)
                ->where('fen_after', $fenAfter)
                ->count();
            if ($count >= 3) {
                $finished = true;
                $winner = 'draw';
                $reason = 'threefold-repetition';
            }
        }

        if ($finished) {
            $game->draw_offered_by = null;
            self::finishGame($game, $winner, $reason);
        } else {
            // A move cancels any pending draw offer.
            if ($game->draw_offered_by !== null) {
                $game->draw_offered_by = null;
            }
            $game->save();
        }

        return ['ok' => true, 'san' => $san, 'uci' => $uci, 'finished' => $finished, 'winner' => $winner, 'reason' => $finished ? $reason : 'ongoing'];
    }

    /** @param \App\Models\ChessMove[] $moves */
    private static function replayPosition(array $moves): Position
    {
        $pos = ChessEngine::start();
        foreach ($moves as $move) {
            $m = ChessEngine::moveFromUci($move->uci);
            if ($m === null) {
                break;
            }
            $pos = ChessEngine::applyMove($pos, $m);
        }

        return $pos;
    }

    // ------------------------------------------------------------------
    // Game end
    // ------------------------------------------------------------------

    public static function finishGame(Game $game, ?string $winner, string $reason): Game
    {
        if ($game->status === Game::STATUS_FINISHED) {
            return $game;
        }

        $game->status = Game::STATUS_FINISHED;
        $game->winner = $winner;          // 'white' | 'black' | 'draw'
        $game->result_reason = $reason;
        $game->draw_offered_by = null;
        $game->finished_at = now();
        $game->save();

        if ($game->rated) {
            self::applyRatings($game);
        }

        return $game;
    }

    public static function resign(Game $game, User $user): Game
    {
        $color = self::colorOfUser($game, $user);
        if ($color === null || $game->status !== Game::STATUS_ACTIVE) {
            throw new RuntimeException('You cannot resign this game.');
        }

        return self::finishGame($game, $color === 'white' ? 'black' : 'white', 'resignation');
    }

    public static function claimTimeout(Game $game, User $user): Game
    {
        $color = self::colorOfUser($game, $user);
        if ($color === null || $game->status !== Game::STATUS_ACTIVE) {
            throw new RuntimeException('You cannot claim a timeout here.');
        }

        return self::finishGame($game, $color === 'white' ? 'black' : 'white', 'timeout');
    }

    public static function offerDraw(Game $game, User $user): void
    {
        $color = self::colorOfUser($game, $user);
        if ($color === null || $game->status !== Game::STATUS_ACTIVE) {
            throw new RuntimeException('You cannot offer a draw now.');
        }
        $game->draw_offered_by = $color;
        $game->save();
    }

    public static function respondDraw(Game $game, User $user, bool $accept): void
    {
        $color = self::colorOfUser($game, $user);
        if ($color === null || $game->draw_offered_by === null || $game->draw_offered_by === $color) {
            throw new RuntimeException('No draw offer to respond to.');
        }

        if ($accept) {
            self::finishGame($game, 'draw', 'agreement');

            return;
        }

        $game->draw_offered_by = null;
        $game->save();
    }

    public static function rematch(Game $game): Game
    {
        $tc = self::timeControl($game->time_control) ?? self::timeControl('10+5');

        $new = new Game;
        $new->code = self::generateCode();
        $new->mode = $game->mode;
        $new->status = $game->mode === Game::MODE_BOT ? Game::STATUS_ACTIVE : Game::STATUS_WAITING;
        $new->white_user_id = $game->black_user_id;
        $new->black_user_id = $game->white_user_id;
        $new->white_side = $game->black_side;
        $new->black_side = $game->white_side;
        $new->time_control = $tc['id'];
        $new->time_base = $tc['base'];
        $new->time_increment = $tc['inc'];
        $new->difficulty = $game->difficulty;
        $new->rated = $game->rated;

        if ($new->mode === Game::MODE_BOT) {
            $new->started_at = now();
        }
        $new->save();

        return $new;
    }

    // ------------------------------------------------------------------
    // Ratings
    // ------------------------------------------------------------------

    public static function applyRatings(Game $game): void
    {
        $whiteUser = $game->whiteUser();
        $blackUser = $game->blackUser();

        $whiteRating = $whiteUser?->rating ?? (self::BOT_RATINGS[$game->difficulty] ?? 1200);
        $blackRating = $blackUser?->rating ?? (self::BOT_RATINGS[$game->difficulty] ?? 1200);

        $expectedWhite = 1 / (1 + 10 ** (($blackRating - $whiteRating) / 400));
        $actualWhite = $game->winner === 'white' ? 1.0 : ($game->winner === 'black' ? 0.0 : 0.5);
        $delta = (int) round(32 * ($actualWhite - $expectedWhite));

        $participants = [
            ['user' => $whiteUser, 'delta' => $delta, 'actual' => $actualWhite],
            ['user' => $blackUser, 'delta' => -$delta, 'actual' => 1 - $actualWhite],
        ];

        foreach ($participants as $entry) {
            $user = $entry['user'];
            if (! $user) {
                continue;
            }
            $user->rating = max(100, $user->rating + $entry['delta']);
            $user->games_played += 1;
            if ($entry['actual'] === 1.0) {
                $user->wins += 1;
            } elseif ($entry['actual'] === 0.0) {
                $user->losses += 1;
            } else {
                $user->draws += 1;
            }
            $user->save();
        }
    }

    // ------------------------------------------------------------------
    // State payload for polling clients
    // ------------------------------------------------------------------

    public static function statePayload(Game $game, ?User $viewer, int $sinceMoveNumber = 0): array
    {
        $moves = $game->moves();
        $turn = count($moves) % 2 === 0 ? 'white' : 'black';

        $newMoves = [];
        $lastUci = null;
        foreach ($moves as $m) {
            $lastUci = $m->uci;
            if ($m->move_number > $sinceMoveNumber) {
                $newMoves[] = [
                    'n' => $m->move_number,
                    'color' => $m->color,
                    'san' => $m->san,
                    'uci' => $m->uci,
                ];
            }
        }

        return [
            'game' => [
                'code' => $game->code,
                'mode' => $game->mode,
                'status' => $game->status,
                'time_control' => $game->time_control,
                'base' => $game->time_base,
                'inc' => $game->time_increment,
                'difficulty' => $game->difficulty,
                'rated' => $game->rated,
                'winner' => $game->winner,
                'result_reason' => $game->result_reason,
                'draw_offered_by' => $game->draw_offered_by,
                'started_at' => $game->started_at?->toIso8601String(),
            ],
            'me' => ['color' => self::colorOfUser($game, $viewer)],
            'players' => [
                'white' => self::playerInfo($game, 'white'),
                'black' => self::playerInfo($game, 'black'),
            ],
            'turn' => $turn,
            'move_count' => count($moves),
            'moves' => $newMoves,
            'last_move' => $lastUci,
            'bot_turn' => $game->mode === Game::MODE_BOT
                && $turn === $game->botColor()
                && $game->status === Game::STATUS_ACTIVE,
        ];
    }

    private static function playerInfo(Game $game, string $color): array
    {
        $side = $color === 'white' ? 'white' : 'black';
        $kind = $color === 'white' ? $game->white_side : $game->black_side;
        $userId = $color === 'white' ? $game->white_user_id : $game->black_user_id;

        if ($kind === 'bot') {
            return [
                'kind' => 'bot',
                'name' => 'Bot ('.($game->difficulty ?? 'bot').')',
                'avatar' => '🤖',
                'rating' => self::BOT_RATINGS[$game->difficulty] ?? 1200,
            ];
        }

        $user = $userId ? User::find($userId) : null;
        if (! $user) {
            return ['kind' => 'open', 'name' => 'Waiting...', 'avatar' => '❔', 'rating' => null];
        }

        return ['kind' => 'user', 'id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar, 'rating' => $user->rating];
    }
}