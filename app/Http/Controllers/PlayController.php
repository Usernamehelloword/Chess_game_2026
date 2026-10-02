<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PlayController extends Controller
{
    /** Lobby: create a multiplayer game (or reuse an existing waiting room). */
    public static function lobby(Request $request)
    {
        $user = Auth::user();

        $existing = Game::query()
            ->where('mode', Game::MODE_MULTI)
            ->where('status', Game::STATUS_WAITING)
            ->where('white_user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        if ($existing && ! $request->boolean('new')) {
            return redirect('/games/'.$existing->code);
        }

        return view('games.lobby', [
            'timeControls' => GameService::timeControls(),
        ]);
    }

    /** The "join by code" page. */
    public static function joinForm()
    {
        return view('games.join');
    }

    /** Bot setup page. */
    public static function botSetup()
    {
        return view('games.bot-setup', [
            'timeControls' => GameService::timeControls(),
            'difficulties' => GameService::DIFFICULTIES,
        ]);
    }

    /** The current player's game history. */
    public static function myGames()
    {
        $user = Auth::user();

        $games = Game::query()
            ->where(function ($q) use ($user) {
                $q->where('white_user_id', $user->id)->orWhere('black_user_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('games.index', ['games' => $games, 'me' => $user]);
    }
}
