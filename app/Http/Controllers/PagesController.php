<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;

final class PagesController extends Controller
{
    public static function home()
    {
        return view('pages.home', [
            'totalGames' => Game::query()->count(),
            'totalPlayers' => User::query()->count(),
            'botGames' => Game::query()->where('mode', 'bot')->count(),
            'multiGames' => Game::query()->where('mode', 'multi')->count(),
            'topPlayers' => User::query()->orderByDesc('rating')->limit(5)->get(),
        ]);
    }

    public static function play()
    {
        return view('pages.play');
    }

    public static function howToPlay()
    {
        return view('pages.how-to-play');
    }

    public static function leaderboard()
    {
        $players = User::query()
            ->orderByDesc('rating')
            ->orderByDesc('wins')
            ->limit(50)
            ->get()
            ->values();

        return view('pages.leaderboard', ['players' => $players]);
    }
}
