@extends('layouts.app')

@section('title', 'My Games — CHess')

@section('content')
<div class="py-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold tracking-tight">My Games</h1>
        <a href="{{ route('play') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">+ New Game</a>
    </div>

    <div class="mt-8 overflow-hidden rounded-2xl border border-zinc-100 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-100 bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-5 py-3">Opponent</th>
                    <th class="px-5 py-3">Result</th>
                    <th class="hidden px-5 py-3 sm:table-cell">Mode</th>
                    <th class="hidden px-5 py-3 sm:table-cell">Time</th>
                    <th class="px-5 py-3 text-right">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-50">
                @forelse ($games as $game)
                    @php
                        $isWhite = $game->white_user_id === $me->id;
                        $myResult = null;
                        if ($game->status === 'finished') {
                            if ($game->winner === 'draw') $myResult = ['Draw', 'text-zinc-500'];
                            elseif (($game->winner === 'white') === $isWhite) $myResult = ['Win', 'text-emerald-600'];
                            else $myResult = ['Loss', 'text-red-500'];
                        }
                        $opp = $isWhite ? $game->blackUser() : $game->whiteUser();
                        $oppName = $opp?->name ?? ($game->mode === 'bot' ? 'Bot ('.$game->difficulty.')' : 'Waiting...');
                    @endphp
                    <tr class="transition hover:bg-zinc-50">
                        <td class="px-5 py-3 font-medium">
                            <a href="{{ route('games.show', $game->code) }}" class="hover:text-emerald-600">{{ $oppName }}</a>
                        </td>
                        <td class="px-5 py-3">
                            @if ($myResult)
                                <span class="font-semibold {{ $myResult[1] }}">{{ $myResult[0] }}</span>
                                <span class="text-xs text-zinc-400">({{ $game->result_reason }})</span>
                            @elseif ($game->status === 'waiting')
                                <span class="text-xs font-medium text-amber-600">Waiting for opponent</span>
                            @else
                                <a href="{{ route('games.show', $game->code) }}" class="text-xs font-semibold text-emerald-600 hover:underline">In progress →</a>
                            @endif
                        </td>
                        <td class="hidden px-5 py-3 text-zinc-500 sm:table-cell">{{ $game->mode === 'bot' ? 'Bot' : '1v1' }}</td>
                        <td class="hidden px-5 py-3 font-mono text-xs text-zinc-500 sm:table-cell">{{ $game->time_control }}</td>
                        <td class="px-5 py-3 text-right text-xs text-zinc-400">{{ $game->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-zinc-400">No games yet. <a href="{{ route('play') }}" class="font-medium text-emerald-600">Start one now →</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
