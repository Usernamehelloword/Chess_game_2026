@extends('layouts.app')

@section('title', 'Online Chess Leaderboard — Player Rankings & Elo Ratings | CHess')
@section('meta_description', 'View the online chess leaderboard. Discover top-rated chess players, global Elo rankings, win-loss statistics, and climb the rankings in multiplayer chess.')
@section('meta_keywords', 'online chess leaderboard, chess leaderboard, chess ranking, chess rating, top chess players, chess profile, chess game history')
@section('page', 'leaderboard')

@section('content')
<div class="py-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                🏆 Global Chess Ratings
            </span>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-zinc-900">Online Chess Leaderboard</h1>
            <p class="mt-1 text-sm text-zinc-600">The highest rated players and grandmasters competing on CHess.</p>
        </div>
        <a href="{{ route('play') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
            ♟️ Play to Climb Rankings
        </a>
    </div>

    <!-- Leaderboard Table -->
    <div class="mt-8 overflow-hidden rounded-2xl border border-zinc-100 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-100 bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-5 py-3.5">Rank</th>
                    <th class="px-5 py-3.5">Chess Player</th>
                    <th class="px-5 py-3.5 text-right">Elo Rating</th>
                    <th class="hidden px-5 py-3.5 text-right sm:table-cell">Games Played</th>
                    <th class="hidden px-5 py-3.5 text-right sm:table-cell">Wins</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-50">
                @forelse ($players as $i => $player)
                <tr class="transition hover:bg-zinc-50 {{ $i < 3 ? 'bg-amber-50/60' : '' }}">
                    <td class="px-5 py-3.5 font-bold text-zinc-500">
                        @if ($i === 0) 🥇 @elseif ($i === 1) 🥈 @elseif ($i === 2) 🥉 @else {{ $i + 1 }} @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <a href="{{ route('profile.show', $player->id) }}" class="flex items-center gap-3 font-medium text-zinc-900 hover:text-emerald-600">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-sm">{{ $player->avatar }}</span>
                            <span>{{ $player->name }}</span>
                        </a>
                    </td>
                    <td class="px-5 py-3.5 text-right font-mono font-semibold text-zinc-900">{{ $player->rating }}</td>
                    <td class="hidden px-5 py-3.5 text-right text-zinc-500 sm:table-cell">{{ $player->games_played }}</td>
                    <td class="hidden px-5 py-3.5 text-right text-zinc-500 sm:table-cell">{{ $player->wins }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-zinc-400">
                        No players registered yet — <a href="{{ route('register') }}" class="font-medium text-emerald-600 underline">Sign up and be the first</a>!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Rating Information Section -->
    <div class="mt-8 grid gap-4 rounded-2xl border border-zinc-100 bg-white p-6 text-xs text-zinc-600 sm:grid-cols-3">
        <div>
            <strong class="text-zinc-900">How Ratings Work:</strong>
            <p class="mt-1">Every player starts with an initial rating of 1200 or 1500. Winning against higher-rated opponents yields greater Elo gains.</p>
        </div>
        <div>
            <strong class="text-zinc-900">Rated Multiplayer Chess:</strong>
            <p class="mt-1">Only 1 vs 1 multiplayer chess matches count toward your official online chess leaderboard standing.</p>
        </div>
        <div>
            <strong class="text-zinc-900">Track Match History:</strong>
            <p class="mt-1">Visit any player's profile to view their past game history, win rates, and recent achievements.</p>
        </div>
    </div>
</div>
@endsection
