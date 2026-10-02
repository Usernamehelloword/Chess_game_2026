<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ProfileController extends Controller
{
    public static function show(Request $request, int $id)
    {
        $user = User::query()->find($id);
        if (! $user) {
            abort(404);
        }

        $games = Game::query()
            ->where(function ($q) use ($user) {
                $q->where('white_user_id', $user->id)->orWhere('black_user_id', $user->id);
            })
            ->where('status', Game::STATUS_FINISHED)
            ->orderByDesc('finished_at')
            ->limit(10)
            ->get();

        $winRate = $user->games_played > 0
            ? (int) round(100 * $user->wins / max(1, $user->games_played))
            : 0;

        return view('profile.show', [
            'player' => $user,
            'games' => $games,
            'winRate' => $winRate,
            'me' => Auth::user(),
        ]);
    }

    public static function settings()
    {
        return view('profile.settings', [
            'me' => Auth::user(),
            'avatars' => ['♞', '♛', '♜', '♝', '♟', ' ★', '⚡', '🛡', '🎯', '👑', '🐉', '🦊'],
        ]);
    }

    public static function updateSettings(Request $request)
    {
        $me = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:32'],
            'avatar' => ['required', 'string', 'max:12'],
            'bio' => ['nullable', 'string', 'max:255'],
        ]);

        $me->name = $data['name'];
        $me->avatar = $data['avatar'];
        $me->bio = $data['bio'] ?? null;
        $me->save();

        return back()->with('status', 'Profile updated.');
    }
}
