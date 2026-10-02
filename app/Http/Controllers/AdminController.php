<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AdminController extends Controller
{
    public static function dashboard()
    {
        if (! Auth::user()?->is_admin) {
            abort(403);
        }

        $totalUsers = User::query()->count();
        $onlineUsers = User::query()->where('last_seen_at', '>=', now()->subMinutes(5))->count();
        $totalGames = Game::query()->count();
        $gamesToday = Game::query()->where('created_at', '>=', now()->startOfDay())->count();
        $botGames = Game::query()->where('mode', Game::MODE_BOT)->count();
        $multiGames = Game::query()->where('mode', Game::MODE_MULTI)->count();

        $gamesPerDay = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $gamesPerDay[] = [
                'label' => $day->format('D j'),
                'count' => Game::query()->whereBetween('created_at', [$day, $day->copy()->endOfDay()])->count(),
            ];
        }

        $maxCount = max(1, max(array_column($gamesPerDay, 'count')));

        $recentGames = Game::query()->orderByDesc('created_at')->limit(10)->get();
        $recentUsers = User::query()->orderByDesc('created_at')->limit(10)->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'onlineUsers' => $onlineUsers,
            'totalGames' => $totalGames,
            'gamesToday' => $gamesToday,
            'botGames' => $botGames,
            'multiGames' => $multiGames,
            'gamesPerDay' => $gamesPerDay,
            'maxCount' => $maxCount,
            'recentGames' => $recentGames,
            'recentUsers' => $recentUsers,
        ]);
    }

    /** Toggle a user's admin status. */
    public static function toggleAdmin(Request $request, int $id)
    {
        if (! Auth::user()?->is_admin) {
            abort(403);
        }
        $user = User::query()->find($id);
        if ($user && $user->id !== Auth::id()) {
            $user->is_admin = ! $user->is_admin;
            $user->save();
        }

        return back();
    }
}
