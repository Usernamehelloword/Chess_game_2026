@extends('layouts.app')

@section('title', $player->name.' — CHess')

@section('content')
<div class="py-6">
    <div class="flex flex-col items-center gap-4 rounded-3xl border border-zinc-100 bg-white p-8 shadow-sm sm:flex-row sm:items-start">
        <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-100 text-4xl">{{ $player->avatar }}</span>
        <div class="flex-1 text-center sm:text-left">
            <h1 class="text-2xl font-bold">{{ $player->name }}</h1>
            <p class="mt-0.5 text-sm text-zinc-500">
                Rating: <span class="font-mono font-semibold text-emerald-700">{{ $player->rating }}</span>
                · Joined {{ $player->created_at->format('M Y') }}
            </p>
            @if ($player->bio)
                <p class="mt-2 text-sm text-zinc-600">{{ $player->bio }}</p>
            @endif
        </div>
        @if ($me && $me->id === $player->id)
            <a href="{{ route('settings') }}" class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-zinc-50">Edit profile</a>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
        <div class="rounded-2xl border border-zinc-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-zinc-500">Games</p><p class="text-2xl font-bold">{{ $player->games_played }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-zinc-500">Wins</p><p class="text-2xl font-bold text-emerald-600">{{ $player->wins }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-zinc-500">Losses</p><p class="text-2xl font-bold text-red-500">{{ $player->losses }}</p>
        </div>
        <div class="rounded-2xl border border-zinc-100 bg-white p-4 text-center shadow-sm">
            <p class="text-xs text-zinc-500">Draws</p><p class="text-2xl font-bold text-zinc-600">{{ $player->draws }}</p>
        </div>
        <div class="col-span-2 rounded-2xl border border-zinc-100 bg-white p-4 text-center shadow-sm sm:col-span-1">
            <p class="text-xs text-zinc-500">Win rate</p><p class="text-2xl font-bold">{{ $winRate }}%</p>
        </div>
    </div>

    <h2 class="mt-10 text-lg font-bold">Recent Games</h2>
    <div class="mt-4 overflow-hidden rounded-2xl border border-zinc-100 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-100 bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-5 py-3">Opponent</th>
                    <th class="px-5 py-3">Result</th>
                    <th class="hidden px-5 py-3 sm:table-cell">Mode</th>
                    <th class="px-5 py-3 text-right">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-50">
                @forelse ($games as $game)
                    @php
                        $isWhite = $game->white_user_id === $player->id;
                        if ($game->winner === 'draw') { $res = 'Draw'; $cls = 'text-zinc-500'; }
                        elseif (($game->winner === 'white') === $isWhite) { $res = 'Win'; $cls = 'text-emerald-600'; }
                        else { $res = 'Loss'; $cls = 'text-red-500'; }
                        $opp = $isWhite ? $game->blackUser() : $game->whiteUser();
                    @endphp
                    <tr class="transition hover:bg-zinc-50">
                        <td class="px-5 py-3 font-medium">{{ $opp?->name ?? ($game->mode === 'bot' ? 'Bot' : '—') }}</td>
                        <td class="px-5 py-3 font-semibold {{ $cls }}">{{ $res }}</td>
                        <td class="hidden px-5 py-3 text-zinc-500 sm:table-cell">{{ $game->mode === 'bot' ? 'Bot' : '1v1' }}</td>
                        <td class="px-5 py-3 text-right text-xs text-zinc-400">{{ $game->finished_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-zinc-400">No finished games yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
