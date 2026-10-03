<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final class GamesController extends Controller
{
    /** Create a new game (multi waiting room or bot game). */
    public static function create(Request $request)
    {
        $user = Auth::user();
        $mode = $request->input('mode', 'multi');

        if ($mode === Game::MODE_BOT) {
            $data = $request->validate([
                'difficulty' => ['required', 'in:beginner,intermediate,advanced,expert'],
                'time_control' => ['required', 'string'],
                'color' => ['required', 'in:white,black,random'],
            ]);
            if (! GameService::validTimeControl($data['time_control'])) {
                return back()->withErrors(['time_control' => 'Unknown time control.']);
            }

            $color = $data['color'];
            if ($color === 'random') {
                $color = random_int(0, 1) === 0 ? 'white' : 'black';
            }

            $game = GameService::createBotGame($user, $data['difficulty'], $data['time_control'], $color);

            return redirect('/games/'.$game->code);
        }

        $data = $request->validate(['time_control' => ['required', 'string']]);
        if (! GameService::validTimeControl($data['time_control'])) {
            return back()->withErrors(['time_control' => 'Unknown time control.']);
        }

        $game = GameService::createMultiGame($user, $data['time_control']);

        return redirect('/games/'.$game->code);
    }

    /** Join a private game by code or invite link. */
    public static function join(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        try {
            $game = GameService::joinGame(Auth::user(), $data['code']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['code' => $e->getMessage()])->withInput();
        }

        return redirect('/games/'.$game->code);
    }

    /** The game page. */
    public static function show(Request $request, string $code)
    {
        $user = Auth::user();
        $cleanCode = strtoupper(trim($code));
        $game = Game::query()->where('code', $cleanCode)->first();
        if (! $game) {
            abort(404);
        }

        $myColor = GameService::colorOfUser($game, $user);
        if ($myColor === null) {
            // If the game is still waiting for an opponent, auto-join this user as Black
            if ($game->status === Game::STATUS_WAITING && $game->white_user_id !== $user->id && $game->black_user_id === null) {
                try {
                    $game = GameService::joinGame($user, $cleanCode);
                    $myColor = 'black';
                } catch (RuntimeException $e) {
                    abort(403, $e->getMessage());
                }
            } else {
                abort(403, 'You are not a player in this game.');
            }
        }

        $state = GameService::statePayload($game, $user);

        return view('games.show', ['state' => $state, 'myColor' => $myColor]);
    }

    /** Polling endpoint: everything the client needs since a move number. */
    public static function state(Request $request, string $code)
    {
        $user = Auth::user();
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }
        if (GameService::colorOfUser($game, $user) === null) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $since = (int) $request->query('since', '0');

        return response()->json(['ok' => true, 'state' => GameService::statePayload($game, $user, $since)]);
    }

    /** Waiting-room poll: has the opponent joined yet? */
    public static function waitingState(Request $request, string $code)
    {
        $user = Auth::user();
        $cleanCode = strtoupper(trim($code));
        $game = Game::query()->where('code', $cleanCode)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'status' => 'cancelled', 'error' => 'Game cancelled or removed']);
        }
        if (GameService::colorOfUser($game, $user) === null) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        return response()->json(['ok' => true, 'status' => $game->status]);
    }

    /** Cancel an open waiting game. */
    public static function cancel(Request $request, string $code)
    {
        $user = Auth::user();
        $cleanCode = strtoupper(trim($code));
        $game = Game::query()->where('code', $cleanCode)->first();
        if (! $game) {
            return redirect()->route('lobby');
        }

        if ($game->white_user_id === $user->id && $game->status === Game::STATUS_WAITING) {
            $game->delete();
            return redirect()->route('lobby')->with('status', 'Game room cancelled.');
        }

        return redirect('/games/'.$cleanCode);
    }

    /** Play a move. */
    public static function move(Request $request, string $code)
    {
        $user = Auth::user();
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }

        $myColor = GameService::colorOfUser($game, $user);
        if ($myColor === null) {
            return response()->json(['ok' => false, 'error' => 'You are not a player in this game.'], 403);
        }
        if ($game->status !== Game::STATUS_ACTIVE) {
            return response()->json(['ok' => false, 'error' => 'The game is over.'], 409);
        }

        $data = $request->validate(['uci' => ['required', 'string', 'max:8']]);

        $moves = $game->moves();
        $sideToMove = count($moves) % 2 === 0 ? 'white' : 'black';
        if ($sideToMove !== $myColor) {
            return response()->json(['ok' => false, 'error' => 'Not your turn.'], 409);
        }

        $result = GameService::playMove($game, $data['uci']);
        if (! $result['ok']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /** Ask the bot to move (bot games only). */
    public static function botMove(Request $request, string $code)
    {
        $user = Auth::user();
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }
        if (GameService::colorOfUser($game, $user) === null) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $result = GameService::botMoveIfNeeded($game);
        if ($result === null) {
            return response()->json(['ok' => false, 'error' => "It is not the bot's turn."], 409);
        }

        return response()->json($result);
    }

    /** Resign. */
    public static function resign(Request $request, string $code)
    {
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }

        try {
            GameService::resign($game, Auth::user());
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 409);
        }

        return response()->json(['ok' => true, 'status' => $game->status, 'winner' => $game->winner]);
    }

    /** Claim that the opponent ran out of time. */
    public static function timeout(Request $request, string $code)
    {
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }

        try {
            GameService::claimTimeout($game, Auth::user());
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 409);
        }

        return response()->json(['ok' => true, 'status' => $game->status, 'winner' => $game->winner]);
    }

    /** Offer / accept / decline a draw. */
    public static function draw(Request $request, string $code)
    {
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }

        $action = $request->input('action', 'offer');
        $user = Auth::user();

        try {
            if ($action === 'accept' || $action === 'decline') {
                GameService::respondDraw($game, $user, $action === 'accept');
            } else {
                GameService::offerDraw($game, $user);
            }
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 409);
        }

        return response()->json(['ok' => true, 'draw_offered_by' => $game->draw_offered_by, 'status' => $game->status]);
    }

    /** Start a rematch. */
    public static function rematch(Request $request, string $code)
    {
        $game = Game::query()->where('code', $code)->first();
        if (! $game) {
            return response()->json(['ok' => false, 'error' => 'Not found'], 404);
        }

        try {
            $newGame = GameService::rematch($game);
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 409);
        }

        return response()->json(['ok' => true, 'code' => $newGame->code]);
    }
}