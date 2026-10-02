@extends('layouts.app')

@section('title', 'Leaderboard — CHess')

@section('content')
<div class="py-6">
    <h1 class="text-3xl font-bold tracking-tight">Leaderboard</h1>
    <p class="mt-1 text-zinc-600">The strongest players on CHess.</p>

    <div class="mt-8 overflow-hidden rounded-2xl border border-zinc-100 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-100 bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-5 py-3">Rank</th>
                    <th class="px-5 py-3">Player</th>
                    <th class="px-5 py-3 text-right">Rating</th>
                    <th class="hidden px-5 py-3 text-right sm:table-cell">Games</th>
                    <th class="hidden px-5 py-3 text-right sm:table-cell">Wins</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-50">
                @forelse ($players as $i => $player)
                <tr class="transition hover:bg-zinc-50 {{ $i < 3 ? 'bg-amber-50/60' : '' }}">
                    <td class="px-5 py-3 font-bold text-zinc-500">
                        @if ($i === 0) 🥇 @elseif ($i === 1) 🥈 @elseif ($i === 2) 🥉 @else {{ $i + 1 }} @endif
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('profile.show', $player->id) }}" class="flex items-center gap-3 font-medium hover:text-emerald-600">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100">{{ $player->avatar }}</span>
                            {{ $player->name }}
                        </a>
                    </td>
                    <td class="px-5 py-3 text-right font-mono font-semibold">{{ $player->rating }}</td>
                    <td class="hidden px-5 py-3 text-right text-zinc-500 sm:table-cell">{{ $player->games_played }}</td>
                    <td class="hidden px-5 py-3 text-right text-zinc-500 sm:table-cell">{{ $player->wins }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-zinc-400">No players yet — be the first!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
